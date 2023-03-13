<?php

namespace App\Services\Api;

use Exception;
use InvalidArgumentException;
use Telegram\Bot\Api;
use Telegram\Bot\HttpClients\GuzzleHttpClient;
use Telegram\Bot\Objects\BaseObject;
use Telegram\Bot\Objects\File;

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
            return (bool) $this->getMe()->id;
        } catch (Exception $exception) {
            return false;
        }
    }

    public function downloadFile($file, string $filename): string
    {
        $originalFilename = null;
        if (! $file instanceof File) {
            if ($file instanceof BaseObject) {
                $originalFilename = $file->get('file_name');

                // Try to get file_id from the object or default to the original param.
                $file = $file->get('file_id');
            }

            if (! is_string($file)) {
                throw new InvalidArgumentException(
                    'Invalid $file param provided. Please provide one of file_id, File or Response object containing file_id'
                );
            }

            $file = $this->getFile(['file_id' => $file]);
        }

        $response = (new GuzzleHttpClient())->send(sprintf('https://api.telegram.org/file/bot%s/%s', $this->getToken(), $file->filePath), 'GET');

        if (!$response->withStatus(200)) {
            return '';
        }

        return $response->getBody()->getContents();
    }
}
