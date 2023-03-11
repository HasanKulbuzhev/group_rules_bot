<?php

namespace App\Services\Telegram\Personal;

use App\Enums\Telegram\TelegramBotTypeEnum;
use App\Interfaces\Base\BaseService;
use App\Models\TelegramBot;
use App\Models\TelegramUser;
use App\Services\TelegramBot\CreateTelegramBotService;
use App\Services\TelegramUser\CreateTelegramUserService;

class TelegramBasePrivateChatService extends BaseRulePrivateChatService implements BaseService
{
    protected array $rules = [
        '/start' => 'getHelp',
        '/help' => 'getHelp',
        '/create_group_rule_bot' => 'createGroupRuleBot',
        '/create_search_answer_bot' => 'createSearchAnswerBot',
        'other' => 'other',
    ];

    public function run(): bool
    {
        return parent::run();
    }

    protected function getHelp(): bool
    {
        $this->reply(view('base_bot_help'));

        return true;
    }

    protected function createBot(int $type): bool
    {
        $token = $this->update->message->text;

        $newBot = TelegramBot::query()->where('token', $token)->first() ?? new TelegramBot();
        $newBot->token = $token;
        if ($newBot->telegram->isValidToken()) {
            \DB::transaction(function () use ($newBot, $type) {
                $user = TelegramUser::query()->where('telegram_id', $this->update->message->from->id)->first();
                if (is_null($user)) {
                    $user = new TelegramUser([
                        'telegram_id' => $this->updateService->getChatId()
                    ]);
                }
                $isSave = (new CreateTelegramUserService($user, $this->update->getChat()->toArray()))->run();
                $newBot->telegram_user_id = $user->id;

                $isSave = $isSave && (new CreateTelegramBotService($newBot, [
                        'type' => $type
                    ]))->run();

                if ($isSave) {
                    $newBot->telegram->setWebhook([
                        'url' => route('bot'. $type, ['token' => $newBot->token])
                    ]);
                    $this->reply("Ваш бот {$newBot->username} успешно сохранён!! \n Вы можете перейти к его настройке");
                }
            });

            return true;
        } else {
            $this->reply('Вы ввели не валидный токен! ');
        }

        $this->resetUserState();

        return true;
    }

    protected function createGroupRuleBot(): bool
    {
        if ($this->hasUserState()) {
            return $this->createBot(TelegramBotTypeEnum::GROUP_RULE);
        } else {
            $this->reply('Введите токен бота');

            $this->setUserState('/create_group_rule_bot');

            return true;
        }
    }

    protected function createSearchAnswerBot(): bool
    {
        if ($this->hasUserState()) {
            return $this->createBot(TelegramBotTypeEnum::SEARCH_ANSWER);
        } else {
            $this->reply('Введите токен бота');

            $this->setUserState('/create_search_answer_bot');

            return true;
        }
    }
}
