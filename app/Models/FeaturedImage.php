<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FeaturedImage extends Model
{
    protected $fillable = [
        'imageable_id',
        'imageable_type',
        'raw_url',
        'small_url',
        'medium_url',
        'large_url',
        'inline_url',
        'prompt_used',
        'status',
        'attempts',
        'error_message',
        'model_used',
    ];

    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get a URL for the given size, falling back through larger sizes.
     */
    public function getUrl(string $size = 'large'): ?string
    {
        $fallbackOrder = [
            'small' => ['small_url', 'medium_url', 'large_url', 'raw_url'],
            'medium' => ['medium_url', 'large_url', 'raw_url'],
            'large' => ['large_url', 'raw_url'],
            'inline' => ['inline_url', 'medium_url', 'large_url', 'raw_url'],
            'raw' => ['raw_url'],
        ];

        $fields = $fallbackOrder[$size] ?? $fallbackOrder['large'];

        foreach ($fields as $field) {
            if (!empty($this->$field)) {
                return $this->$field;
            }
        }

        return null;
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
