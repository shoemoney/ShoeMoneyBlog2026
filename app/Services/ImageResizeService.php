<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageResizeService
{
    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Create size variants from raw image data and upload to S3.
     *
     * @param string $imageData Raw binary image data
     * @param string $basePath S3 base path (e.g., "blog_image/featured_images")
     * @param string $filename Base filename without extension (e.g., "my-post-slug")
     * @return array<string, string> Map of size => URL
     */
    public function createVariants(string $imageData, string $basePath, string $filename): array
    {
        $sizes = config('services.featured_images.sizes');
        $urls = [];
        $cdnBase = config('filesystems.disks.s3.url');

        foreach ($sizes as $name => $width) {
            $image = $this->manager->read($imageData);

            // Scale down to target width, maintaining aspect ratio
            $image->scaleDown(width: $width);

            $encoded = $image->toPng();
            $path = "{$basePath}/{$name}/{$filename}.png";

            Storage::disk('s3')->put($path, (string) $encoded);

            $urls["{$name}_url"] = "{$cdnBase}/{$path}";
        }

        return $urls;
    }

    /**
     * Ensure image is 16:9 landscape. If portrait or wrong aspect, crop to 16:9 from center.
     */
    public function enforceLandscape(string $imageData): string
    {
        $image = $this->manager->read($imageData);
        $w = $image->width();
        $h = $image->height();
        $ratio = $w / $h;

        // Already close to 16:9 (1.77) — allow some tolerance
        if ($ratio >= 1.5 && $ratio <= 2.0) {
            return $imageData;
        }

        // Crop to 16:9 from center
        $targetRatio = 16 / 9;

        if ($ratio < $targetRatio) {
            // Too tall — keep width, crop height
            $newHeight = (int) ($w / $targetRatio);
            $image->crop($w, $newHeight, 0, (int) (($h - $newHeight) / 2));
        } else {
            // Too wide — keep height, crop width
            $newWidth = (int) ($h * $targetRatio);
            $image->crop($newWidth, $h, (int) (($w - $newWidth) / 2), 0);
        }

        return (string) $image->toPng();
    }

    /**
     * Upload raw image to S3 and return the URL.
     */
    public function uploadRaw(string $imageData, string $basePath, string $filename): string
    {
        $path = "{$basePath}/raw/{$filename}.png";
        $cdnBase = config('filesystems.disks.s3.url');

        Storage::disk('s3')->put($path, $imageData);

        return "{$cdnBase}/{$path}";
    }
}
