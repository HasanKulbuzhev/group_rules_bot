<?php


namespace App\Services\Telegram\Personal;


use App\Enums\Telegram\ChatTypeEnum;
use App\Interfaces\Base\BaseService;
use App\Services\Base\Telegram\BaseRuleChatService;
use App\Services\Telegram\Group\AnswerSearchGroupService;
use App\Services\Telegram\Update\TelegramUpdateService;

class SearchAnswerBotService extends BaseRuleChatService implements BaseService
{
    protected array $rules = [
        ChatTypeEnum::GROUP_CHAT => AnswerSearchGroupService::class,
        ChatTypeEnum::PRIVATE_CHAT => AnswerSearchPrivateService::class
    ];

    public function run(): bool
    {
        $chatType = (new TelegramUpdateService($this->update))->getChatType();

        return $this->runService($chatType);
    }
}
