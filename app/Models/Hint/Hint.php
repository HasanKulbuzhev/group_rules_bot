<?php

namespace App\Models\Hint;

use App\Models\Tag\Tag;
use App\Models\TelegramBot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Hint
 * @package App\Models\Hint
 * @property int $id
 * @property int $owner_id
 * @property string $title
 * @property string $text
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Collection|Tag[] $tags
 * @property-read Collection|Tag[] $requireTags
 * @property-read Collection|Tag[] $notRequireTags
 * @property-read Collection|TelegramBot[] $bots
 * @property-read TelegramBot $bot
 * @property-read User $owner
 */
class Hint extends Model
{
    protected $fillable = [
        'title',
        'text',
    ];

    public function bots(): BelongsToMany
    {
        return $this->belongsToMany(TelegramBot::class, 'bot_hint_assignment', 'hint_id', 'bot_id');
    }

    public function bot()
    {
        return $this->bots()->first();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class,
            'hint_tag_assignment',
            'hint_id',
            'tag_id')
            ->withPivot(['require']);
    }

    public function requireTags(): BelongsToMany
    {
        return $this->tags()->wherePivot('require', true);
    }

    public function notRequireTags(): BelongsToMany
    {
        return $this->tags()->wherePivot('require', false);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @param Builder $builder
     * @param         $words
     * @return Builder
     */
    public function scopeOfTagName(Builder $builder, $words): Builder
    {
        return $builder->whereHas('tags', function (Builder $builder) use ($words) {
            $builder->ofName($words);
        });
    }

    /**
     * @param Builder $builder
     * @param         $id
     * @return Builder
     */
    public function scopeOfBot(Builder $builder, $id): Builder
    {
        return $builder->whereHas('bots', function (Builder $builder) use ($id) {
            $builder->where('id', $id);
        });
    }

}
