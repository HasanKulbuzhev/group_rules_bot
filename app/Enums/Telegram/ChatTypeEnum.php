<?php

namespace App\Enums\Telegram;

use App\Enums\Enum;

class ChatTypeEnum extends Enum
{
    public const GROUP_CHAT = 'supergroup';
    public const PRIVATE_CHAT = 'private';
}
