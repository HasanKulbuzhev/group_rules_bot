<?php

namespace App\Services\Telegram\Personal;

use App\Enums\Telegram\MessageTypeEnum;
use App\Interfaces\Base\BaseService;

class GroupRulePrivateChatService extends BaseRulePrivateChatService implements BaseService
{
    protected array $rules = [
        '/start' => 'getHelp',
        '/help' => 'getHelp',
        '/cancel' => 'cancel',
        '/set_rules' => 'setRules',
        MessageTypeEnum::OTHER => 'other',
    ];

    public function run(): bool
    {
        return parent::run();
    }

    protected function getHelp(): bool
    {
        $this->bot->telegram->sendMessage([
            'chat_id' => $this->update->message->chat->id,
            'text' => "введите
             /set_rules чтобы указать правила группы \n
             "
        ]);

        return true;
    }

    protected function setRules(): bool
    {
        if (\Cache::has($this->getUserStatePath())) {
            \DB::transaction(function() {
                $this->bot->setting->rule = $this->update->message->text;
                $isSave = $this->bot->setting->save();

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
        if (is_null(optional($this->bot->setting)->rule)) {
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
