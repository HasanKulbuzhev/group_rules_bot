<?php

namespace App\Services;

use App\Services\Api\MyApi;
use Illuminate\Filesystem\FilesystemAdapter;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;

class TelegramBotService
{
    public Api $param;
    private FilesystemAdapter $disk;
    private Update $update;
    private array $channels = [
        '-1001816892506',
        '1816892506',
        '-1001606203794',
        '1606203794',
        /** Это id https://t.me/ilmalkalam */
        '-1001694365544',
        '1694365544'
    ];

    /**
     * TelegramBotService constructor.
     * @param  Api  $param
     */
    public function __construct(Api $param)
    {
        $this->param = $param;
        $this->disk = \Storage::disk();
        $this->update = $this->param->getWebhookUpdates();
    }

    public function run()
    {
        if ($this->isChatAdmin()) {
            $this->runAdminChat();
        }

        if ($this->isCallbackQuery($this->update)) {
            $this->runCallbackQuery();
        }

        if (in_array(data_get($this->update, 'message.chat.id', ''), $this->channels)) {
            $this->runChannelRule();
        }
    }

    public function sendRuleMessageToChannel(Update $update): Message
    {
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

        $username = $this->getUpdateUserFullName($this->update);
        $userId = $this->getUpdateUserId($this->update);

        $message = "[$username](tg://user?id=$userId)  Прочитайте правила группы и нажмите на кнопку";

        if ($this->disk->exists('channel-rules.json')) {
            $rule = json_decode($this->disk->get('channel-rules.json'), true);
            if ($rule['type'] == 'url') {
                $url = $rule['value'];
                $message = "[$username](tg://user?id=$userId)  Прочитайте [правила]($url) группы и нажмите на кнопку";
            } else {
                $message = $message . "\n \n" . $rule['value'];
            }
        }



        return $this->param->sendMessage([
            'chat_id' => data_get($update, 'message.chat.id'),
            'text' => $message,
            'reply_markup' => $inline_keyboard,
            'parse_mode' => 'Markdown'
        ]);
    }

    public function sendMessage($message = 'test message'): Message
    {
        return $this->param->sendMessage([
            'chat_id' => config('telegram.bots.my_account.id'),
            'text' => $message,
        ]);

    }

    public function saveLog($updates)
    {
        $this->disk->put('text.txt', $this->disk->get('text.txt')."\n".$updates);
    }

    private function saveRuleMessage(int $userId, $chatId, Message $sendingMessage)
    {
        $messages = $this->getRuleMessages();
        $messages[$chatId] = [$userId => $sendingMessage->toArray()];

        $this->disk->put('ruleMessage.json', json_encode($messages));
    }

    private function issetRuleMessage(int $userId, $chatId): bool
    {
        return \Arr::exists(data_get($this->getRuleMessages(), "$chatId", []), $userId);
    }

    private function getRuleMessages(): array
    {
        if ($this->disk->exists('ruleMessage.json')) {
            return (array) json_decode($this->disk->get('ruleMessage.json'), true);
        }

        return [];
    }

    private function isCallbackQuery(Update $update): bool
    {
        return \Arr::exists($update->toArray(), 'callback_query');
    }

    private function getUpdateUserId(Update $update): int
    {
        if ($this->isCallbackQuery($update)) {
            return data_get($update->toArray(), 'callback_query.from.id');
        }

        if ($this->updateIssetNewChatParticipant()) {
            return data_get($update->toArray(), 'message.new_chat_participant.id');
        }

        return data_get($update->toArray(), 'message.from.id');
    }

    private function runCallbackQuery()
    {
        $messages = $this->getRuleMessages();
        $user_id = $this->getUpdateUserId($this->update);
        $chatId = $this->update->getCallbackQuery()->getMessage()->getChat()->getId();
        if (data_get($messages, "$chatId.$user_id")) {
            $this->removeMessage(data_get($messages, "$chatId.$user_id.message_id"), $chatId);
            $chatMessages = data_get($messages, "$chatId");
            unset($chatMessages[$user_id]);
            $messages[$chatId] = $chatMessages;
            $this->disk->put('ruleMessage.json', json_encode($messages));
        }
    }

