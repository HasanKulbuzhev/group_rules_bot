<?php

namespace App\Services\Telegram\Personal;

use App\Enums\Telegram\MessageTypeEnum;
use App\Interfaces\Base\BaseService;
use Carbon\Carbon;
use Exception;

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
    }

    public function calculation()
    {
        try {
            $text = $this->update->message->text;

            $date = Carbon::now($text);
            $day = $date->day;
            $month = $date->month;
            $year = $date->year;
            $lunarNumber = ($year % 19) + 1;
            $lunarDay = ($lunarNumber * 11) - 14 + $day + $month;

            $this->reply(view('moonCalculationBot-calculate', [
                'lunarDay' => $lunarDay,
                'date' => $date->format('d.m.Y')
            ]));
        } catch (Exception $e) {
            return;
        }
    }
}