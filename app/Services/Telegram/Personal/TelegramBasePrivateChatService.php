<?php

namespace App\Services\Telegram\Personal;

use App\Enums\Telegram\TelegramBotTypeEnum;
use App\Interfaces\Base\BaseServiceInterface;
use App\Models\TelegramBot;
use App\Services\TelegramBot\CreateTelegramBotService;

class TelegramBasePrivateChatService extends BaseRulePrivateTelegramChatService implements BaseServiceInterface
{
    protected array $rules = [
        '/start' => 'getHelp',
        '/help' => 'getHelp',
        '/create_group_rule_bot' => 'createGroupRuleBot',
    ];

    public function run(): bool
    {
        return parent::run();
    }

    protected function toOther(): bool
    {
        return true;
    }

    protected function getHelp(): bool
    {
        $this->replyToUser("введите \n
             /help чтобы показать это сообщение, \n
             /create_rule_group_bot чтобы создать бота для показа правил для вашей группы \n
             ");

        return true;
    }

    protected function createBot(int $type): bool
    {
        $token = $this->update->message->text;

        $newBot = new TelegramBot();
        $newBot->token = $token;
        if ($newBot->telegram->isValidToken()) {
            $isSave = (new CreateTelegramBotService($newBot, [
                'type' => TelegramBotTypeEnum::BASE
            ]))->run();
            if ($isSave) {
                $newBot->telegram->setWebhook([
                    'url' => route('bot'. $type)
                ]);
                $this->replyToUser("Ваш бот успешно сохранён!! \n Вы можете перейти к его настройке");
            }

            return true;
        } else {
            $this->replyToUser('Вы ввели не валидный токен! ');
        }

        $this->resetUserState();

        return true;
    }

    protected function createGroupRuleBot(): bool
    {
        if ($this->hasUserState()) {
            return $this->createBot(TelegramBotTypeEnum::GROUP_RULE);
        } else {
            $this->replyToUser('Введите токен бота');

            $this->setUserState('/create_group_rule_bot');

            return true;
        }
    }

}
