<?php

namespace App\Jobs;

use App\Models\FeaturedImage;
use App\Models\Setting;
use App\Services\ImageResizeService;
use App\Services\OpenRouterService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerateFeaturedImageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 300;
    public array $backoff = [30, 120, 300];

    public function __construct(
        public int $featuredImageId,
    ) {}

    public function handle(OpenRouterService $openRouter, ImageResizeService $resizer): void
    {
        $featuredImage = FeaturedImage::find($this->featuredImageId);

        if (!$featuredImage || $featuredImage->isCompleted()) {
            return;
        }

        $imageable = $featuredImage->imageable;
        if (!$imageable) {
            $featuredImage->update(['status' => 'failed', 'error_message' => 'Imageable model not found']);
            return;
        }

        $featuredImage->update([
            'status' => 'generating',
            'attempts' => $featuredImage->attempts + 1,
        ]);

        try {
            // Step 1: Generate the image prompt from post content
            $title = $imageable->title;
            $content = $imageable->content ?? '';
            $slug = $imageable->slug;

            $prompt = $openRouter->generateImagePrompt($title, $content, $slug);

            $featuredImage->update(['prompt_used' => $prompt]);

            // Step 2: Pick 1-2 random reference images
            $refPaths = $this->getRandomRefImages();

            // Step 3: Generate the image
            $imageData = $openRouter->generateImage($prompt, $refPaths);

            // Step 3b: Enforce 16:9 landscape - crop if model returned wrong aspect
            $imageData = $resizer->enforceLandscape($imageData);

            // Step 4: Upload raw to S3
            $basePath = Setting::getValue('ai_s3_path', 'blog_image') . '/featured_images';
            $rawUrl = $resizer->uploadRaw($imageData, $basePath, $slug);

            $featuredImage->update(['raw_url' => $rawUrl]);

            // Step 5: Create size variants
            $variantUrls = $resizer->createVariants($imageData, $basePath, $slug);

            // Step 6: Verify raw image is publicly accessible
            $this->verifyUpload($rawUrl, $slug);

            // Step 7: Update all URLs and mark completed
            $featuredImage->update(array_merge($variantUrls, [
                'status' => 'completed',
                'model_used' => Setting::getValue('ai_image_model', 'google/gemini-3-pro-image-preview'),
                'error_message' => null,
            ]));

            Log::info("Featured image generated for {$featuredImage->imageable_type}#{$imageable->id}: {$slug}");
        } catch (\Throwable $e) {
            Log::error("Featured image generation failed for #{$this->featuredImageId}: {$e->getMessage()}");

            $featuredImage->update([
                'status' => $featuredImage->attempts >= $this->tries ? 'failed' : 'pending',
                'error_message' => mb_substr($e->getMessage(), 0, 1000),
            ]);

            throw $e; // Let the queue retry
        }
    }

    /**
     * Verify the uploaded image is publicly reachable via HTTP HEAD request.
     */
    private function verifyUpload(string $url, string $slug): void
    {
        $response = Http::timeout(10)->head($url);

        if (!$response->successful()) {
            throw new \RuntimeException(
                "Upload verification failed for {$slug}: HTTP {$response->status()} at {$url}"
            );
        }
    }

    /**
     * Pick 1-2 random jeremy*.png reference images.
     */
    private function getRandomRefImages(): array
    {
        $refDir = config('services.featured_images.ref_images_path');

        if (!is_dir($refDir)) {
            return [];
        }

        $files = glob($refDir . '/jeremy*.png');

        if (empty($files)) {
            return [];
        }

        shuffle($files);

        // Use 1-2 reference images to keep payload reasonable
        return array_slice($files, 0, rand(1, min(2, count($files))));
    }
}
