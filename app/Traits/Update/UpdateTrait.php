<?php

namespace App\Traits\Update;

use App\Enums\Telegram\MessageTypeEnum;

trait UpdateTrait
{
    public function generateUpdate(string $text, array $types = []): array
    {
        $data =  [
            'update_id' => rand(1, 10000000),
            'message' => [
                'message_id' => 39,
                'from' => [
                    'id' => 643803968,
                    'is_bot' => false,
                    'first_name' => 'Хьасан',
                    'last_name' => '(vince)',
                    'username' => 'Hasan_vince',
                    'language_code' => 'ru',
                ],
                'chat' => [
                    'id' => 643803968,
                    'first_name' => 'Хьасан',
                    'last_name' => '(vince)',
                    'username' => 'Hasan_vince',
                    'type' => 'private',
                ],
                'date' => 585063057,
                'text' => $text,
            ]
        ];

        foreach ($types as $type) {
            if ($type === MessageTypeEnum::COMMAND) {
                $data['message'] = array_merge($data['message'], [
                    'entities' => [
                        0 => [
                            'type' => 'bot_command',
                        ],
                    ]
                ]);
            }
        }

        return $data;
    }
}
