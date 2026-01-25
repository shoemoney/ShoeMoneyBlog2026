<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate {--dry-run : Show stats without generating}';
    protected $description = 'Generate sitemap.xml with all published content';

    public function handle(): int
    {
        $this->info('');
        $this->info('Generating sitemap...');
        $this->info('');

        $sitemap = Sitemap::create();

        // Add homepage (highest priority)
        $sitemap->add(
            Url::create('/')
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(1.0)
        );

        $stats = [
            'posts' => 0,
            'pages' => 0,
            'categories' => 0,
            'tags' => 0,
        ];

        // Add posts (priority 0.8) - chunked for memory efficiency
        $this->info('Processing posts...');
        Post::published()
            ->orderBy('published_at', 'desc')
            ->chunk(500, function ($posts) use ($sitemap, &$stats) {
                foreach ($posts as $post) {
                    $sitemap->add(
                        Url::create($post->url)
                            ->setLastModificationDate($post->updated_at ?? $post->published_at)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                            ->setPriority(0.8)
                    );
                    $stats['posts']++;
                }
            });
        $this->line("  Added {$stats['posts']} posts");

        // Add pages (priority 0.6) - chunked for memory efficiency
        $this->info('Processing pages...');
        Page::query()
            ->chunk(100, function ($pages) use ($sitemap, &$stats) {
                foreach ($pages as $page) {
                    $sitemap->add(
                        Url::create($page->url)
                            ->setLastModificationDate($page->updated_at)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                            ->setPriority(0.6)
                    );
                    $stats['pages']++;
                }
            });
        $this->line("  Added {$stats['pages']} pages");

        // Add categories (priority 0.5)
        $this->info('Processing categories...');
        Category::query()
            ->chunk(100, function ($categories) use ($sitemap, &$stats) {
                foreach ($categories as $category) {
                    $sitemap->add(
                        Url::create($category->url)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.5)
                    );
                    $stats['categories']++;
                }
            });
        $this->line("  Added {$stats['categories']} categories");

        // Add tags with published posts only (priority 0.3)
        $this->info('Processing tags (only with published posts)...');
        Tag::whereHas('posts', function ($query) {
            $query->where('status', 'published')
                ->whereNotNull('published_at');
        })
            ->chunk(500, function ($tags) use ($sitemap, &$stats) {
                foreach ($tags as $tag) {
                    $sitemap->add(
                        Url::create($tag->url)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.3)
                    );
                    $stats['tags']++;
                }
            });
        $this->line("  Added {$stats['tags']} tags");

        // Summary
        $total = 1 + $stats['posts'] + $stats['pages'] + $stats['categories'] + $stats['tags'];
        $this->info('');
        $this->info("Total URLs: {$total}");

        if ($this->option('dry-run')) {
            $this->warn('Dry run - sitemap not written');
            return Command::SUCCESS;
        }

        // Write sitemap
        $path = public_path('sitemap.xml');
        $sitemap->writeToFile($path);

        $this->info("Sitemap written to: {$path}");
        $this->info('');

        return Command::SUCCESS;
    }
}
