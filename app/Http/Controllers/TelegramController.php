<?php

namespace App\Http\Controllers;

use App\Enums\Telegram\TelegramBotTypeEnum;
use App\Services\Telegram\RuleTelegramBotService;
use App\Services\TelegramBot\CreateTelegramBotService;
use App\Models\TelegramBot;
use Throwable;

class TelegramController extends Controller
{
    public function groupRuleBot(string $token): string
    {
        $bot = TelegramBot::query()
            ->where('token', $token)
            ->where('type', TelegramBotTypeEnum::GROUP_RULE)
            ->first();

        if (is_null($bot)) {
            return 'not ok';
        }


        try {
            (new RuleTelegramBotService($bot))->run();
        } catch (Throwable  $e) {
            $bot->telegram->sendMessage([
                'chat_id' => config('telegram.bots.my_account.id'),
                'text' => (string) $e->getMessage()
            ]);
            throw $e;
        }


        return 'ok';
    }

    public function baseBot()
    {
        $bot = TelegramBot::query()
            ->where('token', config('telegram.bots.mybot.token'))
            ->where('type', TelegramBotTypeEnum::BASE)->first();

        if (is_null($bot)) {
            $bot = new TelegramBot([
                'token' => config('telegram.bots.mybot.token'),
                'type' =>  TelegramBotTypeEnum::BASE
            ]);

            (new CreateTelegramBotService($bot, []));
        }

        (new RuleTelegramBotService($bot))->run();

        return 'ok';
    }
}
