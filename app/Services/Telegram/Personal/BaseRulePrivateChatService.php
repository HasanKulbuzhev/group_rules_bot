<?php

namespace App\Services\Telegram\Personal;

use App\Enums\Cache\CacheTypeEnum;
use App\Enums\Telegram\MessageTypeEnum;
use App\Interfaces\Base\BaseService;
use App\Services\Base\Telegram\BaseRuleChatService;
use Arr;
use Cache;
use Telegram\Bot\FileUpload\InputFile;

class BaseRulePrivateChatService extends BaseRuleChatService implements BaseService
{
    protected array $allow_types = [
        MessageTypeEnum::TEXT,
        MessageTypeEnum::CALLBACK_QUERY,
    ];

    public function run(): bool
    {
        if (!$this->validate()) {
            return true;
        }

        $method = $this->getMethod();

        return $this->$method();
    }

    protected function getMethod(): string
    {
        $method = null;

        if (
        in_array(MessageTypeEnum::CALLBACK_QUERY, $this->updateService->getMessageInnerTypes())
        ) {
            $method = Arr::get($this->rules, $this->updateService->getCallbackData()->method);
        }

        if (
            in_array(MessageTypeEnum::COMMAND, $this->updateService->getMessageInnerTypes()) &&
            is_null($method)
        ) {
            $method = Arr::get($this->rules, $this->updateService->data()->message->text);
        }

        if ($this->hasUserState() && is_null($method)) {
            $method = Arr::get($this->rules, $this->getUserState());
        }

        if (is_null($method)) {
            $method = MessageTypeEnum::OTHER;
        }

        return $method;
    }

    protected function validate(): bool
    {
        $isAllow = !$this->allowTypes();

        return $isAllow;
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

    protected function allowTypes(): bool
    {
        foreach ($this->allow_types as $type) {
            if(!in_array($type, $this->updateService->getMessageInnerTypes())) {
                return true;
            }
        }

        return false;
    }

    protected function other(): bool
    {
        return true;
    }

    /**
     * @param string         $message
     * @param array|null     $inline_keyboard
     * @param InputFile|null $file
     * @throws \Telegram\Bot\Exceptions\TelegramSDKException <code>
     *       $inline_keyboard = [
     *       [
     *       [
     *       'text'                     => '',  // string - Текст кнопки
     *       'callback_data'            => '',  // string     - Название метода, который будет выполняться
     *       ]
     *       ]
     *       ]
     *       </code>
     */
    protected function reply(string $message, ?array $inline_keyboard = null, ?InputFile $file = null): void
    {
        $reply_markup = empty($inline_keyboard) ? null : json_encode(
            [
                'inline_keyboard' => $inline_keyboard
            ]);

        if (!is_null($file)) {
            $this->bot->telegram->sendDocument(
                [
                    'chat_id'      => $this->updateService->data()->message->chat->id,
                    'caption'      => $message,
                    'document'     => $file,
                    'reply_markup' => $reply_markup,

                ]);

            return ;
        }

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
        return CacheTypeEnum::PRIVATE_RULE_TYPE . ".{$this->bot->telegram_id}.{$this->updateService->data()->message->chat->id}." . (int)$value;
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
        if ($this->hasUserState(true))
            Cache::delete($this->getUserStatePath(true));
    }

    public function hasSecretCode(): bool
    {
        return \Cache::has($this->getSecretCodePath());
    }

    public function getSecretCodePath(): string
    {
        return CacheTypeEnum::PRIVATE_RULE_TYPE . 'secret_code_' . "{$this->bot->telegram_id}";
    }

    public function getSecretCode()
    {
        return \Cache::get($this->getSecretCodePath());
    }

    public function setSecretCode(string $code)
    {
        \Cache::put($this->getSecretCodePath(), $code);
    }

    public function deleteSecretCode()
    {
        \Cache::delete($this->getSecretCodePath());
    }
}
