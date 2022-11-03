<?php

namespace App\Services\Telegram\Update;

use App\Enums\Telegram\ChatTypeEnum;
use App\Enums\Telegram\MessageTypeEnum;
use Telegram\Bot\Objects\Update;

class TelegramUpdateService
{
    private Update $update;

    public function __construct(Update $update)
    {
        $this->update = $update;
    }

    public function getChatId(): int
    {
        return $this->getChatIdToDefault();
    }

    public function isChatGroup(): bool
    {
        return $this->getChatType() === ChatTypeEnum::GROUP_CHAT;
    }

    public function isChatPrivate(): bool
    {
        return $this->getChatType() === ChatTypeEnum::PRIVATE_CHAT;
    }

    private function getChatIdToDefault(): int
    {
        return $this->update->getMessage()->getChat()->getId();
    }

    public function getChatType(): string
    {
        return $this->update->getMessage()->getChat()->getType();
    }

    public function getMessageType(): string
    {
        if (data_get($this->update->toArray(), 'message.text', false)) {
            return MessageTypeEnum::TEXT;
        }

        if (data_get($this->update->toArray(), 'message.sticker', false)) {
            return MessageTypeEnum::STICKER;
        }

        if (data_get($this->update->toArray(), 'message.photo', false)) {
            return MessageTypeEnum::PHOTO;
        }

        if (data_get($this->update->toArray(), 'message.video', false)) {
            return MessageTypeEnum::VIDEO;
        }

        if (data_get($this->update->toArray(), 'callback_query', false)) {
            return MessageTypeEnum::CALLBACK_QUERY;
        }

        if (data_get($this->update->toArray(), 'message.group_chat_created', false)) {
            return MessageTypeEnum::GROUP_CHAT_CREATED;
        }

        if (data_get($this->update->toArray(), 'message.new_chat_participant', false)) {
            return MessageTypeEnum::NEW_CHAT_PARTICIPANT;
        }

        if (data_get($this->update->toArray(), 'message.left_chat_participant', false)) {
            return MessageTypeEnum::LEFT_CHAT_PARTICIPANT;
        }
    }
}
