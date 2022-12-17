<?php

namespace App\Models\TagSynonym;

use App\Models\Tag\Tag;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class TagSynonym
 * @package App\Models\TagSynonym
 * @property int $id
 * @property int $tag_id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Tag[] $tag
 */
class TagSynonym extends Model
{
    protected $fillable = [
        'name'
    ];

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }

    public function scopeOfName(Builder $builder, $words): Builder
    {
        if (is_array($words)) {
            return $builder->whereIn('name', $words);
        }

        return $builder->where('name', $words);
    }
}
