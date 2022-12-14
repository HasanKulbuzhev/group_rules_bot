<?php

namespace App\Services\Telegram;

use App\Enums\Telegram\TelegramBotTypeEnum;
use App\Interfaces\Base\BaseService;
use App\Services\Base\Telegram\BaseRuleChatService;
use App\Services\Telegram\Base\BaseBotService;
use App\Services\Telegram\Group\GroupRuleBotService;

class RuleBotService extends BaseRuleChatService implements BaseService
{
    protected array $rules = [
        TelegramBotTypeEnum::BASE => BaseBotService::class,
        TelegramBotTypeEnum::GROUP_RULE => GroupRuleBotService::class,
    ];

    public function run(): bool
    {
        $botType = $this->bot->type;

        if (!in_array($botType, array_keys($this->rules))) {
            return true;
        }

        /** @var BaseService $ruleService */
        $ruleService = new $this->rules[$botType]($this->bot, $this->update);

        return $ruleService->run();
    }
}
