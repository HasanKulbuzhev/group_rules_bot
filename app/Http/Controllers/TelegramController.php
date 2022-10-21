<?php

namespace App\Http\Controllers;

use App\Services\Api\MyApi;
use App\Services\TelegramBotService;
use Telegram\Bot\Api;
use Throwable;

class TelegramController extends Controller
{
    public function message(string $token): string
    {
        $service = new TelegramBotService(new MyApi($token));

        try {
            $service->run();
        } catch (Throwable  $e) {
            $service->sendMessage($e->getMessage());
            $service->saveLog($e);
        }


        return 'ok';
    }
}
