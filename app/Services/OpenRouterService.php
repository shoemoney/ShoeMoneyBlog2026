<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OpenRouterService
{
    private string $apiKey;
    private string $apiUrl;
    private string $generalModel;
    private string $imageModel;

    public function __construct()
    {
        $this->apiKey = Setting::getValue('ai_openrouter_api_key');
        $this->apiUrl = Setting::getValue('ai_api_url', 'https://openrouter.ai/api/v1/chat/completions');
        $this->generalModel = Setting::getValue('ai_general_model', 'google/gemini-3-flash-preview');
        $this->imageModel = Setting::getValue('ai_image_model', 'google/gemini-3-pro-image-preview');
    }

    /**
     * Generate a detailed image prompt from post content using Gemini Flash.
     */
    public function generateImagePrompt(string $title, string $content, string $slug): string
    {
        $contentSnippet = strip_tags(mb_substr($content, 0, 3000));

        $systemPrompt = <<<'PROMPT'
You are an expert at writing image generation prompts. Given a blog post title and content, write a detailed, vivid image generation prompt that would make a great featured image for this blog post.

Guidelines:
- CRITICAL: The image MUST be in LANDSCAPE orientation (wider than tall), 16:9 aspect ratio. Start every prompt with "Wide landscape 16:9 image:"
- The image should be eye-catching and suitable as a blog post thumbnail/hero image
- EVERY image MUST feature a male character (30s, casual style) as the main subject. Reference photos of this person will be provided during generation — the model MUST reproduce his exact likeness.
- Describe the male character in a dynamic pose: either naturally part of the scene, doing an exaggerated "soy face / excited YouTube thumbnail" reaction, or fully immersed as the main character in the scenario
- Always describe the character's position, pose, and interaction with the scene in detail so the reference photo person can be placed convincingly
- Keep the style modern, clean, and professional
- Include specific details about composition, lighting, colors, and mood
- Output ONLY the image prompt text, nothing else - no quotes, no labels, no explanation
PROMPT;

        $response = Http::withHeaders($this->headers())->timeout(60)->post($this->apiUrl, [
            'model' => $this->generalModel,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => "Title: {$title}\n\nContent:\n{$contentSnippet}"],
            ],
        ]);

        if (!$response->successful()) {
            throw new RuntimeException("Prompt generation failed: HTTP {$response->status()} - {$response->body()}");
        }

        $data = $response->json();
        $prompt = $data['choices'][0]['message']['content'] ?? null;

        if (empty($prompt)) {
            throw new RuntimeException('Prompt generation returned empty content');
        }

        return trim($prompt);
    }

    /**
     * Generate an image using Gemini Image Pro with optional reference photos.
     *
     * @param string $prompt The image generation prompt
     * @param array $referenceImagePaths Paths to reference images (jeremy*.png)
     * @return string Raw image data (binary)
     */
    public function generateImage(string $prompt, array $referenceImagePaths = []): string
    {
        $contentParts = [];

        // Add reference images as base64 multimodal content
        foreach ($referenceImagePaths as $path) {
            if (!file_exists($path)) {
                continue;
            }
            $base64 = base64_encode(file_get_contents($path));
            $contentParts[] = [
                'type' => 'image_url',
                'image_url' => [
                    'url' => 'data:image/png;base64,' . $base64,
                ],
            ];
        }

        // Build the full prompt — face instructions FIRST, then scene prompt
        if (!empty($referenceImagePaths)) {
            $contentParts[] = [
                'type' => 'text',
                'text' => <<<'FACE'
!!! MOST IMPORTANT INSTRUCTION — DO NOT SKIP — READ THIS FIRST !!!

The reference photo(s) above show a REAL PERSON. The generated image MUST feature THIS EXACT PERSON. This is the #1 priority of this entire request. If the final image does not clearly show the person from the reference photo, the output is a FAILURE.

Study the reference photo(s) VERY carefully. Memorize every detail: face shape, jawline, nose, eyes, eyebrows, skin tone, hair color, hair style, facial hair, build, and overall appearance.

Now pick ONE of these three styles RANDOMLY and execute it:

1. FACE SWAP — Generate the scene below, but the main character's face MUST be an EXACT match of the reference photo person. Same face shape, same features, same skin tone, same hair. NOT a similar-looking person. THE SAME PERSON.

2. SOY FACE OVERLAY — Put the reference photo person in the foreground doing an exaggerated excited "soy face" YouTube thumbnail reaction (mouth wide open, hands on cheeks or pointing). They must be reacting to the blog post topic behind them. The person MUST match the reference photo EXACTLY.

3. TOTAL CHARACTER SWAP — The reference photo person IS the main character. Put them in a dynamic, random pose relevant to the blog topic. Recreate their COMPLETE appearance faithfully — face, body type, hair, skin tone — in the new scene.

!!! FINAL CHECK: Before outputting the image, verify the person in it matches the reference photo. Same person. Not a different person. Not a generic person. THE EXACT PERSON FROM THE REFERENCE PHOTO. !!!

Now here is the scene to generate:
FACE,
            ];
        }

        $contentParts[] = [
            'type' => 'text',
            'text' => $prompt,
        ];

        $response = Http::withHeaders($this->headers())->timeout(120)->post($this->apiUrl, [
            'model' => $this->imageModel,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $contentParts,
                ],
            ],
            'provider' => [
                'sort' => 'throughput',
            ],
            'image_size' => '1024x576',
        ]);

        if (!$response->successful()) {
            throw new RuntimeException("Image generation failed: HTTP {$response->status()} - {$response->body()}");
        }

        $data = $response->json();

        return $this->extractImageData($data);
    }

    private function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'HTTP-Referer' => 'https://shoemoney.com',
            'X-Title' => 'ShoeMoney Blog',
        ];
    }

    /**
     * Extract base64 image data from the OpenRouter/Gemini response.
     * Handles both dict and string image formats.
     */
    private function extractImageData(array $data): string
    {
        $message = $data['choices'][0]['message'] ?? null;

        if (!$message) {
            throw new RuntimeException('No message in API response');
        }

        // Check for images array (Gemini format via OpenRouter)
        if (!empty($message['images'])) {
            $imgObj = $message['images'][0];
            $imgData = '';

            if (is_array($imgObj)) {
                // Dict format: {"image_url": {"url": "data:image/png;base64,..."}}
                $imgData = $imgObj['image_url']['url'] ?? '';
            } else {
                // String format (raw base64 or data URI)
                $imgData = (string) $imgObj;
            }

            // Strip data URI prefix if present
            if (str_contains($imgData, ',')) {
                $imgData = substr($imgData, strpos($imgData, ',') + 1);
            }

            if (!empty($imgData)) {
                $decoded = base64_decode($imgData, true);
                if ($decoded === false) {
                    throw new RuntimeException('Failed to decode base64 image data');
                }
                return $decoded;
            }
        }

        // Check content parts for inline image data
        if (!empty($message['content']) && is_array($message['content'])) {
            foreach ($message['content'] as $part) {
                if (isset($part['type']) && $part['type'] === 'image_url') {
                    $url = $part['image_url']['url'] ?? '';
                    if (str_contains($url, ',')) {
                        $url = substr($url, strpos($url, ',') + 1);
                    }
                    $decoded = base64_decode($url, true);
                    if ($decoded !== false) {
                        return $decoded;
                    }
                }
            }
        }

        Log::error('Image generation: unexpected response format', ['response' => $data]);
        throw new RuntimeException('No image data found in API response');
    }
}
