<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TelegramUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'message' => ['required_without:callback_query', 'array'],
            'message.from' => ['required_with:message', 'array'],
            'message.from.chat' => ['required_with:message', 'array'],
            'callback_query' => ['array', 'required_without:message'],
            'callback_query.message' => ['required_with:callback_query', 'array'],
        ];
    }
}