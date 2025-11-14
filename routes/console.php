<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule pertemuan auto-close every 5 minutes
Schedule::command('pertemuan:auto-close')->everyFiveMinutes();

// Schedule rate limit cleanup daily at 2 AM
Schedule::command('pertemuan:cleanup-rate-limit')->dailyAt('02:00');

// Schedule daily report at 6 PM
Schedule::command('pertemuan:daily-report')->dailyAt('18:00');
