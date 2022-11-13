<?php

namespace App\Services\Telegram;

use App\Enums\Telegram\TelegramBotTypeEnum;
use App\Interfaces\Base\BaseServiceInterface;
use App\Services\Base\Telegram\BaseRuleTelegramChatService;
use App\Services\Telegram\Base\BaseTelegramBotService;
use App\Services\Telegram\Group\GroupRuleTelegramBotService;

class RuleTelegramBotService extends BaseRuleTelegramChatService implements BaseServiceInterface
{
    protected array $rules = [
        TelegramBotTypeEnum::BASE => BaseTelegramBotService::class,
        TelegramBotTypeEnum::GROUP_RULE => GroupRuleTelegramBotService::class,
    ];

    public function run(): bool
    {
        $botType = $this->bot->type;

        if (!in_array($botType, array_keys($this->rules))) {
            return true;
        }

        /** @var BaseServiceInterface $ruleService */
        $ruleService = new $this->rules[$botType]($this->bot, $this->update);

        return $ruleService->run();
    }
}
