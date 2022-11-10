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
        $updates = $this->bot->telegram->getUpdates(['allowed_updates' => []]);
        $this->bot->telegram->setAccessToken('asdfadsf');

        $this->update = end($updates);
//        $this->update = is_null($update)? $this->telegram->getWebhookUpdates() : $update;
    }
}
