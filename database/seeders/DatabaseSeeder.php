<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Order matters - dependencies must be seeded first:
     * 1. Users (authors for posts)
     * 2. Categories and Tags (taxonomy for posts)
     * 3. Posts and Pages (content)
     * 4. Taxonomy Relationships (links posts to categories/tags)
     * 5. Comments (references posts, users, and other comments)
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('╔═══════════════════════════════════════╗');
        $this->command->info('║   WordPress to Laravel Migration      ║');
        $this->command->info('╚═══════════════════════════════════════╝');
        $this->command->newLine();

        // Phase 1: Foundation (no dependencies)
        $this->command->info('Phase 1: Users & Taxonomies');
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            TagSeeder::class,
        ]);

        // Phase 2: Content (depends on users and taxonomies)
        $this->command->newLine();
        $this->command->info('Phase 2: Content');
        $this->call([
            PostSeeder::class,
            PageSeeder::class,
            TaxonomyRelationshipSeeder::class,
        ]);

        // Phase 3: Engagement (depends on posts and users)
        $this->command->newLine();
        $this->command->info('Phase 3: Comments');
        $this->call([
            CommentSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('╔═══════════════════════════════════════╗');
        $this->command->info('║   Migration Complete!                 ║');
        $this->command->info('╚═══════════════════════════════════════╝');
        $this->command->newLine();

        // Print final statistics
        $this->printStatistics();
    }

    private function printStatistics(): void
    {
        $this->command->info('Final Statistics:');
        $this->command->table(
            ['Entity', 'Count'],
            [
                ['Users', \App\Models\User::count()],
                ['Categories', \App\Models\Category::count()],
                ['Tags', \App\Models\Tag::count()],
                ['Posts', \App\Models\Post::count()],
                ['Pages', \App\Models\Page::count()],
                ['Comments', \App\Models\Comment::count()],
                ['Comment Threads', \App\Models\Comment::whereNotNull('parent_id')->count()],
            ]
        );
    }
}
