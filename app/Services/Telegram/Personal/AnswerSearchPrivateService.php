<?php


namespace App\Services\Telegram\Personal;


use App\Enums\Telegram\MessageTypeEnum;
use App\Interfaces\Base\BaseService;
use App\Models\Hint\Hint;
use App\Models\Tag\Tag;
use App\Models\TagSynonym\TagSynonym;

class AnswerSearchPrivateService extends BaseRulePrivateChatService implements BaseService
{
    protected array $rules = [
        '/start' => 'getHelp',
        '/help' => 'getHelp',
        '/get_setting' => 'getSetting',
        '/set_setting' => 'setAnswer',
        '/set_word' => 'setWord',
        '/set_synonyms' => 'setSynonyms',
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
             /set_setting начать настройку \n
             /get_setting - показать настройку \n
             "
        ]);

        return true;
    }

    public function getSetting(): bool
    {
        $text = '';
        foreach ($this->bot->hints as $hint) {
            $text .= "\n Ответ: {$hint->text}";
            foreach ($hint->tags as $tag) {
                $synonyms = implode(', ', $tag->synonyms->pluck('name')->toArray());
                $text .= "\n Ключевое слово: {$tag->name} ({$synonyms})";
            }
            $text .= "\n ======================== \n";
        }

        if (empty($text)) $text = "вы пока не настроили бот";

        $this->bot->telegram->sendMessage([
            'chat_id' => $this->update->message->chat->id,
            'text' => $text
        ]);

        return true;
    }

    protected function setAnswer(): bool
    {
        if ($this->hasUserState()) {
            $hint = new Hint([
                'text' => $this->update->message->text
            ]);
            $hint->owner_id = $this->bot->admin->id;
            $isSave = $hint->save();
            $isSave = $isSave && $this->bot->hints()->save($hint);

            if ($isSave) {
                $this->reply("
                Ответ успешно сохранен! \n
                Теперь введите слово, по которому будет отдаваться ответ.
                ");

                $this->setUserState('/set_word', $hint);
            }

            return $isSave;
        } else {
            $this->reply("введите ответ, который вы хотите отдавать!");

            $this->setUserState('/set_setting');

            return true;
        }
    }

    protected function setWord(): bool
    {
        if ($this->hasUserState()) {
            /** @var Hint $hint */
            $hint = \Cache::get($this->getUserStatePath(true));
            $tag = new Tag([
                'name' => $this->update->message->text
            ]);
            $isSave = $tag->save();
            $isSave = $isSave && $hint->tags()->save($tag);

            if ($isSave) {
                $this->reply("
                Ключевое слово успешно сохранено! \n
                Теперь бот, будет отправлять ответ каждый раз, когда в сообщении будет присутствовать это слово \n
                Дело за малым. Нужно указать синонимы для этого слова (другое написание, варианты ошибок и т.д.).
                Напишите через запятую все возможные варианты для этого слова. Либо пропустите этот пунк нажав на /skip
                ");

                $this->setUserState('/set_synonyms', $tag);
            }

            return $isSave;
        } else {
            $this->reply('Для начала нужно указать ответ');

            return true;
        }
    }

    protected function setSynonyms(): bool
    {
        if ($this->hasUserState()) {
            $isSave = true;

            if ($this->update->message->text !== '/skip') {
                /** @var Tag $tag */
                $tag = \Cache::get($this->getUserStatePath(true));

                foreach(explode(',', $this->update->message->text) as $name) {
                    $synonym = new TagSynonym([
                        'name' => trim($name)
                    ]);
                    $synonym->tag_id = $tag->id;
                    $isSave = $isSave && $synonym->save();
                }
            }

            if ($isSave) {
                $this->reply("
                Всё успешно сохранено! \n
                Можете протестировать бота
                ");

                $this->resetUserState();
            }

            return $isSave;
        } else {
            $this->reply('Для начала нужно указать слово');

            return true;
        }
    }
}
