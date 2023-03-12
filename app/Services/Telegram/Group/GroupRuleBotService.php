<?php

namespace App\Services\Telegram\Group;

use App\Enums\Telegram\ChatTypeEnum;
use App\Interfaces\Base\BaseService;
use App\Services\Base\Telegram\BaseRuleChatService;
use App\Services\Telegram\Personal\GroupRulePrivateChatService;
use App\Services\Telegram\Update\TelegramUpdateService;

class GroupRuleBotService extends BaseRuleChatService implements BaseService
{
    protected array $rules = [
        ChatTypeEnum::GROUP_CHAT => GroupRuleChatService::class,
        ChatTypeEnum::PRIVATE_CHAT => GroupRulePrivateChatService::class
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
