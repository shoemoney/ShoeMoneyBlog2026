<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'wordpress_id',
        'name',
        'slug',
    ];

    public function posts(): MorphToMany
    {
        return $this->morphedByMany(Post::class, 'taggable')->withTimestamps();
    }

    public function pages(): MorphToMany
    {
        return $this->morphedByMany(Page::class, 'taggable')->withTimestamps();
    }

    public function getUrlAttribute(): string
    {
        return '/tag/' . $this->slug . '/';
    }
}
