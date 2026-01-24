<?php

namespace App\Models\WordPress;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class WpTermTaxonomy extends Model
{
    protected $connection = 'wordpress';

    protected $table = 'term_taxonomy';

    protected $primaryKey = 'term_taxonomy_id';

    public $timestamps = false;

    /**
     * Get the term for this taxonomy.
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(WpTerm::class, 'term_id', 'term_id');
    }

    /**
     * Get the posts for this term taxonomy.
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(
            WpPost::class,
            'term_relationships',
            'term_taxonomy_id',
            'object_id',
            'term_taxonomy_id',
            'ID'
        );
    }

    /**
     * Scope to only categories.
     */
    public function scopeCategories(Builder $query): void
    {
        $query->where('taxonomy', 'category');
    }

    /**
     * Scope to only tags.
     */
    public function scopeTags(Builder $query): void
    {
        $query->where('taxonomy', 'post_tag');
    }
}
