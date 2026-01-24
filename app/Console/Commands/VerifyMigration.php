<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Models\WordPress\WpComment;
use App\Models\WordPress\WpPost;
use App\Models\WordPress\WpTermTaxonomy;
use App\Models\WordPress\WpUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyMigration extends Command
{
    protected $signature = 'migration:verify {--fix : Attempt to fix discrepancies}';
    protected $description = 'Verify WordPress to Laravel migration integrity';

    private array $issues = [];
    private array $passed = [];

    public function handle(): int
    {
        $this->info('');
        $this->info('Verifying WordPress to Laravel migration...');
        $this->info('');

        $this->verifyUsers();
        $this->verifyCategories();
        $this->verifyTags();
        $this->verifyPosts();
        $this->verifyPages();
        $this->verifyComments();
        $this->verifyCommentThreading();
        $this->verifyTaxonomyRelationships();
        $this->verifyUserRoles();

        $this->printResults();

        return empty($this->issues) ? Command::SUCCESS : Command::FAILURE;
    }

    private function verifyUsers(): void
    {
        $wpCount = WpUser::count();
        $laravelCount = User::whereNotNull('wordpress_id')->count();

        if ($wpCount === $laravelCount) {
            $this->passed[] = "Users: {$laravelCount}/{$wpCount} OK";
        } else {
            $this->issues[] = "Users: Expected {$wpCount}, found {$laravelCount}";
        }
    }

    private function verifyCategories(): void
    {
        $wpCount = WpTermTaxonomy::categories()->count();
        $laravelCount = Category::count();

        if ($wpCount === $laravelCount) {
            $this->passed[] = "Categories: {$laravelCount}/{$wpCount} OK";
        } else {
            $this->issues[] = "Categories: Expected {$wpCount}, found {$laravelCount}";
        }
    }

    private function verifyTags(): void
    {
        $wpCount = WpTermTaxonomy::tags()->count();
        $laravelCount = Tag::count();

        if ($wpCount === $laravelCount) {
            $this->passed[] = "Tags: {$laravelCount}/{$wpCount} OK";
        } else {
            $this->issues[] = "Tags: Expected {$wpCount}, found {$laravelCount}";
        }
    }

    private function verifyPosts(): void
    {
        $wpCount = WpPost::published()->posts()->count();
        $laravelCount = Post::count();

        if ($wpCount === $laravelCount) {
            $this->passed[] = "Posts: {$laravelCount}/{$wpCount} OK";
        } else {
            $this->issues[] = "Posts: Expected {$wpCount}, found {$laravelCount}";
        }
    }

    private function verifyPages(): void
    {
        $wpCount = WpPost::published()->pages()->count();
        $laravelCount = Page::count();

        if ($wpCount === $laravelCount) {
            $this->passed[] = "Pages: {$laravelCount}/{$wpCount} OK";
        } else {
            $this->issues[] = "Pages: Expected {$wpCount}, found {$laravelCount}";
        }
    }

    private function verifyComments(): void
    {
        $wpCount = WpComment::approved()->count();
        $laravelCount = Comment::count();

        // Allow small variance for orphaned comments
        $tolerance = ceil($wpCount * 0.02); // 2% tolerance for orphaned comments

        if (abs($wpCount - $laravelCount) <= $tolerance) {
            $this->passed[] = "Comments: {$laravelCount}/{$wpCount} OK (within tolerance)";
        } else {
            $this->issues[] = "Comments: Expected ~{$wpCount}, found {$laravelCount}";
        }
    }

    private function verifyCommentThreading(): void
    {
        // Count WordPress threaded comments
        $wpThreaded = WpComment::approved()->where('comment_parent', '>', 0)->count();

        // Count Laravel threaded comments
        $laravelThreaded = Comment::whereNotNull('parent_id')->count();

        $tolerance = ceil($wpThreaded * 0.02); // 2% tolerance

        if (abs($wpThreaded - $laravelThreaded) <= $tolerance) {
            $this->passed[] = "Comment Threading: {$laravelThreaded}/{$wpThreaded} OK";
        } else {
            $this->issues[] = "Comment Threading: Expected ~{$wpThreaded} threaded, found {$laravelThreaded}";
        }

        // Verify no orphaned parent_ids
        $orphanedReplies = Comment::whereNotNull('parent_id')
            ->whereDoesntHave('parent')
            ->count();

        if ($orphanedReplies === 0) {
            $this->passed[] = "Comment Parents: No orphaned replies";
        } else {
            $this->issues[] = "Comment Parents: {$orphanedReplies} orphaned replies (parent missing)";
        }
    }

    private function verifyTaxonomyRelationships(): void
    {
        // Sample check: verify posts have categories/tags
        $postsWithCategories = DB::table('categorizables')
            ->where('categorizable_type', Post::class)
            ->distinct('categorizable_id')
            ->count('categorizable_id');

        $postsWithTags = DB::table('taggables')
            ->where('taggable_type', Post::class)
            ->distinct('taggable_id')
            ->count('taggable_id');

        $totalPosts = Post::count();

        $this->passed[] = "Posts with categories: {$postsWithCategories}/{$totalPosts}";
        $this->passed[] = "Posts with tags: {$postsWithTags}/{$totalPosts}";

        // Verify relationship works both ways
        $samplePost = Post::has('categories')->first();
        if ($samplePost && $samplePost->categories()->count() > 0) {
            $this->passed[] = "Post->categories relationship: Working";
        } else {
            $this->issues[] = "Post->categories relationship: Not working";
        }

        $sampleCategory = Category::has('posts')->first();
        if ($sampleCategory && $sampleCategory->posts()->count() > 0) {
            $this->passed[] = "Category->posts relationship: Working";
        } else {
            $this->issues[] = "Category->posts relationship: Not working";
        }
    }

    private function verifyUserRoles(): void
    {
        $administrators = User::where('role', User::ROLE_ADMINISTRATOR)->count();
        $editors = User::where('role', User::ROLE_EDITOR)->count();
        $authors = User::where('role', User::ROLE_AUTHOR)->count();

        $total = $administrators + $editors + $authors;
        $expected = User::whereNotNull('wordpress_id')->count();

        if ($total === $expected) {
            $this->passed[] = "User Roles: {$administrators} admins, {$editors} editors, {$authors} authors";
        } else {
            $this->issues[] = "User Roles: {$total} assigned, expected {$expected}";
        }
    }

    private function printResults(): void
    {
        $this->newLine();

        if (!empty($this->passed)) {
            $this->info('PASSED:');
            foreach ($this->passed as $pass) {
                $this->line("  [OK] {$pass}");
            }
        }

        if (!empty($this->issues)) {
            $this->newLine();
            $this->error('ISSUES:');
            foreach ($this->issues as $issue) {
                $this->line("  [!!] {$issue}");
            }
        }

        $this->newLine();
        if (empty($this->issues)) {
            $this->info('Migration verification PASSED');
        } else {
            $this->error('Migration verification FAILED - ' . count($this->issues) . ' issue(s)');
        }
    }
}
