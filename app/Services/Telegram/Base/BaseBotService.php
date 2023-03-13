<?php

namespace App\Services\Telegram\Base;

use App\Enums\Telegram\ChatTypeEnum;
use App\Interfaces\Base\BaseService;
use App\Services\Base\Telegram\BaseRuleChatService;
use App\Services\Telegram\Personal\TelegramBasePrivateChatService;
use App\Services\Telegram\Update\TelegramUpdateService;

class BaseBotService extends BaseRuleChatService implements BaseService
{
    protected array $rules = [
        ChatTypeEnum::PRIVATE_CHAT => TelegramBasePrivateChatService::class,
    ];

    public function run(): bool
    {
        $chatType = (new TelegramUpdateService($this->update))->getChatType();

        if (!in_array($chatType, array_keys($this->rules))) {
            return true;
        }

        return $this->runService($chatType);
    }
}
