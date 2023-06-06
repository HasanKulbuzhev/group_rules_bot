<?php

namespace App\Http\Controllers;

use App\Enums\Telegram\TelegramBotTypeEnum;
use App\Http\Requests\TelegramUpdateRequest;
use App\Services\Telegram\RuleBotService;
use App\Models\TelegramBot;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Telegram\Bot\Objects\Update;
use Throwable;

class TelegramController extends Controller
{
    public function baseBot(TelegramUpdateRequest $request)
    {
        /** @var TelegramBot $bot */
        $bot = TelegramBot::query()
            ->where('token', config('telegram.bots.mybot.token'))
            ->where('type', TelegramBotTypeEnum::BASE)->first();

        if (is_null($bot)) {
            throw (new Exception('Вы не создали базового бота!'));
        }

        try {
            (new RuleBotService($bot, new Update($request->post())))->run();
        } catch (Throwable  $e) {
            $baseBot = TelegramBot::query()->ofBaseBot()->first();
            $baseBot->telegram->sendMessage([
                'chat_id' => config('telegram.bots.my_account.id'),
                'text'    => (string)$e->getMessage()
            ]);
//            throw $e;
        }

        return 'ok';
    }

    public function groupRuleBot(TelegramUpdateRequest $request, string $token): string
    {
        /** @var TelegramBot $bot */
        $bot = TelegramBot::query()
            ->where('token', $token)
            ->where('type', TelegramBotTypeEnum::GROUP_RULE)
            ->first();

        if (is_null($bot)) {
            return 'not ok';
        }

        try {
            (new RuleBotService($bot, new Update($request->post())))->run();
        } catch (Exception $e) {
            $baseBot = TelegramBot::query()->ofBaseBot()->first();
            $baseBot->telegram->sendMessage([
                'chat_id' => config('telegram.bots.my_account.id'),
                'text'    => substr($e->getMessage(), 0, 3000) . "\n "
            ]);

            return 'error';
        }


        return 'ok';
    }

    public function searchAnswerBot(Request $request, string $token): string
    {
        /** @var TelegramBot $bot */
        $bot = TelegramBot::query()
            ->where('token', $token)
            ->where('type', TelegramBotTypeEnum::SEARCH_ANSWER)
            ->first();

        $validate = \Validator::make($request->all(), TelegramUpdateRequest::rule());

        if (is_null($bot) && $validate->fails()) {
            return 'not ok';
        }

        try {
            (new RuleBotService($bot, new Update($request->post())))->run();
        } catch (Exception $e) {
            $baseBot = TelegramBot::query()->ofBaseBot()->first();
            $baseBot->telegram->sendMessage([
                'chat_id' => config('telegram.bots.my_account.id'),
                'text'    => substr($e->getMessage(), 0, 3000) . "\n "
            ]);

            return 'error';
        }


        return 'ok';
    }

    public function moonCalculation(Request $request, string $token): string
    {
        /** @var TelegramBot $bot */
        $bot = TelegramBot::query()
            ->where('token', $token)
            ->where('type', TelegramBotTypeEnum::MOON_CALCULATION)
            ->first();

        $validate = \Validator::make($request->all(), TelegramUpdateRequest::rule());

        if (is_null($bot) && $validate->fails()) {
            return 'not ok';
        }

        try {
            (new RuleBotService($bot, new Update($request->post())))->run();
        } catch (Exception $e) {
            $baseBot = TelegramBot::query()->ofBaseBot()->first();
            $baseBot->telegram->sendMessage([
                'chat_id' => config('telegram.bots.my_account.id'),
                'text'    => substr($e->getMessage(), 0, 3000) . "\n "
            ]);

            return 'error';
        }


        return 'ok';
    }

    public function test(TelegramUpdateRequest $request)
    {
        dd(Carbon::make('04.08.2001')->format('d-m-Y'));
    }
}
