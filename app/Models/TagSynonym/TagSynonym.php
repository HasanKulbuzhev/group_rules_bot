<?php

namespace App\Models\TagSynonym;

use App\Models\Tag\Tag;
use Carbon\Carbon;
use DB;
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
 * @property-read Tag $tag
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

    /**
     * @param Builder $builder
     * @param string  $words
     * @return Builder
     */
    public function scopeOfName(Builder $builder, string $words): Builder
    {
        return $builder->whereRaw('"?" LIKE CONCAT("%", tag_synonyms.name, "%")', [$words]);
    }

    public function scopeOfBot(Builder $builder, $id): Builder
    {
        return $builder->whereHas('tag', function (Builder $builder) use ($id) {
            $builder->whereHas('hints', function (Builder $builder) use ($id) {
                $builder->ofBot('bots', function (Builder $builder) use ($id) {
                    $builder->where('id', $id);
                });
            });
        });
    }
}
