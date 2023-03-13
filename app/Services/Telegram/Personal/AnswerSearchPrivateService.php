<?php


namespace App\Services\Telegram\Personal;


use App\Enums\Telegram\MessageTypeEnum;
use App\Interfaces\Base\BaseService;
use App\Models\Hint\Hint;
use App\Models\Tag\Tag;
use App\Models\TagSynonym\TagSynonym;
use App\Services\Telegram\Update\TelegramUpdateService;
use Exception;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Objects\File;

class AnswerSearchPrivateService extends BaseRulePrivateChatService implements BaseService
{
    protected array $rules = [
        '/start'               => 'getHelp',
        '/help'                => 'getHelp',
        '/cancel'              => 'cancel',
        '/get_setting'         => 'getSetting',
        '/get_hint'            => 'getHint',
        '/add_hint'            => 'addHint',
        '/update_hint'         => 'updateHint',
        '/delete_hint'         => 'deleteHint',
        '/get_tag'             => 'getTag',
        '/add_tag'             => 'addTag',
        '/update_tag'          => 'updateTag',
        '/delete_tag'          => 'deleteTag',
        '/get_synonym'         => 'getSynonym',
        '/add_synonym'         => 'addSynonym',
        '/update_synonym'      => 'updateSynonym',
        '/delete_synonym'      => 'deleteSynonym',
        '/add_answer'          => 'setAnswer',
        '/start_setting'       => 'setAnswer',
        '/set_word'            => 'setWord',
        '/set_synonyms'        => 'setSynonyms',
        '/get_backup'          => 'getBackup',
        '/restore'             => 'restore',
        MessageTypeEnum::OTHER => 'other',
    ];

