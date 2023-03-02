<?php

namespace App\Services\Telegram\Group;

use App\Enums\Telegram\MessageTypeEnum;
use App\Interfaces\Base\BaseService;
use App\Models\Hint\Hint;
use App\Services\Telegram\Base\BaseGroupChatService;

class AnswerSearchGroupService extends BaseGroupChatService implements BaseService
{
    protected array $rules = [
        MessageTypeEnum::VALUE_TYPE => [
            MessageTypeEnum::TEXT => 'message'
        ],
        MessageTypeEnum::OTHER => 'other',
    ];

    public function run(): bool
    {
        return parent::run();
    }

    public function message(): bool
    {
        $hints = $this->bot->hints()->ofTagName(str_word_count($this->updateService->data()->message->text, 1))->get();

        /** @var Hint $hint */
        foreach ($hints as $hint) {
            $this->bot->telegram->sendMessage([
                'chat_id'             => $this->updateService->data()->message->chat->id,
                'reply_to_message_id' => $this->updateService->data()->message->messageId,
                'text'                => $hint->text,
            ]);
        }

        return true;
    }
}
