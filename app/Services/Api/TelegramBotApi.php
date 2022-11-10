<?php

namespace App\Services\Api;

use Exception;
use Telegram\Bot\Api;

class TelegramBotApi extends Api
{
    private static array $instances = [];

    private function __construct(string $token = null)
    {
        parent::__construct($token);
    }

    public static function getInstance(string $token): self
    {
        /** @var TelegramBotApi $instance */
        foreach (self::$instances as $instance) {
            if ($instance->getToken() === $token) {
                return $instance;
            }
        }
        $newInstance = new self($token);
        self::$instances[] = $newInstance;

        return $newInstance;
    }

    public function getToken(): string
    {
        return $this->getAccessToken();
    }

    public function isValidToken(): bool
    {
        try {
            return isset($this->getMe()->id);
        } catch (Exception $exception) {
            return false;
        }
    }
}
