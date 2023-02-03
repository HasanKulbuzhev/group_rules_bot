<?php

namespace App\Services\Telegram\Personal;

use App\Enums\Cache\CacheTypeEnum;
use App\Enums\Telegram\MessageTypeEnum;
use App\Interfaces\Base\BaseService;
use App\Services\Base\Telegram\BaseRuleChatService;
use Arr;
use Cache;

class BaseRulePrivateChatService extends BaseRuleChatService implements BaseService
{
    public function run(): bool
    {
        if (
            !in_array(MessageTypeEnum::TEXT, $this->updateService->getMessageInnerTypes()) &&
            !in_array(MessageTypeEnum::CALLBACK_QUERY, $this->updateService->getMessageInnerTypes())
        ) {
            return true;
        }

        $method = null;

        if (!$this->bot->isAdminTelegramId($this->updateService->getChatId())) {
            return $this->sendErrorNotAdmin();
        }

        if (
        in_array(MessageTypeEnum::CALLBACK_QUERY, $this->updateService->getMessageInnerTypes())
        ) {
            $method = Arr::get($this->rules, $this->updateService->getCallbackData()->method, MessageTypeEnum::OTHER);
        }

        if (
        in_array(MessageTypeEnum::COMMAND, $this->updateService->getMessageInnerTypes()) &&
        is_null($method)
        ) {
            $this->resetUserState();
            $method = Arr::get($this->rules, $this->updateService->data()->message->text);
        }

        if ($this->hasUserState() && is_null($method)) {
            $rule   = Cache::get($this->getUserStatePath());
            $method = Arr::get($this->rules, $rule, MessageTypeEnum::OTHER);
        }

        if (is_null($method)) {
            $method = MessageTypeEnum::OTHER;
        }

        return $this->$method();
    }

    protected function sendErrorNotAdmin(): bool
    {
        $this->bot->telegram->sendMessage(
            [
                'chat_id' => $this->updateService->data()->message->chat->id,
                'text'    => "Вы не являетесь админом бота!"
            ]);

        return true;
    }

    protected function other(): bool
    {
        return true;
    }

    /**
     * @param string     $message
     * @param array|null $inline_keyboard
     * @throws \Telegram\Bot\Exceptions\TelegramSDKException
     *
     * <code>
     * $inline_keyboard = [
     * [
     * [
     *       'text'                     => '',  // string - Текст кнопки
     *       'callback_data'            => '',  // string     - Название метода, который будет выполняться
     * ]
     * ]
     * ]
     * </code>
     */
    protected function reply(string $message, ?array $inline_keyboard = null): void
    {
        $reply_markup = empty($inline_keyboard) ? null : json_encode(
            [
                'inline_keyboard' => $inline_keyboard
            ]);
        foreach (mb_str_split($message, 3000) as $text) {
            $this->bot->telegram->sendMessage(
                [
                    'chat_id'      => $this->updateService->data()->message->chat->id,
                    'text'         => $text,
                    'reply_markup' => $reply_markup,

                ]);
        }
    }

    protected function getUserStatePath(bool $value = false): string
    {
        return CacheTypeEnum::PRIVATE_RULE_TYPE . ".{$this->bot->telegram_id}.{$this->updateService->data()->message->from->id}." . (int)$value;
    }

    protected function getUserState(bool $value = false)
    {
        return Cache::get($this->getUserStatePath($value));
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
