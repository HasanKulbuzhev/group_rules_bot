<?php

namespace App\Services\Base\Telegram;

use App\Services\Api\MyApi;
use App\Services\Base\BaseRuleService;
use Telegram\Bot\Objects\Update;

abstract class BaseRuleTelegramChatService extends BaseRuleService
{
    protected MyApi $telegram;
    protected Update $update;

    public function __construct(MyApi $telegram, Update $update = null)
    {
        $this->telegram = $telegram;
        $updates = $this->telegram->getUpdates();
        $this->telegram->get
        dd($updates);
        $this->update = end($updates);
//        $this->update = is_null($update)? $this->telegram->getWebhookUpdates() : $update;
    }
}
