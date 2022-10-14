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
//            $service->sendMessage();
        } catch (Throwable  $e) {
            $service->saveLog($e);
        }


        return 'ok';
    }
}