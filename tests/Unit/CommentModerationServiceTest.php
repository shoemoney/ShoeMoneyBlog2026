<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\Post;
use App\Services\CommentModerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentModerationServiceTest extends TestCase
{
    use RefreshDatabase;

    private CommentModerationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CommentModerationService();
    }

    public function test_first_time_commenter_returns_pending(): void
    {
        $status = $this->service->determineStatus('newuser@example.com');

        $this->assertEquals('pending', $status);
    }

    public function test_previously_approved_commenter_returns_approved(): void
    {
        // Create a post for the comment
        $post = Post::factory()->create();

        // Create an approved comment from this email
        Comment::factory()->create([
            'post_id' => $post->id,
            'author_email' => 'returning@example.com',
            'status' => 'approved',
        ]);

        $status = $this->service->determineStatus('returning@example.com');

        $this->assertEquals('approved', $status);
    }

    public function test_pending_comment_does_not_grant_approval(): void
    {
        $post = Post::factory()->create();

        // Create a pending comment (not approved)
        Comment::factory()->create([
            'post_id' => $post->id,
            'author_email' => 'pending@example.com',
            'status' => 'pending',
        ]);

        $status = $this->service->determineStatus('pending@example.com');

        $this->assertEquals('pending', $status);
    }

    public function test_email_comparison_is_case_insensitive(): void
    {
        $post = Post::factory()->create();

        Comment::factory()->create([
            'post_id' => $post->id,
            'author_email' => 'user@example.com',
            'status' => 'approved',
        ]);

        // Query with uppercase email
        $status = $this->service->determineStatus('USER@EXAMPLE.COM');

        $this->assertEquals('approved', $status);
    }

    public function test_email_is_trimmed(): void
    {
        $post = Post::factory()->create();

        Comment::factory()->create([
            'post_id' => $post->id,
            'author_email' => 'trim@example.com',
            'status' => 'approved',
        ]);

        // Query with whitespace
        $status = $this->service->determineStatus('  trim@example.com  ');

        $this->assertEquals('approved', $status);
    }
}
