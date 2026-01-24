<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\WordPress\WpTermTaxonomy;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Migrating WordPress categories...');

        $wpCategories = WpTermTaxonomy::categories()
            ->with('term')
            ->get();

        foreach ($wpCategories as $wpCat) {
            Category::updateOrCreate(
                ['wordpress_id' => $wpCat->term_taxonomy_id],
                [
                    'name' => $wpCat->term->name,
                    'slug' => $wpCat->term->slug,
                    'description' => $wpCat->description ?: null,
                ]
            );
        }

        $this->command->info("Migrated {$wpCategories->count()} categories.");
    }
}
