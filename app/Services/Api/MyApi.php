<?php

namespace App\Services\Api;

use Telegram\Bot\Api;
use Telegram\Bot\Objects\Message;

class MyApi extends Api
{
    public function deleteMessage(array $params)
    {
        $response = $this->post('deleteMessage', $params);

        return new Message($response->getDecodedBody());
    }

}