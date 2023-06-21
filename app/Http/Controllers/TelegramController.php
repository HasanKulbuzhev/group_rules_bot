<?php

namespace App\Http\Controllers;

use App\Enums\LunarMonth;
use App\Enums\Telegram\TelegramBotTypeEnum;
use App\Http\Requests\TelegramUpdateRequest;
use App\Services\Telegram\RuleBotService;
use App\Models\TelegramBot;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
        $date = Carbon::make('21.06.2023');
        $day = $date->day;
        $month = $date->month;
        $year = $date->year;
        $lunarNumber = ($year % 19) + 1;
        $lunarDay = (($lunarNumber * 11) - 14 + $day + $month) % 30;
        $lunarDay = $lunarDay === 0? 30: $lunarDay;

        /**
         * step 1:
         * hijra 28
         * lunnar day 1
         *
         * step 2:
         * hijra 22
         * lunar day 24
         *
         * step 3:
         * hijra 1
         * lunnar day 28
         */

        $data = Http::get(sprintf('http://api.aladhan.com/v1/gToH/%s-%s-%s', $day, $month, $year))->object()->data->hijri;
        $hijraDay = (int) $data->day;
//        dd((int) $data->day);
//        dd($data);
        $lunarMonth = __('hijra.' . LunarMonth::getKey($data->month->number));
        $lunarYear = $data->year;

        /** Если лунный день по нашему расчёт на следующем месяце от календаря */
        if (
            $hijraDay > 25 ||
            $lunarDay < 5
        ) {
            if ($lunarMonth === 12) {
                $lunarMonth = 1;
                $lunarYear = (int) $lunarYear + 1;
            } else {
                $lunarMonth++;
            }
        }

        /** Если лунный день по нашему расчёт на предыдущем месяце от календаря */
        if (
            $lunarDay > 25 ||
            $hijraDay < 5
        ) {
            if ($lunarMonth === 1) {
                $lunarMonth = 12;
                $lunarYear = (int) $lunarYear - 1;
            } else {
                $lunarMonth--;
            }
        }

        return view('moonCalculationBot-calculate', [
            'lunarDay' => $lunarDay,
            'lunarMonth' => $lunarMonth,
            'lunarYear' => $lunarYear,
            'date' => $date->format('d.m.Y'),
        ]);
//        dd(__('hijra'));
        dd($data->data->hijri);
        dd($lunarYear);
        dd($lunarDay);

    }
}
