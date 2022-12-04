<?php

namespace Tests\Feature;

use App\Enums\Telegram\MessageTypeEnum;
use App\Enums\Telegram\TelegramBotTypeEnum;
use App\Traits\Update\UpdateTrait;
use Tests\TestCase;

class BaseBotControllerTest extends TestCase
{
    use UpdateTrait;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testStart()
    {
        $response = $this->postJson(
            route('bot' . TelegramBotTypeEnum::BASE),
            $this->generateUpdate('/start', [MessageTypeEnum::COMMAND]),
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        );

        $response->assertStatus(200);
    }

    public function testHelp()
    {
        $response = $this->postJson(
            route('bot' . TelegramBotTypeEnum::BASE),
            $this->generateUpdate('/help', [MessageTypeEnum::COMMAND]),
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        );

        $response->assertStatus(200);
    }

    public function testCreateGroupRuleBot()
    {
        $responseCreateBot = $this->postJson(
            route('bot' . TelegramBotTypeEnum::BASE),
            $this->generateUpdate('/create_group_rule_bot', [MessageTypeEnum::COMMAND]),
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        );

        $responseCreateBot->assertStatus(200);

        $responseSetToken = $this->postJson(
            route('bot' . TelegramBotTypeEnum::BASE),
            $this->generateUpdate(config('telegram.bots.test_bot.token')),
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        );

        $responseSetToken->assertStatus(200);
    }

    public function testNewUser()
    {
        $response = $this->postJson(
            route('bot' . TelegramBotTypeEnum::BASE),
            $this->generateUpdate('/help', [MessageTypeEnum::COMMAND]),
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        );

        $response->assertStatus(200);
    }
}
