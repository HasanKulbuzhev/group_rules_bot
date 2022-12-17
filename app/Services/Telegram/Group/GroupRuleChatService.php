<?php

namespace App\Services\Telegram\Group;

use App\Enums\Cache\CacheTypeEnum;
use App\Enums\Telegram\MessageTypeEnum;
use App\Interfaces\Base\BaseService;
use App\Services\Base\Telegram\BaseRuleChatService;
use App\Services\Telegram\Base\BaseGroupChatService;
use App\Services\Telegram\Update\TelegramUpdateService;
use Arr;
use Cache;
use Exception;

class GroupRuleChatService extends BaseGroupChatService implements BaseService
{
    protected array $rules = [
        MessageTypeEnum::VALUE_TYPE => 'ruleMessageText',
        MessageTypeEnum::EVENT_TYPE => [
            MessageTypeEnum::CALLBACK_QUERY => 'callbackQuery',
        ],
        MessageTypeEnum::GROUP_RULE_TYPE => [
            MessageTypeEnum::NEW_CHAT_MEMBERS => 'newChatMember',
            MessageTypeEnum::LEFT_CHAT_PARTICIPANT => 'leftChatUser',
        ],
        MessageTypeEnum::OTHER => 'other',
    ];

    public function run(): bool
    {
        return parent::run();
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

        if ($warningMessageId = Cache::get($this->getWarningMessagePath($chatId, $userId))) {
            try {
                $this->bot->telegram->deleteMessage([
                    'chat_id' => $chatId,
                    'message_id' => $warningMessageId,
                ]);
                Cache::delete($this->getWarningMessagePath($chatId, $userId));

                return true;
            } catch (Exception $exception) {
                $text = $exception->getMessage();
                $allErrorText = json_encode($exception->getTrace());

                throw new Exception("
                С ботом @{$this->bot->username} произошло что-то не так. \n
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
                    'text' => "Прочитайте правила группы и согласитесь с ними. \n {$this->bot->setting->rule}",
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
            С ботом @{$this->bot->username} произошло что-то не так. \n
            $text. \n
            https://t.me/c/$chatId/$messageId. \n
            All error text : \n
            $allErrorText
            ");

        }

        return false;
    }

    public function leftChatUser(): bool
    {
        $chatId = $this->update->getChat()->id;
        $messageId = $this->update->message->messageId;
        $user = $this->update->message->leftChatMember;

        try {
            $this->bot->telegram->deleteMessage([
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ]);

            Cache::delete($this->getWarningMessagePath($chatId, $user->id));

            return true;
        } catch (Exception $exception) {
            $text = $exception->getMessage();
            $allErrorText = json_encode($exception->getTrace());

            throw new Exception("
            С ботом @{$this->bot->username} произошло что-то не так. \n
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
