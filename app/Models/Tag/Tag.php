<?php

namespace App\Models\Tag;

use App\Models\TagSynonym\TagSynonym;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Tag
 * @package App\Models\Tag
 * @property int $id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Collection|TagSynonym[] $synonyms
 */
class Tag extends Model
{
    protected $fillable = [
        'name'
    ];

    public function synonyms(): HasMany
    {
        return $this->hasMany(TagSynonym::class);
    }

    public function scopeOfName(Builder $builder, $words): Builder
    {
        if (is_array($words)) {
            $builder->whereIn('name', $words);
        } else {
            $builder->where('name', $words);
        }

        return $builder->orWhereHas('synonyms', function (Builder $builder) use ($words) {
            $builder->ofName($words);
        });
    }


}
