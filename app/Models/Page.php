<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Page model - a thin proxy over the Post model.
 *
 * Pages are stored in the posts table with post_type = 'page'.
 * This model exists for backward compatibility so existing code
 * using Page:: continues to work seamlessly.
 */
class Page extends Post
{
    protected $table = 'posts';

    protected $attributes = [
        'post_type' => 'page',
        'status' => 'published',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('pages', function (Builder $builder) {
            $builder->where('post_type', 'page');
        });

        static::creating(function (Page $page) {
            $page->post_type = 'page';
            $page->status ??= 'published';
            $page->published_at ??= now();
        });
    }

    /**
     * Override searchable index so pages have their own Algolia index.
     */
    public function searchableAs(): string
    {
        return config('scout.prefix') . 'pages';
    }
}
