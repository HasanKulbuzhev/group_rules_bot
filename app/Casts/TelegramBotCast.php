<?php

namespace App\Casts;

use App\Services\Api\TelegramBotApi;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class TelegramBotCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): TelegramBotApi
    {
        return TelegramBotApi::getInstance($model->getBotToken());
    }

    public function set($model, string $key, $value, array $attributes): array
    {
        return [];
    }
}
