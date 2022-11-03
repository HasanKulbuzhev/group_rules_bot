<?php

namespace App\Http\Controllers;

use App\Services\Api\MyApi;
use App\Services\Telegram\TelegramService;
use App\Services\TelegramBot\CreateTelegramBotService;
use App\Services\TelegramBotService;
use App\TelegramBot;
use App\TelegramUser;
use Cache;
use Telegram\Bot\Api;
use Throwable;

class TelegramController extends Controller
{
    public function message(string $token): string
    {
//        $service = new TelegramBotService(new MyApi($token));
        $bot = new TelegramBot();
        (new CreateTelegramBotService($bot, []))->run();
        (new CreateTelegramUserService(new TelegramUser(), $bot->telegram->get))->run();
        (new TelegramService(new MyApi($token)))->run();
        dd(2);

        try {
            $service->run();
        } catch (Throwable  $e) {
            $service->sendMessage($e->getMessage());
            $service->saveLog($e);
        }


        return 'ok';
    }
}
