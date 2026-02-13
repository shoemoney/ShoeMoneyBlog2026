<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MenuItem extends Model
{
    protected $fillable = [
        'label',
        'url',
        'type',
        'linkable_type',
        'linkable_id',
        'parent_id',
        'position',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('position');
    }

    /**
     * Resolve the URL based on type.
     */
    public function getResolvedUrlAttribute(): string
    {
        return match ($this->type) {
            'page' => $this->linkable ? '/' . $this->linkable->slug : '#',
            'category' => $this->linkable ? '/category/' . $this->linkable->slug : '#',
            default => $this->url ?? '#',
        };
    }
}
