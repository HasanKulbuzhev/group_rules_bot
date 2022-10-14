<?php

namespace App\Http\Controllers;

use App\Services\TelegramBotService;
use Telegram\Bot\Api;
use Throwable;

class TelegramController extends Controller
{
    public function message(): string
    {
        $service = new TelegramBotService(new Api(config('telegram.bots.mybot.token')));

        try {
            $service->run();
        } catch (Throwable  $e) {
            $service->sendMessage($e->getMessage());
            $service->saveLog($e);
        }


        return 'ok';
    }
}