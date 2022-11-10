<?php

use App\Enums\Telegram\TelegramBotTypeEnum;
use App\Http\Controllers\TelegramController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::post(config('telegram.bots.mybot.token') . '/webhook', [TelegramController::class, 'groupRuleBot']);
Route::post('bot/' . config('telegram.bots.mybot.token') . '/base', [TelegramController::class, 'baseBot'])->name('bot' . TelegramBotTypeEnum::BASE);
Route::post('bot/{token}/group-rule', [TelegramController::class, 'groupRuleBot'])->name('bot' . TelegramBotTypeEnum::GROUP_RULE);