    private function removeMessage(int $messageId, $chatId): bool
    {
        try {
            (new MyApi(config('telegram.bots.mybot.token')))->deleteMessage([
                'chat_id' => $chatId,
                'message_id' => $messageId
            ]);

            return true;
        } catch(\Exception $e) {
            if ($e->getMessage() == "Bad Request: message to delete not found") {
                return true;
            }
	    $this->sendMessage("$chatId - $messageId \n" . $e->getMessage());
        }
	return false;
    }

    private function runChannelRule()
    {
        $chatId = $this->update->getMessage()->getChat()->getId();
        $messageId = $this->update->getMessage()->getMessageId();
        if ($this->updateIssetLeftChatParticipant()) {
            $this->removeMessage($messageId, $chatId);
            $messages = $this->getRuleMessages();
            $user_id = $this->getUpdateUserId($this->update);
            if (data_get($messages, "$chatId.$user_id")) {
                $this->removeMessage(\Arr::get($messages, "$chatId.$user_id.message_id"), $chatId);
                $chatMessages = data_get($messages, "$chatId");
                unset($chatMessages[$user_id]);
                $messages[$chatId] = $chatMessages;
                $this->disk->put('ruleMessage.json', json_encode($messages));
            }
        }

        if ($this->issetRuleMessage($this->getUpdateUserId($this->update), $chatId)) {
            $this->removeMessage($messageId, $chatId);
        } else {
            if (
                ! $this->updateIssetNewChatParticipant() ||
                $this->param->getMe()->getId() == $this->getUpdateUserId($this->update)
            ) {
                return ;
            }

            $this->removeMessage($messageId, $chatId);
            $sendingMessage = $this->sendRuleMessageToChannel($this->update);
            $this->saveRuleMessage($this->getUpdateUserId($this->update), $chatId, $sendingMessage);
        }
    }

    private function updateIssetNewChatParticipant(): bool
    {
        return (bool) data_get($this->update->toArray(), 'message.new_chat_participant.id', false);
    }

    private function updateIssetLeftChatParticipant(): bool
    {
        return (bool) data_get($this->update->toArray(), 'message.left_chat_participant.id', false);
    }

    private function getUpdateUserFullName(Update $update): string
    {
        if ($this->isCallbackQuery($update)) {
            return trim(data_get($update->toArray(), 'callback_query.from.first_name'));
        }

        if ($this->updateIssetNewChatParticipant()) {
            return trim(data_get($update->toArray(), 'message.new_chat_participant.first_name'));
        }

        return trim(data_get($update->toArray(), 'message.from.first_name') . ' ' . data_get($update->toArray(), 'message.from.last_name'));
    }

    private function isChatAdmin(): bool
    {
        return data_get($this->update->toArray(), 'message.chat.id') == config('telegram.bots.mybot.admin');

    }

    private function test()
    {
        $this->update->getMessage()->getText();
    }

    private function runAdminChat()
    {
        if (data_get($this->update->toArray(), 'message.entities.0.type') === 'bot_command') {
            if (
                data_get($this->update->toArray(), 'message.text') == '/start' ||
                data_get($this->update->toArray(), 'message.text') == '/help'
            ) {
                $this->sendMessage("السلام عليكم ورحمة الله وبركاته \n
                введите /set_rules чтобы ввести правила для группы");
            }

            if (data_get($this->update->toArray(), 'message.text') === '/set_rules') {
                $this->sendMessage('Следующим сообщением введите ссылку правила группы');

                $this->disk->put('admin-session.json', json_encode([
                    $this->getUpdateUserId($this->update) => [
                        'status' => "/set_rules"
                    ]
                ]));
            }

        } elseif (
            $this->disk->exists('admin-session.json') &&
            data_get(json_decode($this->disk->get('admin-session.json')), $this->getUpdateUserId($this->update) . '.status') == '/set_rules'
        ) {
            $message = $this->update->getMessage()->getText();
            $message_type = preg_match('%^https?://[^\s]+$%', $message) ? 'url': 'text';

            $this->disk->put('channel-rules.json', json_encode([
                'type' => $message_type,
                'value' => $message
            ]));
            $this->disk->put('admin-session.json', json_encode([]));

            $this->sendMessage('Ваши правила успешно сохранены!');
        }
    }
}
