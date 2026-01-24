<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\WordPress\WpPost;
use App\Models\WordPress\WpTermTaxonomy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxonomyRelationshipSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Migrating taxonomy relationships...');

        // Build lookup maps
        $postMap = Post::pluck('id', 'wordpress_id')->toArray();
        $categoryMap = Category::pluck('id', 'wordpress_id')->toArray();
        $tagMap = Tag::pluck('id', 'wordpress_id')->toArray();

        // Clear existing relationships (for idempotency)
        DB::table('categorizables')->where('categorizable_type', Post::class)->delete();
        DB::table('taggables')->where('taggable_type', Post::class)->delete();

        $total = WpPost::published()->posts()->count();
        $bar = $this->command->getOutput()->createProgressBar($total);
        $bar->start();

        $categorizablesData = [];
        $taggablesData = [];

        WpPost::published()
            ->posts()
            ->with(['termTaxonomies.term'])
            ->chunk(100, function ($wpPosts) use ($postMap, $categoryMap, $tagMap, &$categorizablesData, &$taggablesData, $bar) {
                foreach ($wpPosts as $wpPost) {
                    $laravelPostId = $postMap[$wpPost->ID] ?? null;
                    if (!$laravelPostId) continue;

                    foreach ($wpPost->termTaxonomies as $termTax) {
                        if ($termTax->taxonomy === 'category') {
                            $categoryId = $categoryMap[$termTax->term_taxonomy_id] ?? null;
                            if ($categoryId) {
                                $categorizablesData[] = [
                                    'category_id' => $categoryId,
                                    'categorizable_id' => $laravelPostId,
                                    'categorizable_type' => Post::class,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        } elseif ($termTax->taxonomy === 'post_tag') {
                            $tagId = $tagMap[$termTax->term_taxonomy_id] ?? null;
                            if ($tagId) {
                                $taggablesData[] = [
                                    'tag_id' => $tagId,
                                    'taggable_id' => $laravelPostId,
                                    'taggable_type' => Post::class,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        }
                    }

                    // Batch insert every 1000 relationships
                    if (count($categorizablesData) >= 1000) {
                        DB::table('categorizables')->insert($categorizablesData);
                        $categorizablesData = [];
                    }
                    if (count($taggablesData) >= 1000) {
                        DB::table('taggables')->insert($taggablesData);
                        $taggablesData = [];
                    }

                    $bar->advance();
                }
            });

        // Insert remaining records
        if (!empty($categorizablesData)) {
            DB::table('categorizables')->insert($categorizablesData);
        }
        if (!empty($taggablesData)) {
            DB::table('taggables')->insert($taggablesData);
        }

        $bar->finish();
        $this->command->newLine();

        $catCount = DB::table('categorizables')->where('categorizable_type', Post::class)->count();
        $tagCount = DB::table('taggables')->where('taggable_type', Post::class)->count();
        $this->command->info("Created {$catCount} category relationships, {$tagCount} tag relationships.");
    }
}
