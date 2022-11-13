<?php

namespace App\Services\Base\Telegram;

use App\Models\TelegramBot;
use App\Services\Base\BaseRuleService;
use Telegram\Bot\Objects\Update;

abstract class BaseRuleTelegramChatService extends BaseRuleService
{
    protected TelegramBot $bot;
    protected Update $update;

    public function __construct(TelegramBot $bot, Update $update = null)
    {
        $this->bot = $bot;
        $this->update = is_null($update) ? $bot->telegram->getWebhookUpdate() : $update;
    }
}
