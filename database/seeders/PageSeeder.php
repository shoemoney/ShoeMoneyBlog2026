<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\User;
use App\Models\WordPress\WpPost;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Migrating WordPress pages...');

        // Build user ID mapping
        $userMap = User::whereNotNull('wordpress_id')
            ->pluck('id', 'wordpress_id')
            ->toArray();

        // Default fallback user (first admin)
        $defaultUserId = User::where('role', 'administrator')->first()?->id
            ?? User::first()?->id;

        $wpPages = WpPost::published()->pages()->get();
        $seenSlugs = [];
        $migratedCount = 0;

        foreach ($wpPages as $wpPage) {
            $userId = $userMap[$wpPage->post_author] ?? null;

            if (!$userId) {
                // Use default user if author not found (deleted WP user)
                $userId = $defaultUserId;
                $this->command->warn("  Page {$wpPage->ID}: author {$wpPage->post_author} not found, using default user");
            }

            // Handle duplicate slugs by appending wordpress_id
            $slug = $wpPage->post_name;
            if (isset($seenSlugs[$slug]) || Page::where('slug', $slug)->whereNot('wordpress_id', $wpPage->ID)->exists()) {
                $slug = $slug . '-' . $wpPage->ID;
                $this->command->warn("  Page {$wpPage->ID}: duplicate slug, using '{$slug}'");
            }
            $seenSlugs[$slug] = true;

            Page::updateOrCreate(
                ['wordpress_id' => $wpPage->ID],
                [
                    'user_id' => $userId,
                    'title' => $wpPage->post_title,
                    'slug' => $slug,
                    'content' => $wpPage->post_content,
                    'menu_order' => $wpPage->menu_order ?? 0,
                    'created_at' => $wpPage->post_date,
                    'updated_at' => $wpPage->post_modified,
                ]
            );
            $migratedCount++;
        }

        $this->command->info("Migrated {$migratedCount} pages.");
    }
}
