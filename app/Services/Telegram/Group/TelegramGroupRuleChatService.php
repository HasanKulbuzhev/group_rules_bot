<?php

namespace App\Services\Telegram\Group;

use App\Enums\Cache\CacheTypeEnum;
use App\Enums\Telegram\MessageTypeEnum;
use App\Interfaces\Base\BaseServiceInterface;
use App\Services\Base\Telegram\BaseRuleTelegramChatService;
use App\Services\Telegram\Update\TelegramUpdateService;
use Cache;
use Exception;

class TelegramGroupRuleChatService extends BaseRuleTelegramChatService implements BaseServiceInterface
{
    protected array $rules = [
        MessageTypeEnum::VALUE_TYPE => 'ruleMessageText',
        MessageTypeEnum::EVENT_TYPE => [
            MessageTypeEnum::CALLBACK_QUERY => 'callbackQuery',
        ],
        MessageTypeEnum::GROUP_RULE_TYPE => [
            MessageTypeEnum::NEW_CHAT_MEMBERS => 'newChatMember',
        ],
        MessageTypeEnum::OTHER => 'default',
    ];

    public function run(): bool
    {
        $updateService = (new TelegramUpdateService($this->update));
        $messageType = $updateService->getMessageType();
        $methods = \Arr::get($this->rules, $messageType, MessageTypeEnum::OTHER);
        if (is_array($methods)) {
            foreach ($methods as $type => $methodName) {
                if (in_array($type, $updateService->getMessageInnerTypes($messageType))) {
                    $method = $methodName;
                    break;
                } else {
                    $method = \Arr::get($this->rules, MessageTypeEnum::OTHER);
                }
            }
        }

        return $this->$method();
    }

    private function default(): bool
    {
        return true;
    }

    private function ruleMessageText(): bool
    {
        $chatId = $this->update->getChat()->id;
        $messageId = $this->update->message->messageId;
        $userId = $this->update->message->from->id;

        if (Cache::has($this->getWarningMessagePath($chatId, $userId))) {
            try {
                $this->bot->telegram->deleteMessage([
                    'chat_id' => $chatId,
                    'message_id' => $messageId
                ]);

                return true;
            } catch (Exception $exception) {
                throw $exception;
            }
        }

        return true;
    }

    private function callbackQuery(): bool
    {
        $chatId = $this->update->callbackQuery->message->chat->id;
        $userId = $this->update->callbackQuery->from->id;

        if (Cache::has($this->getWarningMessagePath($chatId, $userId))) {
            try {
                Cache::delete($this->getWarningMessagePath($chatId, $userId));
                $this->bot->telegram->deleteMessage([
                    'chat_id' => $chatId,
                    'message_id' => $warningMessageId,
                ]);

                return true;
            } catch (Exception $exception) {
                Cache::put($this->getWarningMessagePath($chatId, $userId), $warningMessageId);
                $text = $exception->getMessage();
                $allErrorText = json_encode($exception->getTrace());

                throw new Exception("
                Произошло что-то не так. \n
                $text. \n
                https://t.me/c/$chatId/$warningMessageId. \n
                All error text : \n
                $allErrorText
                ");
            }
        }

        return true;
    }

    private function newChatMember(): bool
    {
        $chatId = $this->update->getChat()->id;
        $messageId = $this->update->message->messageId;
        $newUsers = $this->update->message->newChatMembers;
        $inline_keyboard = json_encode([
            'inline_keyboard' => [
                [
                    [
                        'text' => 'Я согласен с правилами группы и не буду их нарушать',
                        'callback_data' => 'smth2',
                    ],
                ],
            ]
        ]);


        try {
            $this->bot->telegram->deleteMessage([
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ]);

            foreach ($newUsers as $user) {
                $warningMessageId = $this->bot->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Вы согласны с правилами группы?',
                    'reply_markup' => $inline_keyboard,
                    'parse_mode' => 'Markdown'
                ])->messageId;

                Cache::put($this->getWarningMessagePath($chatId, $user->id), $warningMessageId);
            }

            return true;
        } catch (Exception $exception) {
            $text = $exception->getMessage();
            $allErrorText = json_encode($exception->getTrace());

            throw new Exception("
            Произошло что-то не так. \n
            $text. \n
            https://t.me/c/$chatId/$messageId. \n
            All error text : \n
            $allErrorText
            ");

        }

        return false;
    }

    private function getWarningMessagePath($chatId, $userId): string
    {
        return CacheTypeEnum::GROUP_RULES_TYPE . ".$chatId.$userId.warning_message_id";

    }
}
