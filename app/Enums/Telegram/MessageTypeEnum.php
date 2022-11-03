<?php

namespace App\Enums\Telegram;

use App\Enums\Enum;

class MessageTypeEnum extends Enum
{
    public const NEW_CHAT_PARTICIPANT = 'new_chat_member';
    public const MY_CHAT_MEMBER = 'my_chat_member';
    public const STICKER = 'sticker';
    public const TEXT = 'text';
    public const PHOTO = 'photo';
    public const VIDEO = 'video';
    public const CALLBACK_QUERY = 'callback_query';
    public const GROUP_CHAT_CREATED = 'group_chat_created';
    public const LEFT_CHAT_PARTICIPANT = 'left_chat_participant';
}
