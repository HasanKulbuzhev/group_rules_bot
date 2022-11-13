<?php

namespace App\Services\Telegram\Group;

use App\Enums\Telegram\ChatTypeEnum;
use App\Interfaces\Base\BaseServiceInterface;
use App\Services\Base\Telegram\BaseRuleTelegramChatService;
use App\Services\Telegram\Personal\TelegramGroupRulePrivateChatService;
use App\Services\Telegram\Update\TelegramUpdateService;

class GroupRuleTelegramBotService extends BaseRuleTelegramChatService implements BaseServiceInterface
{
    protected array $rules = [
        ChatTypeEnum::GROUP_CHAT => TelegramGroupRuleChatService::class,
        ChatTypeEnum::PRIVATE_CHAT => TelegramGroupRulePrivateChatService::class
    ];

    public function run(): bool
    {
        $chatType = (new TelegramUpdateService($this->update))->getChatType();

        if (!in_array($chatType, $this->rules)) {
            return true;
        }

        /** @var BaseServiceInterface $ruleService */
        $ruleService = new $this->rules[$chatType]($this->bot, $this->update);

        return $ruleService->run();
    }
}
