<?php

namespace App\Models;

use App\Models\Concerns\HasFeaturedImage;
use App\Services\ShortcodeProcessor;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Page extends Model
{
    use HasFactory;
    use HasFeaturedImage;
    use Searchable;

    protected $fillable = [
        'wordpress_id',
        'user_id',
        'title',
        'slug',
        'content',
        'menu_order',
    ];

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

    public function getUrlAttribute(): string
    {
        return '/' . $this->slug . '/';
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

    // Search (Laravel Scout / Algolia)

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => Str::limit(strip_tags($this->content ?? ''), 5000),
            'slug' => $this->slug,
        ];
    }

    public function searchableAs(): string
    {
        return config('scout.prefix') . 'pages';
    }
}
