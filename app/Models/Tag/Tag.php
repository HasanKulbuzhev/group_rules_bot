<?php

namespace App\Models\Tag;

use App\Models\Hint\Hint;
use App\Models\TagSynonym\TagSynonym;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Tag
 * @package App\Models\Tag
 * @property int                          $id
 * @property string                       $name
 * @property Carbon                       $created_at
 * @property Carbon                       $updated_at
 *
 * @property-read Collection|TagSynonym[] $synonyms
 * @property-read Collection|Hint[]       $hints
 */
class Tag extends Model
{
    protected $fillable = [
        'name'
    ];

    public function hints(): BelongsToMany
    {
        return $this->belongsToMany(Hint::class,
            'hint_tag_assignment',
            'tag_id',
            'hint_id')
            ->withPivot(['require']);
    }

    public function synonyms(): HasMany
    {
        return $this->hasMany(TagSynonym::class);
    }

    /**
     * @param Builder $builder
     * @param string  $words
     * @return Builder
     */
    public function scopeOfName(Builder $builder, string $words): Builder
    {
        return $builder->where(function (Builder $builder) use ($words) {
            $builder->whereRaw('"' . $words . '" LIKE CONCAT("%", tags.name, "%")');

            $builder->orWhereHas('synonyms', function (Builder $builder) use ($words) {
                $builder->ofName($words);
            });
        });
    }

    public function scopeOfBot(Builder $builder, $id): Builder
    {
        return $builder->whereHas('hints', function (Builder $builder) use ($id) {
            $builder->ofBot($id);
        });
    }

    public function scopeOfHint(Builder $builder, $id): Builder
    {
        return $builder->whereHas('hints', function (Builder $builder) use ($id) {
            $builder->where('id', $id);
        });
    }
}
