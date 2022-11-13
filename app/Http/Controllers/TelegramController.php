<?php

namespace App\Http\Controllers;

use App\Enums\Telegram\TelegramBotTypeEnum;
use App\Models\TelegramUser;
use App\Services\Telegram\RuleTelegramBotService;
use App\Services\TelegramBot\CreateTelegramBotService;
use App\Models\TelegramBot;
use App\Services\TelegramBot\SyncTelegramUserTelegramBotService;
use App\Services\TelegramUser\CreateTelegramUserService;
use Exception;
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
        /** @var TelegramBot $bot */
        $bot = TelegramBot::query()
            ->where('token', config('telegram.bots.mybot.token'))
            ->where('type', TelegramBotTypeEnum::BASE)->first();

        if (is_null($bot)) {
            throw (new Exception('Вы не создали базового бота!'));
        }

        (new RuleTelegramBotService($bot))->run();

        return 'ok';
    }
}
