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
        }

        if ($this->hasUserState() && !isset($method)) {
            $rule = Cache::get($this->getUserStatePath());
            $method = Arr::get($this->rules, $rule, MessageTypeEnum::OTHER);
        }

        if (!isset($method)) {
            $method = MessageTypeEnum::OTHER;
        }

        return $this->$method();
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

    protected function reply(string $message): void
    {
        $this->bot->telegram->sendMessage([
            'chat_id' => $this->update->message->chat->id,
            'text' => $message
        ]);
    }

    protected function getUserStatePath(bool $value = false): string
    {
        return CacheTypeEnum::PRIVATE_RULE_TYPE . ".{$this->bot->telegram_id}.{$this->update->message->from->id}." . (int) $value;
    }

    protected function setUserState(string $string, $value = null)
    {
        \Cache::put($this->getUserStatePath(), $string);
        if ($value) {
            \Cache::put($this->getUserStatePath(true), $value);
        }
    }

    protected function hasUserState(bool $value = false): bool
    {
        return \Cache::has($this->getUserStatePath($value));
    }

    protected function resetUserState()
    {
        Cache::delete($this->getUserStatePath());
        if ($this->hasUserState())
        Cache::delete($this->getUserStatePath(true));
    }
}
