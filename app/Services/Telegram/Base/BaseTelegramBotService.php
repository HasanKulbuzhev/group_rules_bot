<?php

namespace App\Services\Telegram\Base;

use App\Enums\Telegram\ChatTypeEnum;
use App\Interfaces\Base\BaseServiceInterface;
use App\Services\Base\Telegram\BaseRuleTelegramChatService;
use App\Services\Telegram\Personal\TelegramBasePrivateChatService;
use App\Services\Telegram\Update\TelegramUpdateService;

class BaseTelegramBotService extends BaseRuleTelegramChatService implements BaseServiceInterface
{
    protected array $rules = [
        ChatTypeEnum::PRIVATE_CHAT => TelegramBasePrivateChatService::class,
    ];

    public function run(): bool
    {
        $chatType = (new TelegramUpdateService($this->update))->getChatType();

        /** @var BaseServiceInterface $ruleService */
        $ruleService = new $this->rules[$chatType]($this->bot, $this->update);

        return $ruleService->run();
    }
}
