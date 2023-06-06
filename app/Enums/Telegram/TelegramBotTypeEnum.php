<?php

namespace App\Enums\Telegram;

use App\Enums\Enum;

class TelegramBotTypeEnum extends Enum
{
    public const BASE = 0;
    public const GROUP_RULE = 1;
    public const SEARCH_ANSWER = 2;
    public const MOON_CALCULATION = 3;
}
