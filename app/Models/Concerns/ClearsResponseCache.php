<?php

namespace App\Models\Concerns;

use Spatie\ResponseCache\Facades\ResponseCache;

trait ClearsResponseCache
{
    /**
     * Boot the trait and register model event listeners.
     *
     * Clears the entire response cache when a model using this trait
     * is created, updated, or deleted. Full cache clear is appropriate
     * for a blog with relatively infrequent content updates.
     */
    public static function bootClearsResponseCache(): void
    {
        static::created(function () {
            ResponseCache::clear();
        });

        static::updated(function () {
            ResponseCache::clear();
        });

        static::deleted(function () {
            ResponseCache::clear();
        });
    }
}
