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

        if (data_get($this->update, 'message.chat.id') == config('telegram.chats.channel_test')) {
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

    private function saveRuleMessage(int $userId, Message $sendingMessage)
    {
        $messages = $this->getRuleMessages();
        $messages[$userId] = $sendingMessage->toArray();

        $this->disk->put('ruleMessage.json', json_encode($messages));
    }

    private function issetRuleMessage(int $userId): bool
    {
        return \Arr::exists($this->getRuleMessages(), $userId);
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
        if (\Arr::exists($messages, $user_id)) {
            $this->removeMessage(\Arr::get($messages, $user_id . '.message_id'));
            unset($messages[$user_id]);
            $this->disk->put('ruleMessage.json', json_encode($messages));
        }
    }

    private function removeMessage(int $message_id)
    {
        (new MyApi(config('telegram.bots.mybot.token')))->deleteMessage([
            'chat_id' => config('telegram.chats.channel_test'),
            'message_id' => $message_id
        ]);
    }

    private function runChannelRule()
    {
        if ($this->updateIssetLeftChatParticipant()) {
            $this->removeMessage($this->update->getMessage()->getMessageId());
            $messages = $this->getRuleMessages();
            $user_id = $this->getUpdateUserId($this->update);
            if (\Arr::exists($messages, $user_id)) {
                $this->removeMessage(\Arr::get($messages, $user_id . '.message_id'));
                unset($messages[$user_id]);
                $this->disk->put('ruleMessage.json', json_encode($messages));
            }
        }

        if ($this->issetRuleMessage($this->getUpdateUserId($this->update))) {
            $this->removeMessage($this->update->getMessage()->getMessageId());
        } else {
            if (! $this->updateIssetNewChatParticipant()) {
                return ;
            }

            $this->removeMessage($this->update->getMessage()->getMessageId());
            $sendingMessage = $this->sendRuleMessageToChannel($this->update);
            $this->saveRuleMessage($this->getUpdateUserId($this->update), $sendingMessage);
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