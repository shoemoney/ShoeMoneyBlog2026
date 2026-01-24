<?php

namespace App\Models\WordPress;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class WpPost extends Model
{
    protected $connection = 'wordpress';

    protected $table = 'posts';

    protected $primaryKey = 'ID';

    public $timestamps = false;

    /**
     * Get the post meta for this post.
     */
    public function meta(): HasMany
    {
        return $this->hasMany(WpPostMeta::class, 'post_id', 'ID');
    }

    /**
     * Get the author of this post.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(WpUser::class, 'post_author', 'ID');
    }

    /**
     * Get the term taxonomies for this post.
     */
    public function termTaxonomies(): BelongsToMany
    {
        return $this->belongsToMany(
            WpTermTaxonomy::class,
            'term_relationships',
            'object_id',
            'term_taxonomy_id',
            'ID',
            'term_taxonomy_id'
        );
    }

    /**
     * Get the comments for this post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(WpComment::class, 'comment_post_ID', 'ID');
    }

    /**
     * Scope to only published posts.
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('post_status', 'publish');
    }

    /**
     * Scope to only posts (not pages or custom post types).
     */
    public function scopePosts(Builder $query): void
    {
        $query->where('post_type', 'post');
    }

    /**
     * Scope to only pages.
     */
    public function scopePages(Builder $query): void
    {
        $query->where('post_type', 'page');
    }
}
