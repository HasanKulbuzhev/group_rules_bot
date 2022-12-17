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

    private function message(): bool
    {
        /** @var Hint $hint */
        $hint = $this->bot->hints()->ofTagName($this->update->message->text)->first();
        if ($hint) {
            $this->bot->telegram->sendMessage([
                'chat_id' => $this->update->message->chat->id,
                'reply_to_message_id' => $this->update->message->messageId,
                'text' => $hint->text,
            ]);
        }

        return true;
    }
}
