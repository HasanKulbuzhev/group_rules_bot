<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RuleBotSetting
 * @package App\Models
 *
 * @property int $id
 * @property string $rules // правила для бота
 * @property int $timer // таймер в секундах
 * @property int $telegram_bot_id // таймер в секундах
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 */
class RuleBotSetting extends Model
{
    protected $fillable = [
        'rules',
        'timer',
        'telegram_bot_id',
    ];
}
