<?php

namespace App\Console\Commands;

use App\Jobs\GenerateFeaturedImageJob;
use App\Models\FeaturedImage;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class GenerateFeaturedImages extends Command
{
    protected $signature = 'images:generate
        {--limit=10 : Number of posts/pages to process}
        {--type=all : Type to process: posts, pages, or all}
        {--force : Regenerate even if image already exists}
        {--dry-run : Show what would be generated without doing anything}
        {--sync : Run synchronously instead of dispatching to queue}
        {--failed-only : Only retry failed generations}';

    protected $description = 'Generate AI featured images for posts and pages';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $type = $this->option('type');
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');
        $sync = $this->option('sync');
        $failedOnly = $this->option('failed-only');

        $this->info("Featured Image Generator");
        $this->info("========================");

        $items = collect();

        if (in_array($type, ['posts', 'all'])) {
            $posts = $this->getItems(Post::class, $limit, $force, $failedOnly);
            $items = $items->merge($posts->map(fn ($p) => ['model' => $p, 'type' => 'Post']));
        }

        if (in_array($type, ['pages', 'all'])) {
            $remaining = $limit - $items->count();
            if ($remaining > 0) {
                $pages = $this->getItems(Page::class, $remaining, $force, $failedOnly);
                $items = $items->merge($pages->map(fn ($p) => ['model' => $p, 'type' => 'Page']));
            }
        }

        if ($items->isEmpty()) {
            $this->info('No items found that need featured images.');
            return self::SUCCESS;
        }

        $this->info("Found {$items->count()} items to process:");
        $this->newLine();

        // Display table
        $tableData = $items->map(fn ($item) => [
            $item['type'],
            $item['model']->id,
            mb_substr($item['model']->title, 0, 60),
            $item['model']->slug,
            $item['model']->featuredImage?->status ?? 'new',
        ])->toArray();

        $this->table(['Type', 'ID', 'Title', 'Slug', 'Status'], $tableData);

        if ($dryRun) {
            $this->warn('Dry run - no images will be generated.');
            return self::SUCCESS;
        }

        $this->newLine();
        $bar = $this->output->createProgressBar($items->count());
        $bar->start();

        foreach ($items as $item) {
            $model = $item['model'];

            // Create or reset the FeaturedImage record
            $featuredImage = FeaturedImage::updateOrCreate(
                [
                    'imageable_id' => $model->id,
                    'imageable_type' => get_class($model),
                ],
                $force ? [
                    'status' => 'pending',
                    'attempts' => 0,
                    'error_message' => null,
                ] : [
                    'status' => 'pending',
                ]
            );

            if ($sync) {
                try {
                    GenerateFeaturedImageJob::dispatchSync($featuredImage->id);
                    $bar->advance();
                } catch (\Throwable $e) {
                    $bar->advance();
                    $this->newLine();
                    $this->error("  Failed: {$model->slug} - {$e->getMessage()}");
                }
            } else {
                GenerateFeaturedImageJob::dispatch($featuredImage->id);
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        if ($sync) {
            $completed = FeaturedImage::where('status', 'completed')
                ->whereIn('imageable_id', $items->pluck('model.id'))
                ->count();
            $this->info("Completed: {$completed}/{$items->count()}");
        } else {
            $this->info("Dispatched {$items->count()} jobs to the queue.");
            $this->info('Run `php artisan queue:work` to process them.');
        }

        return self::SUCCESS;
    }

    private function getItems(string $modelClass, int $limit, bool $force, bool $failedOnly)
    {
        $query = $modelClass::query();

        // Posts have published scope, pages don't
        if ($modelClass === Post::class) {
            $query->published()->orderBy('published_at', 'desc');
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        if ($failedOnly) {
            $query->whereHas('featuredImage', function (Builder $q) {
                $q->where('status', 'failed');
            });
        } elseif (!$force) {
            // Skip items that already have completed featured images
            $query->where(function (Builder $q) {
                $q->whereDoesntHave('featuredImage')
                  ->orWhereHas('featuredImage', function (Builder $q2) {
                      $q2->whereIn('status', ['pending', 'failed']);
                  });
            });
        }

        return $query->with('featuredImage')->limit($limit)->get();
    }
}
