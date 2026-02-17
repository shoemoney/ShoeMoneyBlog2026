<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Backups
|--------------------------------------------------------------------------
|
| Backups run at 01:00 (cleanup) and 01:30 (new backup).
| Times chosen to avoid DST transition hours (02:00-03:00).
| Cleanup runs first to ensure space for new backup.
|
*/

Schedule::command('backup:clean')->daily()->at('01:00');
Schedule::command('backup:run')->daily()->at('01:30');

/*
|--------------------------------------------------------------------------
| AI Featured Image Generation
|--------------------------------------------------------------------------
|
| Picks up posts/pages missing featured images and dispatches
| generation jobs to the queue. Runs every minute, processes
| up to 5 items per run. Uses withoutOverlapping() so a slow
| batch won't stack up duplicate runs.
|
*/

Schedule::command('images:generate --limit=5 --type=all')
    ->everyMinute()
    ->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Algolia Search Index Sync
|--------------------------------------------------------------------------
|
| Re-imports posts and pages into Algolia daily at 02:00.
| Keeps search index in sync with any content changes.
|
*/

Schedule::command('scout:import', ['App\\Models\\Post'])->daily()->at('02:00');
Schedule::command('scout:import', ['App\\Models\\Page'])->daily()->at('02:05');
