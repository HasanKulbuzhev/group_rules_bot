<?php

namespace App\Services\TelegramBot;

use App\Enums\Telegram\TelegramBotTypeEnum;
use App\Interfaces\Base\BaseServiceInterface;
use App\Models\TelegramBot;

class CreateTelegramBotService implements BaseServiceInterface
{
    private TelegramBot $bot;
    private array $data;

    public function __construct(TelegramBot $bot, array $data)
    {
        $this->bot = $bot;
        $botValue = $bot->telegram->getMe()->toArray();
        $this->data = array_merge($botValue, $data,  [
            'telegram_id' => $botValue['id'],
        ]);
    }

    public function run(): bool
    {
        $this->bot->fill($this->data);

        return $this->bot->save();
    }
}
