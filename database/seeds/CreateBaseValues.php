<?php

use App\Enums\Telegram\TelegramBotTypeEnum;
use App\Models\TelegramBot;
use App\Models\TelegramUser;
use App\Services\TelegramBot\CreateTelegramBotService;
use App\Services\TelegramUser\CreateTelegramUserService;
use Illuminate\Database\Seeder;

class CreateBaseValues extends Seeder
{
    public function run()
    {
        \Illuminate\Database\Eloquent\Model::reguard();
        $bot = TelegramBot::query()
            ->where('token', config('telegram.bots.mybot.token'))
            ->where('type', TelegramBotTypeEnum::BASE)->first();

        if (is_null($bot)) {
            $bot = new TelegramBot([
                'token' => config('telegram.bots.mybot.token'),
                'type' =>  TelegramBotTypeEnum::BASE
            ]);
        }

        if (
            !$bot->admin()->exists()
        ) {
            $telegramUser = new TelegramUser();
            (new CreateTelegramUserService($telegramUser, ['chat_id' => config('telegram.bots.mybot.admin')], $bot))->run();
            $bot->telegram_user_id = $telegramUser->id;
        }

        (new CreateTelegramBotService($bot, []))->run();
    }
}
