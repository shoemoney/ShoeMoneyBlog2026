<?php

namespace App\Models\Concerns;

use App\Models\FeaturedImage;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasFeaturedImage
{
    public function featuredImage(): MorphOne
    {
        return $this->morphOne(FeaturedImage::class, 'imageable');
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        return $this->featuredImage?->getUrl('large');
    }
}
