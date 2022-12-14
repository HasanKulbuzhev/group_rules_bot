<?php

namespace App\Services\Telegram\Personal;

use App\Enums\Cache\CacheTypeEnum;
use App\Enums\Telegram\MessageTypeEnum;
use App\Interfaces\Base\BaseService;
use App\Services\Base\Telegram\BaseRuleChatService;
use App\Services\Telegram\Update\TelegramUpdateService;
use Arr;
use Cache;

class BaseRulePrivateChatService extends BaseRuleChatService implements BaseService
{
    public function run(): bool
    {
        $updateService = (new TelegramUpdateService($this->update));
        if (!in_array(MessageTypeEnum::TEXT, $updateService->getMessageInnerTypes())) {
            return true;
        }

        if ($this->update->message->from->id !== $this->bot->admin->telegram_id) {
            return $this->sendErrorNotAdmin();
        }

        if (in_array(MessageTypeEnum::COMMAND, $updateService->getMessageInnerTypes())) {
            $this->resetUserState();
            $method = Arr::get($this->rules, $this->update->message->text, MessageTypeEnum::OTHER);
            return $this->$method();
        }

        if (Cache::has($this->getUserStatePath())) {
            $rule = Cache::get($this->getUserStatePath());
            $method = Arr::get($this->rules, $rule, MessageTypeEnum::OTHER);
            return $this->$method();
        }

        return true;
    }

    protected function sendErrorNotAdmin(): bool
    {
        $this->bot->telegram->sendMessage([
            'chat_id' => $this->update->message->chat->id,
            'text' => "Вы не являетесь админом бота!"
        ]);

        return true;
    }

    protected function other(): bool
    {
        return true;
    }

    protected function replyToUser(string $message): void
    {
        $this->bot->telegram->sendMessage([
            'chat_id' => $this->update->message->from->id,
            'text' => $message
        ]);
    }

    protected function getUserStatePath(): string
    {
        return CacheTypeEnum::PRIVATE_RULE_TYPE . ".{$this->bot->telegram_id}.{$this->update->message->from->id}";
    }

    protected function setUserState(string $string)
    {
        \Cache::put($this->getUserStatePath(), $string);
    }

    protected function hasUserState(): bool
    {
        return \Cache::has($this->getUserStatePath());
    }

    protected function resetUserState()
    {
        Cache::delete($this->getUserStatePath());
    }
}
