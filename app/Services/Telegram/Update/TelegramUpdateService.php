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
        if ((bool)$this->getMessageValueTypes()) {
            return MessageTypeEnum::VALUE_TYPE;
        }

        if ((bool)$this->getMessageEventTypes()) {
            return MessageTypeEnum::EVENT_TYPE;
        }

        if ((bool)$this->getMessageGroupRuleTypes()) {
            return MessageTypeEnum::GROUP_RULE_TYPE;
        }

        if ((bool)$this->getMessageOwnerTypes()) {
            return MessageTypeEnum::OWNER_TYPE;
        }

        return MessageTypeEnum::OTHER;
    }

    public function getMessageInnerTypes($messageType = 'all'): array
    {
        $all = array_merge(
            $this->getMessageValueTypes(),
            $this->getMessageOwnerTypes(),
            $this->getMessageGroupRuleTypes(),
            $this->getMessageEventTypes(),
        );

        $types = [
            'all' => $all,
            MessageTypeEnum::VALUE_TYPE => $this->getMessageValueTypes(),
            MessageTypeEnum::OWNER_TYPE => $this->getMessageOwnerTypes(),
            MessageTypeEnum::GROUP_RULE_TYPE => $this->getMessageGroupRuleTypes(),
            MessageTypeEnum::EVENT_TYPE => $this->getMessageEventTypes(),
            MessageTypeEnum::OTHER => [],
        ];

        return $types[$messageType];
    }

    private function getMessageOwnerTypes(): array
    {
        $types = [];

        foreach (MessageTypeEnum::getOwnerTypes() as $type) {
            if (data_get($this->update->toArray(), 'message.' . $type, false)) {
                $types[] = $type;
            }
        }

        return $types;
    }

    private function getMessageValueTypes(): array
    {
        $types = [];

        foreach (MessageTypeEnum::getValueTypes() as $type) {
            if (data_get($this->update->toArray(), 'message.' . $type, false)) {
                $types[] = $type;
            }
        }

        return $types;
    }

    private function getMessageEventTypes(): array
    {
        $types = [];

        foreach (MessageTypeEnum::getEventTypes() as $type) {
            if ($type === MessageTypeEnum::CALLBACK_QUERY) {
                if (data_get($this->update->toArray(), 'callback_query', false)) {
                    $types[] = $type;
                    continue;
                }
            }

            if ($type === MessageTypeEnum::COMMAND) {
                if (data_get($this->update->toArray(), 'message.entities.0.type', false) === 'bot_command') {
                    $types[] = $type;
                    continue;
                }
            }

            if (data_get($this->update->toArray(), 'message.' . $type, false)) {
                $types[] = $type;
            }
        }

        return $types;
    }

    private function getMessageGroupRuleTypes(): array
    {
        $types = [];

        foreach (MessageTypeEnum::getGroupRuleTypes() as $type) {
            if (data_get($this->update->toArray(), 'message.' . $type, false)) {
                $types[] = $type;
            }
        }

        return $types;
    }
}
