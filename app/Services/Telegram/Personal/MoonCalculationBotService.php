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


        try {
            $this->runService($chatType);
        } catch (\Exception $e) {
            $this->bot->telegram->sendMessage([
                'chat_id' => config('telegram.bots.my_account.id'),
                'text'    => 'test ' . $chatType . $e->getMessage()
            ]);
            return true;
        }

        return true;
    }
}