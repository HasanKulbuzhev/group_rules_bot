<?php

namespace App\Services\TelegramBot;

use App\Interfaces\Base\BaseServiceInterface;
use App\TelegramBot;

class CreateTelegramBotService implements BaseServiceInterface
{
    private TelegramBot $bot;
    private array $data;

    public function __construct(TelegramBot $bot, array $data)
    {
        $this->bot = $bot;
        $this->data = $data;
    }

    public function run(): bool
    {
        $this->bot->fill($this->data);

        return $this->bot->save();
    }
}
