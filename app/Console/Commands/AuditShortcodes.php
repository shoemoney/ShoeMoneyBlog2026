<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Page;
use Illuminate\Console\Command;

class AuditShortcodes extends Command
{
    protected $signature = 'migration:audit-shortcodes {--export : Export results to file}';
    protected $description = 'Audit WordPress shortcodes in migrated content';

    private array $shortcodes = [];

    public function handle(): int
    {
        $this->info('Auditing shortcodes in migrated content...');
        $this->newLine();

        // Regex to match WordPress shortcodes: [shortcode] or [shortcode attr="value"]
        $pattern = '/\[([a-zA-Z_][a-zA-Z0-9_-]*)[^\]]*\]/';

        $this->info('Scanning posts...');
        $bar = $this->output->createProgressBar(Post::count());

        Post::chunk(500, function ($posts) use ($pattern, $bar) {
            foreach ($posts as $post) {
                preg_match_all($pattern, $post->content, $matches);
                foreach ($matches[1] as $shortcode) {
                    $shortcode = strtolower($shortcode);
                    if (!isset($this->shortcodes[$shortcode])) {
                        $this->shortcodes[$shortcode] = [
                            'count' => 0,
                            'posts' => [],
                            'example' => null,
                        ];
                    }
                    $this->shortcodes[$shortcode]['count']++;
                    if (count($this->shortcodes[$shortcode]['posts']) < 5) {
                        $this->shortcodes[$shortcode]['posts'][] = $post->id;
                    }
                    if (!$this->shortcodes[$shortcode]['example']) {
                        // Find full shortcode for example
                        preg_match('/\[' . preg_quote($shortcode, '/') . '[^\]]*\]/', $post->content, $full);
                        $this->shortcodes[$shortcode]['example'] = $full[0] ?? "[{$shortcode}]";
                    }
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        $this->info('Scanning pages...');
        Page::chunk(100, function ($pages) use ($pattern) {
            foreach ($pages as $page) {
                preg_match_all($pattern, $page->content, $matches);
                foreach ($matches[1] as $shortcode) {
                    $shortcode = strtolower($shortcode);
                    if (!isset($this->shortcodes[$shortcode])) {
                        $this->shortcodes[$shortcode] = [
                            'count' => 0,
                            'posts' => [],
                            'example' => null,
                        ];
                    }
                    $this->shortcodes[$shortcode]['count']++;
                }
            }
        });

        $this->printResults();

        if ($this->option('export')) {
            $this->exportResults();
        }

        return Command::SUCCESS;
    }

    private function printResults(): void
    {
        $this->newLine();

        if (empty($this->shortcodes)) {
            $this->info('No shortcodes found in content!');
            return;
        }

        // Sort by count descending
        uasort($this->shortcodes, fn($a, $b) => $b['count'] <=> $a['count']);

        $this->info('Shortcodes found:');
        $this->newLine();

        $tableData = [];
        foreach ($this->shortcodes as $shortcode => $data) {
            $tableData[] = [
                $shortcode,
                $data['count'],
                substr($data['example'], 0, 60) . (strlen($data['example']) > 60 ? '...' : ''),
            ];
        }

        $this->table(['Shortcode', 'Count', 'Example'], $tableData);

        $this->newLine();
        $totalUsages = array_sum(array_column($this->shortcodes, 'count'));
        $this->info("Total: " . count($this->shortcodes) . " unique shortcodes, {$totalUsages} usages");

        $this->newLine();
        $this->warn('Recommendation:');
        $this->line('  - Shortcodes with < 10 usages: Convert manually during migration');
        $this->line('  - Shortcodes with > 10 usages: Consider creating Blade components or conversion script');
    }

    private function exportResults(): void
    {
        $filename = storage_path('app/shortcode-audit-' . date('Y-m-d-His') . '.json');

        $export = [
            'generated_at' => now()->toIso8601String(),
            'total_unique' => count($this->shortcodes),
            'total_usages' => array_sum(array_column($this->shortcodes, 'count')),
            'shortcodes' => $this->shortcodes,
        ];

        file_put_contents($filename, json_encode($export, JSON_PRETTY_PRINT));

        $this->info("Results exported to: {$filename}");
    }
}
