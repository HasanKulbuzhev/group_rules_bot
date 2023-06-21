<?php

namespace App\Services\Telegram\Personal;

use App\Enums\LunarMonth;
use App\Enums\Telegram\MessageTypeEnum;
use App\Interfaces\Base\BaseService;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Http;

class MoonCalculationPrivateService extends BaseRulePrivateChatService implements BaseService
{
    protected array $rules = [
        MessageTypeEnum::OTHER => 'other',
        '/start' => 'getHelp',
        '/help' => 'getHelp',
    ];

    public function run(): bool
    {
        return parent::run();
    }

    public function other(): bool
    {
        $this->calculation();

        return true;
    }

    public function getHelp()
    {
        $this->reply(view('moonCalculationBotHelp'));

        return true;
    }

    public function calculation()
    {
        try {
            $text = $this->update->message->text;

            $date = Carbon::make($text);
            $day = $date->day;
            $month = $date->month;
            $year = $date->year;
            $lunarNumber = ($year % 19) + 1;
            $lunarDay = (($lunarNumber * 11) - 14 + $day + $month) % 30;
            $lunarDay = $lunarDay === 0? 30: $lunarDay;

            $data = Http::get(sprintf('http://api.aladhan.com/v1/gToH/%s-%s-%s', $day, $month, $year))->object()->data->hijri;
            $hijraDay = (int) $data->day;
            $lunarMonthNumber = $data->month->number;
            $lunarYear = $data->year;

            /**
             * Если лунный день по нашему расчёт на следующем месяце от календаря
             * hijra 28
             * lunnar day 1
             */
            if (
                $hijraDay > 25 ||
                $lunarDay < 5
            ) {
                if ($lunarMonthNumber === 12) {
                    $lunarMonthNumber = 1;
                    $lunarYear = (int) $lunarYear + 1;
                } else {
                    $lunarMonthNumber++;
                }
            }

            /**
             * Если лунный день по нашему расчёт на предыдущем месяце от календаря
             * hijra 1
             * lunnar day 28
             */
            if (
                $lunarDay > 25 ||
                $hijraDay < 5
            ) {
                if ($lunarMonthNumber === 1) {
                    $lunarMonthNumber = 12;
                    $lunarYear = (int) $lunarYear - 1;
                } else {
                    $lunarMonthNumber--;
                }
            }

            $this->reply(view('moonCalculationBot-calculate', [
                'lunarDay' => $lunarDay,
                'lunarMonth' => __('hijra.' . LunarMonth::getKey($lunarMonthNumber)),
                'lunarYear' => $lunarYear,
                'date' => $date->format('d.m.Y'),
            ]));
        } catch (Exception $e) {
            return;
        }
    }

    private function getApiHidjratData(int $day, int $month, int $year)
    {
//        dd(Http::post(sprintf('http://api.aladhan.com/v1/gToH/%s-%s-%s', $day, $month, $year))->json());
//        Http::post(sprintf('http://api.aladhan.com/v1/gToH/%s-%s-%s', $day, $month, $year));
    }
}
