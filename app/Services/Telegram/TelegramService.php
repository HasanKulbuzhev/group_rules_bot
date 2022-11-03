<?php

namespace App\Services\Telegram;

use App\Enums\Telegram\ChatTypeEnum;
use App\Interfaces\Base\BaseServiceInterface;
use App\Services\Base\Telegram\BaseRuleTelegramChatService;
use App\Services\Telegram\Group\RuleTelegramGroupChatService;
use App\Services\Telegram\Update\TelegramUpdateService;

class TelegramService extends BaseRuleTelegramChatService implements BaseServiceInterface
{
    protected array $rules = [
        ChatTypeEnum::GROUP_CHAT => RuleTelegramGroupChatService::class,
        ChatTypeEnum::PRIVATE_CHAT => ''
    ];

    public function run(): bool
    {
        $chatType = (new TelegramUpdateService($this->update))->getChatType();

        /** @var BaseServiceInterface $ruleService */
        $ruleService = new $this->rules[$chatType]($this->telegram, $this->update);

        return $ruleService->run();
    }
}
