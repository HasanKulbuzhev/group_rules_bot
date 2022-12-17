<?php

namespace App\Models\TagSynonym;

use App\Models\Tag\Tag;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class TagSynonym
 * @package App\Models\TagSynonym
 * @property int $id
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
}
