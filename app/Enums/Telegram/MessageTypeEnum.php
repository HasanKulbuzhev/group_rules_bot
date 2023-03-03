<?php

namespace App\Enums\Telegram;

use App\Enums\Enum;

class MessageTypeEnum extends Enum
{
    public const GROUP_RULE_TYPE = 'rule_type';
    // To groups and rules
    public const NEW_CHAT_MEMBERS = 'new_chat_members';
    public const MY_CHAT_MEMBER = 'my_chat_member';
    public const GROUP_CHAT_CREATED = 'group_chat_created';
    public const NEW_CHAT_PHOTO = 'new_chat_photo';
    public const DELETE_CHAT_PHOTO = 'delete_chat_photo';
    public const LEFT_CHAT_PARTICIPANT = 'left_chat_participant';
    public const VOICE_CHAT_STARTED = 'voice_chat_started';
    public const VOICE_CHAT_ENDED = 'voice_chat_ended';
    public const VOICE_CHAT_PARTICIPANTS_INVITED = 'voice_chat_participants_invited';

    public const OWNER_TYPE = 'owner_type';
    // Message Owner Type
    public const FORWARD_MESSAGE = 'forward_message';
    public const REPLY_TO_MESSAGE = 'reply_to_message';

    public const VALUE_TYPE = 'value_type';
    // value type
    public const STICKER = 'sticker';
    public const TEXT = 'text';
    public const PHOTO = 'photo';
    public const VIDEO = 'video';
    public const AUDIO = 'audio';
    public const VOICE = 'voice';
    public const ANIMATION = 'animation';
    public const DOCUMENT = 'document';
    public const VIDEO_NOTE = 'video_note';
    public const DICE = 'dice';
    public const PINNED_MESSAGE = 'pinned_message';
    public const CONTACT = 'contact';
    public const VENUE = 'venue';

    public const EVENT_TYPE = 'event_type';
    // event type
    public const CALLBACK_QUERY = 'callback_query';
    public const POLL = 'poll';
    public const INVOICE = 'invoice';
    public const SUCCESSFUL_PAYMENT = 'successful_payment';
    public const PASSPORT_DATA = 'passport_data';
    public const COMMAND = 'command';

    public const OTHER = 'other';

    public static function getValueTypes(): array
    {
        return [
            self::TEXT,
            self::STICKER,
            self::PHOTO,
            self::VIDEO,
            self::AUDIO,
            self::VOICE,
            self::ANIMATION,
            self::DOCUMENT,
            self::VIDEO_NOTE,
            self::DICE,
            self::PINNED_MESSAGE,
            self::CONTACT,
            self::VENUE,
        ];
    }

    public static function getEventTypes(): array
    {
        return [
            self::CALLBACK_QUERY,
            self::POLL,
            self::INVOICE,
            self::SUCCESSFUL_PAYMENT,
            self::PASSPORT_DATA,
            self::COMMAND,
        ];
    }

    public static function getOwnerTypes(): array
    {
        return [
            self::FORWARD_MESSAGE,
            self::REPLY_TO_MESSAGE,
        ];
    }

    public static function getGroupRuleTypes(): array
    {
        return [
            self::NEW_CHAT_MEMBERS,
            self::MY_CHAT_MEMBER,
            self::GROUP_CHAT_CREATED,
            self::NEW_CHAT_PHOTO,
            self::DELETE_CHAT_PHOTO,
            self::LEFT_CHAT_PARTICIPANT,
            self::VOICE_CHAT_STARTED,
            self::VOICE_CHAT_ENDED,
            self::VOICE_CHAT_PARTICIPANTS_INVITED,
        ];
    }
}
