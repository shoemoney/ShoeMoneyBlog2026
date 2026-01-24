<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use App\Models\WordPress\WpPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Migrating WordPress posts...');

        // Build user ID mapping (WordPress ID -> Laravel ID)
        $userMap = User::whereNotNull('wordpress_id')
            ->pluck('id', 'wordpress_id')
            ->toArray();

        $total = WpPost::published()->posts()->count();
        $bar = $this->command->getOutput()->createProgressBar($total);
        $bar->start();

        WpPost::published()
            ->posts()
            ->orderBy('post_date', 'asc')
            ->chunk(100, function ($wpPosts) use ($userMap, $bar) {
                $postsData = [];

                foreach ($wpPosts as $wpPost) {
                    // Map WordPress author to Laravel user
                    $userId = $userMap[$wpPost->post_author] ?? null;

                    if (!$userId) {
                        $this->command->warn("  Skipping post {$wpPost->ID}: author {$wpPost->post_author} not found");
                        continue;
                    }

                    $postsData[] = [
                        'wordpress_id' => $wpPost->ID,
                        'user_id' => $userId,
                        'title' => $wpPost->post_title,
                        'slug' => $wpPost->post_name,
                        'content' => $wpPost->post_content,
                        'excerpt' => $wpPost->post_excerpt ?: null,
                        'status' => 'published',
                        'published_at' => $wpPost->post_date,
                        'created_at' => $wpPost->post_date,
                        'updated_at' => $wpPost->post_modified,
                    ];
                }

                if (!empty($postsData)) {
                    Post::upsert(
                        $postsData,
                        ['wordpress_id'],
                        ['user_id', 'title', 'slug', 'content', 'excerpt', 'status', 'published_at', 'updated_at']
                    );
                }

                $bar->advance(count($postsData));
            });

        $bar->finish();
        $this->command->newLine();
        $this->command->info("Migrated posts. Laravel count: " . Post::count());
    }
}
