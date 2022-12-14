<?php

namespace App\Services\Telegram\Group;

use App\Enums\Telegram\ChatTypeEnum;
use App\Interfaces\Base\BaseService;
use App\Services\Base\Telegram\BaseRuleChatService;
use App\Services\Telegram\Personal\TelegramGroupRulePrivateChatService;
use App\Services\Telegram\Update\TelegramUpdateService;

class GroupRuleBotService extends BaseRuleChatService implements BaseService
{
    protected array $rules = [
        ChatTypeEnum::GROUP_CHAT => TelegramGroupRuleChatService::class,
        ChatTypeEnum::PRIVATE_CHAT => TelegramGroupRulePrivateChatService::class
    ];

    public function run(): bool
    {
        $chatType = (new TelegramUpdateService($this->update))->getChatType();

        if (!in_array($chatType, array_keys($this->rules))) {
            return true;
        }

        /** @var BaseService $ruleService */
        $ruleService = new $this->rules[$chatType]($this->bot, $this->update);

        return $ruleService->run();
    }
}