    public function run(): bool
    {
        try {

            if (!$this->bot->isAdminTelegramId($this->updateService->getChatId())) {
                return $this->other();
            }

            return parent::run();
        } catch (Exception $exception) {
            $text = $exception->getMessage();
            $allErrorText = json_encode($exception->getTrace());

            throw new Exception("
                С ботом @{$this->bot->username} произошло что-то не так. \n
                $text. \n
                All error text : \n
                $allErrorText
                ");
        }
    }

    public function other(): bool
    {
        $hints = $this->bot->hints()->ofTagName($this->updateService->data()->message->text)->get();

        /** @var Hint $hint */
        foreach ($hints as $hint) {
            $this->bot->telegram->sendMessage([
                'chat_id'             => $this->updateService->data()->message->chat->id,
                'reply_to_message_id' => $this->updateService->data()->message->messageId,
                'text'                => $hint->text,
            ]);
        }

        return parent::other();
    }

    protected function cancel(): bool
    {
        $this->resetUserState();
        return true;
    }

    protected function getHelp(): bool
    {
        $inline_keyboard = [
            [
                [
                    'text'          => 'Показать настройки',
                    'callback_data' => json_encode([
                        'method' => '/get_setting',
                        'id'     => 'null',
                        'value'  => 'null',
                    ]),
                ],
                [
                    'text'          => 'Быстрые настройки',
                    'callback_data' => json_encode([
                        'method' => '/add_answer',
                        'id'     => 'null',
                        'value'  => 'null',
                    ]),
                ],
            ],
        ];
        $this->reply("введите
             /start_setting начать быструю настройку \n
             /get_setting - показать настройку \n
             /cancel - отменить любое действие \n
             ", $inline_keyboard);

        return true;
    }

    public function getSetting(): bool
    {
        $text = '';
        $inline_keyboard = [];
        foreach ($this->bot->hints as $hint) {
            $inline_keyboard[] = [
                [
                    'text'          => $hint->text,
                    'callback_data' => json_encode([
                        'method' => '/get_hint',
                        'id'     => $hint->id,
                        'value'  => $hint->id,
                    ])
                ]
            ];
            $text .= "\n Ответ: {$hint->text}";
            foreach ($hint->tags as $tag) {
                $synonyms = implode(', ', $tag->synonyms->pluck('name')->toArray());
                $text .= "\n Ключевое слово: {$tag->name} ({$synonyms})";
            }
            $text .= "\n ======================== \n";
        }

        $inline_keyboard[] = [
            [
                'text'          => 'Добавить Ответ',
                'callback_data' => json_encode([
                    'method' => '/add_hint',
                    'id'     => null,
                    'value'  => null,
                ])
            ]
        ];

        if (empty($text)) $text = "вы пока не настроили бот";

        $this->reply($text, $inline_keyboard);

        return true;
    }

    public function getHint(?Hint $hint = null): bool
    {
        $updateService = new TelegramUpdateService($this->update);
        if (is_null($hint)) {
            /** @var Hint $hint */
            $hint = $this->bot->hints()->find($updateService->getCallbackData()->id);
        }

        if (is_null($hint)) {
            $this->reply('Ответ (hint) не найден');
        }

        $text = 'Ответ: ' . $hint->text;

        $inline_keyboard = [
            [
                [
                    'text'          => 'Изменить',
                    'callback_data' => json_encode([
                        'method' => '/update_hint',
                        'id'     => $hint->id,
                        'value'  => $hint->id,
                    ]),
                ],
                [
                    'text'          => 'Удалить',
                    'callback_data' => json_encode([
                        'method' => '/delete_hint',
                        'id'     => $hint->id,
                        'value'  => $hint->id,
                    ]),
                ],
                [
                    'text'          => 'Назад',
                    'callback_data' => json_encode([
                        'method' => '/get_setting',
                        'id'     => $hint->id,
                        'value'  => $hint->id,
                    ]),
                ],
            ]
        ];

        foreach ($hint->tags as $tag) {
            $inline_keyboard[] = [
                [
                    'text'          => $tag->name,
                    'callback_data' => json_encode([
                        'method' => '/get_tag',
                        'id'     => $tag->id,
                        'value'  => $tag->id,
                    ])
                ]
            ];
            $synonyms = implode(', ', $tag->synonyms->pluck('name')->toArray());
            $text .= "\n Ключевое слово: {$tag->name} ({$synonyms})";
        }


        $inline_keyboard[] = [
            [
                'text'          => 'Добавить Ключевое слово',
                'callback_data' => json_encode([
                    'method' => '/add_tag',
                    'id'     => $hint->id,
                    'value'  => $hint->id,
                ]),
            ]
        ];

        $this->reply($text, $inline_keyboard);

        $this->resetUserState();

        return true;
    }

    public function addHint(): bool
    {
        if ($this->getUserState() === '/add_hint') {
            $hint = new Hint([
                'text' => $this->updateService->data()->message->text
            ]);
            $hint->owner_id = $this->bot->admin->id;
            $isSave = $hint->save();
            $isSave = $isSave && $this->bot->hints()->save($hint);

            if ($isSave) {
                $this->reply("
                    Ответ успешно сохранен! \n
                ");

                $this->getSetting();
            }

            return $isSave;
        } else {
            $hint = Hint::query()
                ->find($this->updateService->getCallbackData()->id);

            $this->setUserState('/add_hint', $hint);

            $this->reply("введите ответ, который вы хотите отдавать!");

            return true;
        }
    }

    public function updateHint(): bool
    {
        if (
            $this->getUserState() === '/update_hint'
        ) {
            /** @var Hint $hint */
            $hint = Cache::get($this->getUserStatePath(true));
            $hint->text = $this->update->message->text;
            $isSave = $hint->save();

            if ($isSave) {
                $this->reply("
                Ответ успешно сохранен! \n
                ");

                $this->getHint($hint);
            } else {
                $this->reply("
                Ответ не сохранён! \n
                ");
            }

            $this->resetUserState();

            return $isSave;
        } else {
            $hint = Hint::query()
                ->find($this->updateService->getCallbackData()->id);

            $this->setUserState('/update_hint', $hint);

            $this->reply("введите ответ, который вы хотите отдавать!");

            return true;
        }
    }

    public function deleteHint(): bool
    {
        /** @var Hint $hint */
        $hint = $this->bot->hints()->find($this->updateService->getCallbackData()->id);

        $hint->tags()->delete();
        $hint->delete();

        $this->reply('Успешно удалено');
        $this->getSetting();

        return true;
    }

    public function getTag(?Tag $tag = null): bool
    {
        if (is_null($tag)) {
            /** @var Tag $tag */
            $tag = Tag::query()
                ->find($this->updateService->getCallbackData()->id);
        }

        /** @var Hint $hint */
        $hint = $tag->hints()->ofBot($this->bot->id)->first();

        if (is_null($tag)) {
            $this->reply('Ключевое слово (tag) не найдено');
            return true;
        }

        $synonyms = implode(', ', $tag->synonyms->pluck('name')->toArray());
        $text = "\n Ключевое слово: {$tag->name} ({$synonyms})";
        $inline_keyboard = [
            [
                [
                    'text'          => 'Изменить',
                    'callback_data' => json_encode([
                        'method' => '/update_tag',
                        'id'     => $tag->id,
                        'value'  => $tag->id,
                    ]),
                ],
                [
                    'text'          => 'Удалить',
                    'callback_data' => json_encode([
                        'method' => '/delete_tag',
                        'id'     => $tag->id,
                        'value'  => $tag->id,
                    ]),
                ],
                [
                    'text'          => 'Назад',
                    'callback_data' => json_encode([
                        'method' => '/get_hint',
                        'id'     => $hint->id,
                        'value'  => $hint->id,
                    ]),
                ],
            ]
        ];

        foreach ($tag->synonyms as $synonym) {
            $inline_keyboard[] = [
                [
                    'text'          => $synonym->name,
                    'callback_data' => json_encode([
                        'method' => '/get_synonym',
                        'id'     => $synonym->id,
                        'value'  => $synonym->id,
                    ]),
                ]
            ];
        }

        $inline_keyboard[] = [
            [
                'text'          => 'Добавить Синоним',
                'callback_data' => json_encode([
                    'method' => '/add_synonym',
                    'id'     => $tag->id,
                    'value'  => $tag->id,
                ]),
            ]
        ];

        $this->reply($text, $inline_keyboard);

        return true;
    }

    public function addTag(): bool
    {
        if ($this->getUserState() === '/add_tag') {
            /** @var Hint $hint */
            $hint = Cache::get($this->getUserStatePath(true));
            $tag = new Tag([
                'name' => $this->updateService->data()->message->text
            ]);
            $isSave = $tag->save();
            $isSave = $isSave && $hint->tags()->save($tag);

            if ($isSave) {
                $this->reply("
                Ключевое слово успешно сохранено! \n
                ");

                $this->getHint($hint);
            }

            return $isSave;
        } else {
            $this->reply('Введите слово');

            $tag = Hint::query()
                ->find($this->updateService->getCallbackData()->id);
            $this->setUserState('/add_tag', $tag);

            return true;
        }
    }

    public function updateTag(): bool
    {
        if ($this->hasUserState()) {
            /** @var Tag $tag */
            $tag = Cache::get($this->getUserStatePath(true));
            $tag->name = $this->updateService->data()->message->text;
            $isSave = $tag->save();

            if ($isSave) {
                $this->reply("
                Ключевое слово успешно сохранено! \n
                ");

                $this->resetUserState();
                $this->getTag($tag);
            }

            return $isSave;
        } else {
            $this->reply("Введите слово");

            $tag = Tag::query()
                ->find($this->updateService->getCallbackData()->id);
            $this->setUserState('/update_tag', $tag);

            return true;
        }
    }

    public function deleteTag(): bool
    {
        /** @var tag $tag */
        $tag = Tag::query()
            ->find($this->updateService->getCallbackData()->id);

        /** @var Hint $hint */
        $hint = $tag->hints()->ofBot($this->bot->id)->first();

        $tag->delete();

        $this->reply('Успешно удалено');
        $this->getHint($hint);

        return true;
    }

    public function getSynonym(?TagSynonym $synonym = null): bool
    {
        /** @var TagSynonym $synonym */
        $synonym = $synonym ?? TagSynonym::query()
                ->find($this->updateService->getCallbackData()->id);

        if (is_null($synonym)) {
            $this->reply('слово (synonym) не найдено');
        }

        $text = $synonym->name;
        $inline_keyboard = [
            [
                [
                    'text'          => 'Изменить',
                    'callback_data' => json_encode([
                        'method' => '/update_synonym',
                        'id'     => $synonym->id,
                        'value'  => $synonym->id,
                    ]),
                ],
                [
                    'text'          => 'Удалить',
                    'callback_data' => json_encode([
                        'method' => '/delete_synonym',
                        'id'     => $synonym->id,
                        'value'  => $synonym->id,
                    ]),
                ],
                [
                    'text'          => 'Назад',
                    'callback_data' => json_encode([
                        'method' => '/get_tag',
                        'id'     => $synonym->tag->id,
                        'value'  => $synonym->tag->id,
                    ]),
                ],
            ]
        ];

        $this->reply($text, $inline_keyboard);

        return true;
    }

    public function addSynonym(): bool
    {
        if ($this->getUserState() === '/add_synonym') {
            /** @var Tag $tag */
            $tag = Cache::get($this->getUserStatePath(true));

            $synonym = new TagSynonym([
                'name' => $this->updateService->data()->message->text
            ]);
            $synonym->tag_id = $tag->id;
            $isSave = $synonym->save();

            if ($isSave) {
                $this->reply("
                Всё успешно сохранено! \n
                ");

                $this->getTag($tag);

                $this->resetUserState();
            }

            return $isSave;
        } else {
            $this->reply("Введите синоним слова!");

            $tag = Tag::query()
                ->find($this->updateService->getCallbackData()->id);

            $this->setUserState('/add_synonym', $tag);

            return true;
        }
    }

    public function updateSynonym(): bool
    {
        if ($this->hasUserState()) {
            /** @var TagSynonym $synonym */
            $synonym = Cache::get($this->getUserStatePath(true));
            $synonym->name = $this->updateService->data()->message->text;
            $isSave = $synonym->save();

            if ($isSave) {
                $this->reply("
                Слово успешно сохранено! \n
                ");

                $this->getSynonym($synonym);
            }

            return $isSave;
        } else {
            $this->reply("Введите синоним слова!");

            $synonym = TagSynonym::query()
                ->find($this->updateService->getCallbackData()->id);
            $this->setUserState('/update_synonym', $synonym);

            return true;
        }
    }

    public function deleteSynonym(): bool
    {
        /** @var TagSynonym $synonym */
        $synonym = TagSynonym::query()
            ->where('id', (new TelegramUpdateService($this->update))->getCallbackData()->id)->first();
        $tag = $synonym->tag;

        $isDelete = $synonym->delete();

        $this->getTag($tag);

        return $isDelete;
    }

    protected function setAnswer(): bool
    {
        if ($this->hasUserState()) {
            $hint = new Hint([
                'text' => $this->updateService->data()->message->text
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

            $this->setUserState('/add_answer');

            return true;
        }
    }

    protected function setWord(): bool
    {
        if ($this->hasUserState()) {
            /** @var Hint $hint */
            $hint = Cache::get($this->getUserStatePath(true));
            $tag = new Tag([
                'name' => $this->updateService->data()->message->text
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

            if ($this->updateService->data()->message->text !== '/skip') {
                /** @var Tag $tag */
                $tag = Cache::get($this->getUserStatePath(true));

                foreach (explode(',', $this->updateService->data()->message->text) as $name) {
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

    public function getBackup(): bool
    {
        $values = json_encode($this->bot->hints()->with('tags.synonyms')->get()->toArray());

        $this->reply(
            'Это ваш файл бекап, вы можете использовать его, чтобы передать данные с этого бота на другой',
            [],
            InputFile::createFromContents($values, 'backup.json')
        );

        return true;
    }

    public function restore(): bool
    {
        if ($this->hasUserState()) {
            /** @var File $file */
            $file = $this->bot->telegram->getFile([
                'file_id' => $this->updateService->data()->message->document->fileId
            ]);

            $content = $this->bot->telegram->downloadFile($file, 'test.json');

            /** @var array $hints */
            $hints = json_decode($content, true);

            if (is_null($hints)) {
                throw new Exception('Невалидный документ');
            }

            $isSave = true;

            /** @var array $hint */
            foreach ($hints as $hint) {
                $hint = new Hint([
                    'text' => $hint['text']
                ]);
                $hint->owner_id = $this->bot->admin->id;
                $isSave = $isSave && $hint->save();
                $isSave = $isSave && $this->bot->hints()->save($hint);

                /** @var array $tag */
                foreach ($hint['tags'] as $tag) {
                    $tag = new Tag([
                        'name' => $tag['name']
                    ]);

                    $isSave = $isSave && $tag->save();
                    $isSave = $isSave && $hint->tags()->save($tag);

                    /** @var array $synonym */
                    foreach ($tag['synonyms'] as $synonym) {
                        $synonym = new TagSynonym([
                            'name' => $synonym['name']
                        ]);
                        $synonym->tag_id = $tag->id;
                        $isSave = $synonym->save();
                    }
                }
            }

            if ($isSave) {
                $this->reply('Всё успешно сохранено!');
            } else {
                $this->reply('Ошибка. Что-то пошло не так!');
            }

            $this->resetUserState();

            return $isSave;
        } else {
            $this->reply('Отправьте документ с бекапом в формате .json');

            $this->setUserState('/restore');

            return true;
        }
    }
}
