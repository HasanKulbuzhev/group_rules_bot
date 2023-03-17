<?php


namespace App\Services\Telegram\Base;


use App\Enums\Telegram\MessageTypeEnum;
use App\Services\Base\Telegram\BaseRuleChatService;
use App\Services\Telegram\Update\TelegramUpdateService;
use Arr;

abstract class BaseGroupChatService extends BaseRuleChatService
{
    public function run(): bool
    {
        $method = $this->getMethod();

        return $this->$method();
    }

    protected function getMethod()
    {
        $messageType = $this->updateService->getMessageType();
        $methods = Arr::get($this->rules, $messageType, MessageTypeEnum::OTHER);
        if (is_array($methods)) {
            foreach ($methods as $type => $methodName) {
                if (in_array($type, $this->updateService->getMessageInnerTypes($messageType))) {
                    $method = $methodName;
                    break;
                } else {
                    $method = Arr::get($this->rules, MessageTypeEnum::OTHER);
                }
            }
        } else {
            $method = $methods;
        }

        return $method;
    }

    protected function other(): bool
    {
        return true;
    }
}
