<?php

namespace App\Services\Telegram;

use App\Enums\Telegram\TelegramBotTypeEnum;
use App\Interfaces\Base\BaseService;
use App\Services\Base\Telegram\BaseRuleChatService;
use App\Services\Telegram\Base\BaseBotService;
use App\Services\Telegram\Group\GroupRuleBotService;
use App\Services\Telegram\Personal\MoonCalculationBotService;
use App\Services\Telegram\Personal\SearchAnswerBotService;

class RuleBotService extends BaseRuleChatService implements BaseService
{
    protected array $rules = [
        TelegramBotTypeEnum::BASE => BaseBotService::class,
        TelegramBotTypeEnum::GROUP_RULE => GroupRuleBotService::class,
        TelegramBotTypeEnum::SEARCH_ANSWER => SearchAnswerBotService::class,
        TelegramBotTypeEnum::MOON_CALCULATION => MoonCalculationBotService::class,
    ];

    public function run(): bool
    {
        $botType = $this->bot->type;

        return $this->runService($this->bot->type);
    }
}
