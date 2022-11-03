<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TelegramBot
 * @package App
 * @property int $id
 * @property int $telegram_id
 * @property string $username
 * @property string $first_name
 * @property boolean $can_join_groups
 * @property boolean $can_read_all_group_messages
 * @property boolean $supports_inline_queries
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class TelegramBot extends Model
{
    protected $fillable = [
        'username',
        'telegram_id',
        'first_name',
        'can_join_groups',
        'can_read_all_group_messages',
        'supports_inline_queries',
    ];
}
