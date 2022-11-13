<?php

namespace App\Services\TelegramUser;

use App\Enums\Telegram\TelegramBotTypeEnum;
use App\Interfaces\Base\BaseServiceInterface;
use App\Models\TelegramBot;
use App\Models\TelegramUser;

class CreateTelegramUserService implements BaseServiceInterface
{
    private TelegramUser $telegramUser;
    private array $data;

    public function __construct(TelegramUser $telegramUser, array $data, ?TelegramBot $bot = null)
    {
        $this->telegramUser = $telegramUser;
        if (is_null($bot)) {
            $bot = TelegramBot::query()->where('type', TelegramBotTypeEnum::BASE)->first();
        }
        $userValue = $bot->telegram->getChat([
            'chat_id' => config('telegram.bots.mybot.admin')
        ])->toArray();

        $this->data = array_merge(
            $userValue,
            ['telegram_id' => $userValue['id']],
            $data
            );
    }

    public function run(): bool
    {
        $this->telegramUser->fill($this->data);

        return $this->telegramUser->save();
    }
}
