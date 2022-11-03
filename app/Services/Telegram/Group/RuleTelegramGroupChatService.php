<?php

namespace App\Services\Telegram\Group;

use App\Enums\Cache\CacheTypeEnum;
use App\Enums\Telegram\MessageTypeEnum;
use App\Interfaces\Base\BaseServiceInterface;
use App\Services\Base\Telegram\BaseRuleTelegramChatService;
use App\Services\Telegram\Update\TelegramUpdateService;
use Cache;
use Exception;

class RuleTelegramGroupChatService extends BaseRuleTelegramChatService implements BaseServiceInterface
{
    protected array $rules = [
        MessageTypeEnum::TEXT => 'ruleMessageText',
        MessageTypeEnum::CALLBACK_QUERY => 'callbackQuery',
        MessageTypeEnum::NEW_CHAT_PARTICIPANT => 'newChatMember',
    ];

    public function run(): bool
    {
        $messageType = (new TelegramUpdateService($this->update))->getMessageType();
        $method = $this->rules[$messageType];
        return $this->$method();
    }

    private function ruleMessageText(): bool
    {
        $chatId = $this->update->getMessage()->getChat()->getId();
        $messageId = $this->update->getMessage()->getMessageId();
        $userId = $this->update->getMessage()->getFrom()->getId();

        if (Cache::has($this->getWarningMessagePath($chatId, $userId))) {
            try {
                $this->telegram->deleteMessage([
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
        $chatId = $this->update->getCallbackQuery()->getMessage()->getChat()->getId();
        $userId = $this->update->getCallbackQuery()->getFrom()->getId();

        if (Cache::has($this->getWarningMessagePath($chatId, $userId))) {
            $warningMessageId = Cache::get(CacheTypeEnum::GROUP_RULES_TYPE . ".$chatId.$userId.message_id");
            try {
                Cache::delete($this->getWarningMessagePath($chatId, $userId));
                $this->telegram->deleteMessage([
                    'chat_id' => $chatId,
                    'message_id' => $warningMessageId,
                ]);

                return true;
            } catch (Exception $exception) {
                Cache::put($this->getWarningMessagePath($chatId, $userId), $warningMessageId);
                return false;
            }
        }

        return true;
    }

    private function newChatMember(): bool
    {
        $chatId = $this->update->getMessage()->getChat()->getId();
        $userId = $this->update->getMessage()->getNewChatParticipant()->getId();
        $messageId = $this->update->getMessage()->getMessageId();
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
            $this->telegram->deleteMessage([
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ]);

            $warningMessageId = $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => '',
                'reply_markup' => $inline_keyboard,
                'parse_mode' => 'Markdown'
            ]);
            Cache::put($this->getWarningMessagePath($chatId, $userId), $warningMessageId);

            return true;
        } catch (Exception $exception) {
        }

        return false;
    }

    private function getWarningMessagePath($chatId, $userId): string
    {
        return CacheTypeEnum::GROUP_RULES_TYPE . ".$chatId.$userId.warning_message_id";

    }
}
