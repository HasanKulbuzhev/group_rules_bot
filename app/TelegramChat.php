<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TelegramChat
 * @package App
 * @property int $id
 * @property int $telegram_id
 * @property string $title
 * @property string $username
 * @property string $description
 * @property string $rule_message
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class TelegramChat extends Model
{
    protected $fillable = [
        'telegram_id',
        'title',
        'username',
        'description',
        'rule_message',
    ];
}
