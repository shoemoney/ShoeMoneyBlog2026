<?php

namespace App\Models;

use App\Models\Concerns\ClearsResponseCache;
use App\Models\Concerns\HasFeaturedImage;
use App\Services\ShortcodeProcessor;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Post extends Model
{
    use ClearsResponseCache;
    use HasFactory;
    use HasFeaturedImage;
    use Searchable;

    protected $fillable = [
        'wordpress_id',
        'user_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'status',
        'post_type',
        'menu_order',
        'published_at',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
        ];
    }

    // Relationships
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable')->withTimestamps();
    }

    public function categories(): MorphToMany
    {
        return $this->morphToMany(Category::class, 'categorizable')->withTimestamps();
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')->whereNotNull('published_at');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePosts($query)
    {
        return $query->where('post_type', 'post');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePages($query)
    {
        return $query->where('post_type', 'page');
    }

    public function isPage(): bool
    {
        return $this->post_type === 'page';
    }

    // URL helper for WordPress-style permalinks
    public function getUrlAttribute(): string
    {
        // Pages use simple slug-based URLs
        if ($this->post_type === 'page') {
            return '/' . $this->slug . '/';
        }

        // Posts use date-based URLs
        if (!$this->published_at) {
            return '#';
        }
        return sprintf(
            '/%d/%02d/%02d/%s/',
            $this->published_at->year,
            $this->published_at->month,
            $this->published_at->day,
            $this->slug
        );
    }

    /**
     * Get rendered content with shortcodes processed to HTML.
     */
    protected function renderedContent(): Attribute
    {
        return Attribute::make(
            get: function () {
                $processor = app(ShortcodeProcessor::class);
                return $processor->process($this->content ?? '');
            }
        )->shouldCache();
    }

    /**
     * Get estimated reading time in minutes (200 words per minute).
     */
    protected function readingTime(): Attribute
    {
        return Attribute::make(
            get: fn () => max(1, (int) ceil(str_word_count(strip_tags($this->content ?? '')) / 200))
        )->shouldCache();
    }

    // Search (Laravel Scout / Algolia)

    /**
     * Get the indexable data array for the model.
     *
     * Content is truncated to 5000 chars to stay under Algolia's 10KB limit.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'content' => Str::limit(strip_tags($this->content ?? ''), 5000),
            'slug' => $this->slug,
            'published_at' => $this->published_at?->timestamp,
        ];
    }

    /**
     * Determine if the model should be searchable.
     *
     * Only published posts with a published_at date are indexed.
     */
    public function shouldBeSearchable(): bool
    {
        if ($this->post_type === 'page') {
            return $this->status === 'published';
        }
        return $this->status === 'published' && $this->published_at !== null;
    }

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return config('scout.prefix') . 'posts';
    }
}
