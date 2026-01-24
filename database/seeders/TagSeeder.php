<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\WordPress\WpTermTaxonomy;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Migrating WordPress tags...');

        $total = WpTermTaxonomy::tags()->count();
        $bar = $this->command->getOutput()->createProgressBar($total);
        $bar->start();

        // Chunk for 15K+ tags to avoid memory issues
        WpTermTaxonomy::tags()
            ->with('term')
            ->chunk(500, function ($wpTags) use ($bar) {
                $tagsData = [];

                foreach ($wpTags as $wpTag) {
                    $tagsData[] = [
                        'wordpress_id' => $wpTag->term_taxonomy_id,
                        'name' => $wpTag->term->name,
                        'slug' => $wpTag->term->slug,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // Use upsert for idempotency (safe to re-run)
                Tag::upsert(
                    $tagsData,
                    ['wordpress_id'], // Unique key
                    ['name', 'slug', 'updated_at'] // Update these on conflict
                );

                $bar->advance(count($tagsData));
            });

        $bar->finish();
        $this->command->newLine();
        $this->command->info("Migrated {$total} tags.");
    }
}
