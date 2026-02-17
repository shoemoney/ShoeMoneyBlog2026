<?php

namespace Tests\Feature;

use App\Jobs\GenerateFeaturedImageJob;
use App\Models\FeaturedImage;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use App\Services\ImageResizeService;
use App\Services\OpenRouterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FeaturedImageTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test Author',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->post = Post::create([
            'wordpress_id' => 99999,
            'user_id' => $this->user->id,
            'title' => 'Test Post About Making Money Online',
            'slug' => 'test-post-making-money',
            'content' => '<p>This is a test blog post about making money online with affiliate marketing.</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Seed AI settings
        $this->seedAiSettings();
    }

    private function seedAiSettings(): void
    {
        $settings = [
            ['key' => 'ai_openrouter_api_key', 'value' => 'sk-test-fake-key', 'type' => 'string', 'group' => 'ai'],
            ['key' => 'ai_general_model', 'value' => 'google/gemini-3-flash-preview', 'type' => 'string', 'group' => 'ai'],
            ['key' => 'ai_image_model', 'value' => 'google/gemini-3-pro-image-preview', 'type' => 'string', 'group' => 'ai'],
            ['key' => 'ai_api_url', 'value' => 'https://openrouter.ai/api/v1/chat/completions', 'type' => 'string', 'group' => 'ai'],
            ['key' => 'ai_s3_path', 'value' => 'blog_image', 'type' => 'string', 'group' => 'ai'],
        ];

        foreach ($settings as $s) {
            Setting::create($s);
        }
    }

    // ── Model & Relationship Tests ──────────────────────────────

    public function test_post_has_featured_image_relationship(): void
    {
        $this->assertNull($this->post->featuredImage);

        $fi = FeaturedImage::create([
            'imageable_id' => $this->post->id,
            'imageable_type' => Post::class,
            'status' => 'pending',
        ]);

        $this->post->refresh();
        $this->assertNotNull($this->post->featuredImage);
        $this->assertEquals($fi->id, $this->post->featuredImage->id);
    }

    public function test_featured_image_belongs_to_post(): void
    {
        $fi = FeaturedImage::create([
            'imageable_id' => $this->post->id,
            'imageable_type' => Post::class,
            'status' => 'completed',
            'raw_url' => 'https://cdn.example.com/raw/test.png',
        ]);

        $this->assertInstanceOf(Post::class, $fi->imageable);
        $this->assertEquals($this->post->id, $fi->imageable->id);
    }

    public function test_featured_image_url_accessor_returns_large_url(): void
    {
        FeaturedImage::create([
            'imageable_id' => $this->post->id,
            'imageable_type' => Post::class,
            'status' => 'completed',
            'large_url' => 'https://cdn.example.com/large/test.png',
            'raw_url' => 'https://cdn.example.com/raw/test.png',
        ]);

        $this->post->refresh();
        $this->assertEquals('https://cdn.example.com/large/test.png', $this->post->featured_image_url);
    }

    public function test_featured_image_url_accessor_returns_null_without_image(): void
    {
        $this->assertNull($this->post->featured_image_url);
    }

    // ── FeaturedImage::getUrl Fallback Tests ────────────────────

    public function test_get_url_falls_back_through_sizes(): void
    {
        $fi = FeaturedImage::create([
            'imageable_id' => $this->post->id,
            'imageable_type' => Post::class,
            'status' => 'completed',
            'raw_url' => 'https://cdn.example.com/raw/test.png',
        ]);

        // With only raw_url, all sizes should fall back to raw
        $this->assertEquals('https://cdn.example.com/raw/test.png', $fi->getUrl('small'));
        $this->assertEquals('https://cdn.example.com/raw/test.png', $fi->getUrl('medium'));
        $this->assertEquals('https://cdn.example.com/raw/test.png', $fi->getUrl('large'));
        $this->assertEquals('https://cdn.example.com/raw/test.png', $fi->getUrl('inline'));
    }

    public function test_get_url_prefers_requested_size(): void
    {
        $fi = FeaturedImage::create([
            'imageable_id' => $this->post->id,
            'imageable_type' => Post::class,
            'status' => 'completed',
            'raw_url' => 'https://cdn.example.com/raw/test.png',
            'small_url' => 'https://cdn.example.com/small/test.png',
            'medium_url' => 'https://cdn.example.com/medium/test.png',
            'large_url' => 'https://cdn.example.com/large/test.png',
            'inline_url' => 'https://cdn.example.com/inline/test.png',
        ]);

        $this->assertEquals('https://cdn.example.com/small/test.png', $fi->getUrl('small'));
        $this->assertEquals('https://cdn.example.com/medium/test.png', $fi->getUrl('medium'));
        $this->assertEquals('https://cdn.example.com/large/test.png', $fi->getUrl('large'));
        $this->assertEquals('https://cdn.example.com/inline/test.png', $fi->getUrl('inline'));
        $this->assertEquals('https://cdn.example.com/raw/test.png', $fi->getUrl('raw'));
    }

    // ── Status Helper Tests ─────────────────────────────────────

    public function test_status_helpers(): void
    {
        $fi = FeaturedImage::create([
            'imageable_id' => $this->post->id,
            'imageable_type' => Post::class,
            'status' => 'completed',
        ]);

        $this->assertTrue($fi->isCompleted());
        $this->assertFalse($fi->isFailed());
        $this->assertFalse($fi->isPending());

        $fi->update(['status' => 'failed']);
        $this->assertTrue($fi->isFailed());

        $fi->update(['status' => 'pending']);
        $this->assertTrue($fi->isPending());
    }

    // ── Settings Integration Tests ──────────────────────────────

    public function test_openrouter_service_reads_from_settings_table(): void
    {
        $service = app(OpenRouterService::class);

        $ref = new \ReflectionClass($service);

        $apiUrl = $ref->getProperty('apiUrl');
        $this->assertEquals('https://openrouter.ai/api/v1/chat/completions', $apiUrl->getValue($service));

        $generalModel = $ref->getProperty('generalModel');
        $this->assertEquals('google/gemini-3-flash-preview', $generalModel->getValue($service));

        $imageModel = $ref->getProperty('imageModel');
        $this->assertEquals('google/gemini-3-pro-image-preview', $imageModel->getValue($service));

        $apiKey = $ref->getProperty('apiKey');
        $this->assertEquals('sk-test-fake-key', $apiKey->getValue($service));
    }

    public function test_changing_settings_updates_service(): void
    {
        Setting::setValue('ai_general_model', 'google/gemini-2.0-flash');

        $service = app(OpenRouterService::class);
        $ref = new \ReflectionClass($service);
        $prop = $ref->getProperty('generalModel');

        $this->assertEquals('google/gemini-2.0-flash', $prop->getValue($service));
    }

    // ── Job Tests (Mocked API) ──────────────────────────────────

    public function test_job_generates_image_and_uploads_to_s3(): void
    {
        Storage::fake('s3');
        Http::fake(['*' => Http::response('', 200)]);

        // Create a 1x1 red PNG for testing
        $testImage = $this->createTestPng();

        $mockRouter = $this->mock(OpenRouterService::class);
        $mockRouter->shouldReceive('generateImagePrompt')
            ->once()
            ->andReturn('A detailed prompt about making money online');
        $mockRouter->shouldReceive('generateImage')
            ->once()
            ->andReturn($testImage);

        $fi = FeaturedImage::create([
            'imageable_id' => $this->post->id,
            'imageable_type' => Post::class,
            'status' => 'pending',
        ]);

        $job = new GenerateFeaturedImageJob($fi->id);
        $job->handle($mockRouter, app(ImageResizeService::class));

        $fi->refresh();

        $this->assertEquals('completed', $fi->status);
        $this->assertNotNull($fi->raw_url);
        $this->assertNotNull($fi->small_url);
        $this->assertNotNull($fi->medium_url);
        $this->assertNotNull($fi->large_url);
        $this->assertNotNull($fi->inline_url);
        $this->assertEquals('A detailed prompt about making money online', $fi->prompt_used);

        // Verify files were uploaded to S3
        Storage::disk('s3')->assertExists('blog_image/featured_images/raw/test-post-making-money.png');
        Storage::disk('s3')->assertExists('blog_image/featured_images/small/test-post-making-money.png');
        Storage::disk('s3')->assertExists('blog_image/featured_images/medium/test-post-making-money.png');
        Storage::disk('s3')->assertExists('blog_image/featured_images/large/test-post-making-money.png');
        Storage::disk('s3')->assertExists('blog_image/featured_images/inline/test-post-making-money.png');

        // Verify the HTTP verification was called
        Http::assertSent(fn ($request) => $request->method() === 'HEAD');
    }

    public function test_job_fails_when_upload_not_reachable(): void
    {
        Storage::fake('s3');
        Http::fake(['*' => Http::response('', 403)]);

        $testImage = $this->createTestPng();

        $mockRouter = $this->mock(OpenRouterService::class);
        $mockRouter->shouldReceive('generateImagePrompt')->once()->andReturn('Test prompt');
        $mockRouter->shouldReceive('generateImage')->once()->andReturn($testImage);

        $fi = FeaturedImage::create([
            'imageable_id' => $this->post->id,
            'imageable_type' => Post::class,
            'status' => 'pending',
        ]);

        try {
            $job = new GenerateFeaturedImageJob($fi->id);
            $job->handle($mockRouter, app(ImageResizeService::class));
        } catch (\RuntimeException $e) {
            // Expected
        }

        $fi->refresh();
        $this->assertNotEquals('completed', $fi->status);
        $this->assertStringContains('Upload verification failed', $fi->error_message);
    }

    public function test_job_marks_failed_on_api_error(): void
    {
        Storage::fake('s3');

        $mockRouter = $this->mock(OpenRouterService::class);
        $mockRouter->shouldReceive('generateImagePrompt')
            ->once()
            ->andThrow(new \RuntimeException('HTTP 401 - User not found'));

        $fi = FeaturedImage::create([
            'imageable_id' => $this->post->id,
            'imageable_type' => Post::class,
            'status' => 'pending',
        ]);

        try {
            $job = new GenerateFeaturedImageJob($fi->id);
            $job->handle($mockRouter, app(ImageResizeService::class));
        } catch (\RuntimeException $e) {
            // Expected — job rethrows for queue retry
        }

        $fi->refresh();

        $this->assertEquals(1, $fi->attempts);
        $this->assertStringContains('401', $fi->error_message);
    }

    public function test_job_skips_already_completed(): void
    {
        $mockRouter = $this->mock(OpenRouterService::class);
        $mockRouter->shouldNotReceive('generateImagePrompt');

        $fi = FeaturedImage::create([
            'imageable_id' => $this->post->id,
            'imageable_type' => Post::class,
            'status' => 'completed',
            'raw_url' => 'https://cdn.example.com/raw/test.png',
        ]);

        $job = new GenerateFeaturedImageJob($fi->id);
        $job->handle($mockRouter, app(ImageResizeService::class));

        // Should not have been touched
        $fi->refresh();
        $this->assertEquals('completed', $fi->status);
    }

    public function test_job_marks_failed_after_max_attempts(): void
    {
        Storage::fake('s3');

        $mockRouter = $this->mock(OpenRouterService::class);
        $mockRouter->shouldReceive('generateImagePrompt')
            ->once()
            ->andThrow(new \RuntimeException('API timeout'));

        $fi = FeaturedImage::create([
            'imageable_id' => $this->post->id,
            'imageable_type' => Post::class,
            'status' => 'pending',
            'attempts' => 2, // Already tried twice
        ]);

        try {
            $job = new GenerateFeaturedImageJob($fi->id);
            $job->handle($mockRouter, app(ImageResizeService::class));
        } catch (\RuntimeException $e) {
            // Expected
        }

        $fi->refresh();
        $this->assertEquals('failed', $fi->status); // 3 attempts = max
        $this->assertEquals(3, $fi->attempts);
    }

    // ── Artisan Command Tests ───────────────────────────────────

    public function test_command_dry_run_shows_posts(): void
    {
        $this->artisan('images:generate', ['--dry-run' => true, '--limit' => 1, '--type' => 'posts'])
            ->expectsOutputToContain('Test Post About Making Money')
            ->expectsOutputToContain('Dry run')
            ->assertSuccessful();

        // No FeaturedImage records should be created
        $this->assertEquals(0, FeaturedImage::count());
    }

    public function test_command_skips_posts_with_completed_images(): void
    {
        FeaturedImage::create([
            'imageable_id' => $this->post->id,
            'imageable_type' => Post::class,
            'status' => 'completed',
            'raw_url' => 'https://cdn.example.com/raw/test.png',
        ]);

        $this->artisan('images:generate', ['--limit' => 1, '--type' => 'posts', '--dry-run' => true])
            ->expectsOutputToContain('No items found')
            ->assertSuccessful();
    }

    public function test_command_force_regenerates_completed(): void
    {
        Storage::fake('s3');
        Http::fake(['*' => Http::response('', 200)]);

        $testImage = $this->createTestPng();

        $mockRouter = $this->mock(OpenRouterService::class);
        $mockRouter->shouldReceive('generateImagePrompt')->once()->andReturn('Test prompt');
        $mockRouter->shouldReceive('generateImage')->once()->andReturn($testImage);

        FeaturedImage::create([
            'imageable_id' => $this->post->id,
            'imageable_type' => Post::class,
            'status' => 'completed',
            'raw_url' => 'https://cdn.example.com/raw/old.png',
        ]);

        $this->artisan('images:generate', ['--limit' => 1, '--type' => 'posts', '--force' => true, '--sync' => true])
            ->expectsOutputToContain('1 items to process')
            ->assertSuccessful();
    }

    // ── Unique Constraint Test ──────────────────────────────────

    public function test_unique_constraint_prevents_duplicate_images(): void
    {
        FeaturedImage::create([
            'imageable_id' => $this->post->id,
            'imageable_type' => Post::class,
            'status' => 'pending',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        FeaturedImage::create([
            'imageable_id' => $this->post->id,
            'imageable_type' => Post::class,
            'status' => 'pending',
        ]);
    }

    // ── Helper ──────────────────────────────────────────────────

    private function createTestPng(): string
    {
        $img = imagecreatetruecolor(200, 112); // 16:9 ratio
        $red = imagecolorallocate($img, 255, 0, 0);
        imagefill($img, 0, 0, $red);
        ob_start();
        imagepng($img);
        $data = ob_get_clean();
        imagedestroy($img);

        return $data;
    }

    private function assertStringContains(string $needle, ?string $haystack): void
    {
        $this->assertNotNull($haystack);
        $this->assertTrue(str_contains($haystack, $needle), "Failed asserting that '$haystack' contains '$needle'");
    }
}
