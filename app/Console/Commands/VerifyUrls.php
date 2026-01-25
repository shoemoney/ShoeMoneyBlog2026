<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class VerifyUrls extends Command
{
    protected $signature = 'urls:verify
                            {--type= : Content type to test (posts, pages, categories, tags)}
                            {--limit= : Maximum URLs to test per type}
                            {--base-url= : Base URL to use (defaults to APP_URL)}
                            {--quick : Quick check - sample 10 URLs from each type}';

    protected $description = 'Verify all migrated WordPress URLs resolve correctly';

    private string $baseUrl;
    private array $failures = [];
    private array $results = [
        'posts' => ['total' => 0, 'passed' => 0, 'failed' => 0],
        'pages' => ['total' => 0, 'passed' => 0, 'failed' => 0],
        'categories' => ['total' => 0, 'passed' => 0, 'failed' => 0],
        'tags' => ['total' => 0, 'passed' => 0, 'failed' => 0],
    ];

    public function handle(): int
    {
        $this->baseUrl = rtrim($this->option('base-url') ?? config('app.url'), '/');
        $limit = $this->option('quick') ? 10 : ($this->option('limit') ? (int) $this->option('limit') : null);
        $type = $this->option('type');

        $this->info('');
        $this->info('URL Verification');
        $this->info('================');
        $this->info("Base URL: {$this->baseUrl}");
        $this->info('');

        if ($type) {
            $this->testContentType($type, $limit);
        } else {
            $this->testContentType('posts', $limit);
            $this->testContentType('pages', $limit);
            $this->testContentType('categories', $limit);
            $this->testContentType('tags', $limit);
        }

        $this->printSummary();

        if (!empty($this->failures)) {
            $this->saveFailureReport();
        }

        return empty($this->failures) ? Command::SUCCESS : Command::FAILURE;
    }

    private function testContentType(string $type, ?int $limit): void
    {
        $query = match ($type) {
            'posts' => Post::published()->orderBy('published_at', 'desc'),
            'pages' => Page::query()->orderBy('id'),
            'categories' => Category::query()->orderBy('name'),
            'tags' => Tag::query()->orderBy('name'),
            default => throw new \InvalidArgumentException("Unknown type: {$type}"),
        };

        $total = $query->count();
        $testCount = $limit ? min($limit, $total) : $total;

        $this->info("Testing {$type}: {$testCount}" . ($limit ? " of {$total}" : '') . ' URLs');

        if ($testCount === 0) {
            $this->warn("  No {$type} found to test");
            return;
        }

        $bar = $this->output->createProgressBar($testCount);
        $bar->start();

        $processed = 0;
        $query->when($limit, fn($q) => $q->limit($limit))
            ->chunkById(100, function ($items) use ($type, &$processed, $bar, $limit) {
                foreach ($items as $item) {
                    if ($limit && $processed >= $limit) {
                        return false;
                    }

                    $this->testUrl($type, $item);
                    $processed++;
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();

        $stats = $this->results[$type];
        $status = $stats['failed'] === 0 ? '<fg=green>PASS</>' : '<fg=red>FAIL</>';
        $this->line("  Result: {$status} ({$stats['passed']} passed, {$stats['failed']} failed)");
        $this->newLine();
    }

    private function testUrl(string $type, $item): void
    {
        $url = $this->baseUrl . $item->url;

        try {
            $response = Http::timeout(10)->get($url);
            $status = $response->status();

            $this->results[$type]['total']++;

            if ($status === 200) {
                $this->results[$type]['passed']++;
            } else {
                $this->results[$type]['failed']++;
                $this->failures[] = [
                    'type' => $type,
                    'id' => $item->id,
                    'title' => $item->title ?? $item->name ?? $item->slug,
                    'url' => $url,
                    'status' => $status,
                    'error' => null,
                ];
            }
        } catch (\Exception $e) {
            $this->results[$type]['total']++;
            $this->results[$type]['failed']++;
            $this->failures[] = [
                'type' => $type,
                'id' => $item->id,
                'title' => $item->title ?? $item->name ?? $item->slug,
                'url' => $url,
                'status' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function printSummary(): void
    {
        $this->info('Summary');
        $this->info('=======');

        $headers = ['Type', 'Total', 'Passed', 'Failed', 'Status'];
        $rows = [];

        $totalPassed = 0;
        $totalFailed = 0;
        $totalTested = 0;

        foreach ($this->results as $type => $stats) {
            if ($stats['total'] === 0) {
                continue;
            }

            $status = $stats['failed'] === 0 ? 'PASS' : 'FAIL';
            $rows[] = [
                ucfirst($type),
                $stats['total'],
                $stats['passed'],
                $stats['failed'],
                $status,
            ];

            $totalPassed += $stats['passed'];
            $totalFailed += $stats['failed'];
            $totalTested += $stats['total'];
        }

        if ($totalTested > 0) {
            $rows[] = ['---', '---', '---', '---', '---'];
            $rows[] = [
                'TOTAL',
                $totalTested,
                $totalPassed,
                $totalFailed,
                $totalFailed === 0 ? 'PASS' : 'FAIL',
            ];
        }

        $this->table($headers, $rows);
        $this->newLine();

        if (!empty($this->failures)) {
            $this->error("Failed URLs:");
            $failureHeaders = ['Type', 'ID', 'Title', 'URL', 'Status', 'Error'];
            $failureRows = array_map(function ($f) {
                return [
                    $f['type'],
                    $f['id'],
                    mb_substr($f['title'], 0, 30) . (mb_strlen($f['title']) > 30 ? '...' : ''),
                    $f['url'],
                    $f['status'] ?? 'N/A',
                    $f['error'] ? mb_substr($f['error'], 0, 30) : '',
                ];
            }, array_slice($this->failures, 0, 20));

            $this->table($failureHeaders, $failureRows);

            if (count($this->failures) > 20) {
                $this->warn("  ... and " . (count($this->failures) - 20) . " more failures (see JSON report)");
            }
        }
    }

    private function saveFailureReport(): void
    {
        $reportPath = storage_path('logs/url-failures-' . date('Y-m-d-His') . '.json');
        $report = [
            'generated_at' => now()->toIso8601String(),
            'base_url' => $this->baseUrl,
            'summary' => $this->results,
            'total_failures' => count($this->failures),
            'failures' => $this->failures,
        ];

        file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT));
        $this->info("Full failure report saved to: {$reportPath}");
    }
}
