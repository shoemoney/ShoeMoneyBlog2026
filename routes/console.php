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
