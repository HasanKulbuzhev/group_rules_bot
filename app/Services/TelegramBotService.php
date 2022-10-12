<?php

namespace App\Services;

use Illuminate\Filesystem\FilesystemAdapter;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Update;

class TelegramBotService
{
    private Api $param;
    private FilesystemAdapter $disk;

    /**
     * TelegramBotService constructor.
     * @param  Api  $param
     */
    public function __construct(Api $param)
    {
        $this->param = $param;
        $this->disk = \Storage::disk();
    }

    public function run(Update $updates)
    {
        $this->param->replyKeyboardHide();
        if (data_get($updates, 'message.chat.id') == config('telegram.chats.channel_test')) {
            $this->ruleChannel($updates);
            $this->saveLog($updates);
        }

    }

    public function ruleChannel(Update $updates): \Telegram\Bot\Objects\Message
    {
        return $this->param->sendMessage([
            'chat_id' => data_get($updates, 'message.chat.id'),
            'text' => 'read to rules group'
        ]);
    }

    public function testMessage()
    {
        $this->param->sendMessage([
            'chat_id' => config('telegram.bots.my_account.id'),
            'text' => 'test message'
        ]);

    }

    public function saveLog($updates)
    {
        $this->disk->put('text.txt', $this->disk->get('text.txt') . "\n" . $updates);
    }
}