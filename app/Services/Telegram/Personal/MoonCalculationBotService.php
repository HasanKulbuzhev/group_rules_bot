<?php

namespace App\Services\Telegram\Personal;

use App\Enums\Telegram\ChatTypeEnum;
use App\Interfaces\Base\BaseService;
use App\Services\Base\Telegram\BaseRuleChatService;
use App\Services\Telegram\Update\TelegramUpdateService;

class MoonCalculationBotService extends BaseRuleChatService implements BaseService
{
    protected array $rules = [
        ChatTypeEnum::PRIVATE_CHAT => MoonCalculationPrivateService::class,
    ];
    public function run(): bool
    {
        $chatType = (new TelegramUpdateService($this->update))->getChatType();
        $isSave = true;

        DB::transaction(function() use ($isSave, $chatType) {
            $isSave = $this->runService($chatType) && $isSave;
        });

        return $isSave;
    }
}