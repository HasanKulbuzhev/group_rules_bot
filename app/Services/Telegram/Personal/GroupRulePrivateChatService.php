<?php

namespace App\Services\Telegram\Personal;

use App\Enums\Telegram\MessageTypeEnum;
use App\Interfaces\Base\BaseService;
use App\Models\RuleBotSetting;
use Exception;

class GroupRulePrivateChatService extends BaseRulePrivateChatService implements BaseService
{
    protected array $rules = [
        '/start' => 'getHelp',
        '/help' => 'getHelp',
        '/cancel' => 'cancel',
        '/set_rules' => 'setRules',
        '/get_rules' => 'getRules',
        MessageTypeEnum::OTHER => 'other',
    ];

    public function run(): bool
    {
        try {
            return parent::run();
        } catch (Exception $exception) {
            $text = $exception->getMessage();
            $allErrorText = json_encode($exception->getTrace());

            $this->resetUserState();

            throw new Exception("
                С ботом @{$this->bot->username} произошло что-то не так. \n
                $text. \n
                All error text : \n
                $allErrorText
                ");
        }
    }

    protected function getHelp(): bool
    {
        $this->bot->telegram->sendMessage([
            'chat_id' => $this->update->message->chat->id,
            'text' => "введите
             /set_rules чтобы указать правила группы \n
             /get_rules чтобы показать правила группы \n
             "
        ]);

        return true;
    }

    protected function setRules(): bool
    {
        if (\Cache::has($this->getUserStatePath())) {
            \DB::transaction(function() {
                $setting = $this->bot->setting;

                if (is_null($this->bot->setting)) {
                    $setting = new RuleBotSetting();
                    $setting->rules;
                    $setting->telegram_bot_id = $this->bot->id;
                }

                $setting->rules = $this->update->message->text;
                $isSave = $setting->save();

                $this->bot->telegram->sendMessage([
                    'chat_id' => $this->update->message->chat->id,
                    'text' => "Правила успешно сохранены!"
                ]);

                if ($isSave) {
                    \Cache::delete($this->getUserStatePath());
                }
            });

            return true;
        } else {
            $this->bot->telegram->sendMessage([
                'chat_id' => $this->update->message->chat->id,
                'text' => "Введите правила для группы, либо ссылку на них!"
            ]);

            $this->setUserState('/set_rules');

            return true;
        }
    }

    public function getRules(): bool
    {
        if (is_null($this->bot->setting->rule)) {
            $text = 'Вы пока не настроили бот';
        } else {
            $text = $this->bot->setting->rule;
        }

        $this->bot->telegram->sendMessage([
            'chat_id' => $this->update->message->chat->id,
            'text' => $text
        ]);

        return true;
    }
}
