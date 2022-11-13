<?php

namespace App\Models;

use App\Casts\TelegramBotCast;
use App\Services\Api\TelegramBotApi;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class TelegramBot
 * @package App
 * @property int $id
 * @property int $telegram_id
 * @property string $username
 * @property string $first_name
 * @property string $token
 * @property int $type
 * @property boolean $can_join_groups
 * @property boolean $can_read_all_group_messages
 * @property boolean $supports_inline_queries
 * @property int $telegram_user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property TelegramBotApi $telegram
 *
 * @property TelegramUser $admin
 * @property RuleBotSetting $setting
 */
class TelegramBot extends Model
{
    protected $fillable = [
        'username',
        'telegram_id',
        'first_name',
        'token',
        'type',
        'can_join_groups',
        'can_read_all_group_messages',
        'supports_inline_queries',
    ];

    protected $casts = [
        'telegram' => TelegramBotCast::class
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(TelegramUser::class, 'telegram_user_id');
    }

    public function setting(): HasOne
    {
        return $this->hasOne(RuleBotSetting::class);
    }

    public function getBotToken(): string
    {
        return $this->token;
    }
}
