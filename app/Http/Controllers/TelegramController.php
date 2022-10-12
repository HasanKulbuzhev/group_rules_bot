<?php

namespace App\Http\Controllers;

use App\Services\TelegramBotService;
use Telegram\Bot\Api;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    public function message(): string
    {
        $service = new TelegramBotService(new Api(config('telegram.bots.mybot.token')));

        try {
            $updates = Telegram::getWebhookUpdate();
            $service->run($updates);
            $service->testMessage();
        } catch (\Exception $e) {
            $service->saveLog($e->getMessage());
        }


        return 'ok';
    }
}