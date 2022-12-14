<?php

namespace App\Services\TelegramUser;

use App\Enums\Telegram\TelegramBotTypeEnum;
use App\Interfaces\Base\BaseService;
use App\Models\TelegramBot;
use App\Models\TelegramUser;

class CreateTelegramUserService implements BaseService
{
    private TelegramUser $telegramUser;
    private array $data;

    public function __construct(TelegramUser $telegramUser, array $data, ?TelegramBot $bot = null)
    {
        $this->telegramUser = $telegramUser;
        if (is_null($bot)) {
            $bot = TelegramBot::query()->where('type', TelegramBotTypeEnum::BASE)->first();
        }

        if (\Arr::get($data, 'chat_id', false)) {
            $userValue = $bot->telegram->getChat([
                'chat_id' => $data['chat_id']
            ])->toArray();
            $userValue['telegram_id'] = $userValue['id'];
        }

        $this->data = array_merge(
            $userValue ?? [],
            $data
            );
    }

    public function run(): bool
    {
        $this->telegramUser->fill($this->data);

        return $this->telegramUser->save();
    }
}
