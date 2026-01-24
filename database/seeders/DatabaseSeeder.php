<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Order matters: Users and taxonomies must exist before posts.
     * Posts must exist before comments and taxonomy relationships.
     */
    public function run(): void
    {
        $this->command->info('=== WordPress Migration Seeders ===');
        $this->command->newLine();

        // Phase 1: Foundation (no dependencies)
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            TagSeeder::class,
        ]);

        // Phase 2: Content (depends on users and taxonomies)
        // PostSeeder, PageSeeder, TaxonomyRelationshipSeeder
        // Added in Plan 05

        // Phase 3: Engagement (depends on posts)
        // CommentSeeder
        // Added in Plan 06

        $this->command->newLine();
        $this->command->info('=== Migration Complete ===');
    }
}
