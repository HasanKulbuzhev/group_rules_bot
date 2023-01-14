<?php

namespace App\Services\Base\Telegram;

use App\Interfaces\Base\BaseService;
use App\Models\TelegramBot;
use App\Services\Base\BaseRuleService;
use App\Services\Telegram\Update\TelegramUpdateService;
use Telegram\Bot\Objects\Update;

abstract class BaseRuleChatService extends BaseRuleService
{
    protected TelegramBot $bot;
    protected Update $update;
    protected TelegramUpdateService $updateService;

    public function __construct(TelegramBot $bot, Update $update = null)
    {
        $this->bot = $bot;
        $this->update = is_null($update) ? $bot->telegram->getWebhookUpdate() : $update;
        $this->updateService = new TelegramUpdateService($this->update);
    }

    protected function runService($value): bool
    {
        if (!in_array($value, array_keys($this->rules))) {
            return true;
        }

        /** @var BaseService $ruleService */
        $ruleService = new $this->rules[$value]($this->bot, $this->update);

        return $ruleService->run();
    }
}
