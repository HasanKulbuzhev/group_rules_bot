<?php

namespace App\Http\Controllers;

use App\Enums\Telegram\TelegramBotTypeEnum;
use App\Http\Requests\TelegramUpdateRequest;
use App\Models\Hint\Hint;
use App\Models\Tag\Tag;
use App\Services\Telegram\RuleBotService;
use App\Models\TelegramBot;
use Exception;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\HttpClients\GuzzleHttpClient;
use Telegram\Bot\Objects\Update;
use Throwable;

class TelegramController extends Controller
{
    public function baseBot(TelegramUpdateRequest $request)
    {
        /** @var TelegramBot $bot */
        $bot = TelegramBot::query()
            ->where('token', config('telegram.bots.mybot.token'))
            ->where('type', TelegramBotTypeEnum::BASE)->first();

        if (is_null($bot)) {
            throw (new Exception('Вы не создали базового бота!'));
        }

        try {
            (new RuleBotService($bot, new Update($request->post())))->run();
        } catch (Throwable  $e) {
            $baseBot = TelegramBot::query()->ofBaseBot()->first();
            $baseBot->telegram->sendMessage([
                'chat_id' => config('telegram.bots.my_account.id'),
                'text'    => (string)$e->getMessage()
            ]);
//            throw $e;
        }

        return 'ok';
    }

    public function groupRuleBot(TelegramUpdateRequest $request, string $token): string
    {
        /** @var TelegramBot $bot */
        $bot = TelegramBot::query()
            ->where('token', $token)
            ->where('type', TelegramBotTypeEnum::GROUP_RULE)
            ->first();

        if (is_null($bot)) {
            return 'not ok';
        }

        try {
            (new RuleBotService($bot, new Update($request->post())))->run();
        } catch (Exception $e) {
            $baseBot = TelegramBot::query()->ofBaseBot()->first();
            $baseBot->telegram->sendMessage([
                'chat_id' => config('telegram.bots.my_account.id'),
                'text'    => substr($e->getMessage(), 0, 3000) . "\n "
            ]);

            return 'error';
        }


        return 'ok';
    }

    public function searchAnswerBot(TelegramUpdateRequest $request, string $token): string
    {
        /** @var TelegramBot $bot */
        $bot = TelegramBot::query()
            ->where('token', $token)
            ->where('type', TelegramBotTypeEnum::SEARCH_ANSWER)
            ->first();

        if (is_null($bot)) {
            return 'not ok';
        }

        try {
            (new RuleBotService($bot, new Update($request->post())))->run();
        } catch (Exception $e) {
            $baseBot = TelegramBot::query()->ofBaseBot()->first();
            $baseBot->telegram->sendMessage([
                'chat_id' => config('telegram.bots.my_account.id'),
                'text'    => substr($e->getMessage(), 0, 3000) . "\n "
            ]);

            return 'error';
        }


        return 'ok';
    }

    public function test(TelegramUpdateRequest $request)
    {
        /** @var TelegramBot $bot */
        $bot = TelegramBot::query()
            ->where('token', config('telegram.bots.mybot.token'))
            ->where('type', TelegramBotTypeEnum::BASE)
            ->with('hints.tags')
            ->first();
        dd(Hint::query()->with('tags.synonyms')->get()->slice(10, 54));
        $file = $bot->telegram->getFile([
            'file_id' => 'BQACAgIAAxkBAAIJhGQPJvBY53Qv2eAHsi5NRl2HuLKTAAJQKAAC-655SDOoIKqyjZKpLwQ'
        ]);
//        $bot->telegram->downloadFile($file, 'test.json')
        $test = (new GuzzleHttpClient())->send(sprintf('https://api.telegram.org/file/bot%s/%s', $bot->token, $file->filePath), 'GET');
        $content = $test->getBody()->getContents();
        dd(json_decode($content, true));
//        dd($test);
        dd($test->getBody()->getContents());
        dd($bot->telegram->sendDocument($file, 'test.json'));

        dd(1);

//        $update = new Update($request->post());
//        $hint = new Hint([
//            'text' => 'asdfasdf' . random_int(1, 10000)
//        ]);
//        $hint->owner_id = $bot->admin->id;
//        $isSave = $hint->save();
//        $isSave = $isSave && $bot->hints()->save($hint);
//
//        $tag = new Tag([
//            'name' => 'jaklsdf' . random_int(1, 100000)
//        ]);
//        $isSave = $tag->save();
//        $isSave = $isSave && $hint->tags()->save($tag);
//        return $isSave;
//        dd($bot->hints->first()->bots);
        $hints = $bot->hints()->ofTagName('hasan')->get();
        dd($hints);
        /** @var Tag $tag */
        $tag = Tag::query()->first();
        /** @var Hint $hint */
        $hint = $tag->hints()->ofBot($bot->id)->first();
        dd($hint);

        $inline_keyboard = json_encode([
            'inline_keyboard' => [
                [
                    [
                        'text'          => 'text 1',
                        'callback_data' => 'test',
                    ],
                    [
                        'text'          => 'text 2',
                        'callback_data' => 'test2',
                    ],
                    [
                        'text'          => 'text 2',
                        'callback_data' => 'test2',
                    ],
                ],
                [
                    [
                        'text'          => 'text 2',
                        'callback_data' => 'test2',
                    ],
                ],
            ]
        ]);

        $bot->telegram->sendMessage([
            'chat_id'      => config('telegram.bots.mybot.admin'),
            'text'         => (string)json_encode($request->post()),
            'reply_markup' => $inline_keyboard,
        ]);

        return 'ok';
    }
}
