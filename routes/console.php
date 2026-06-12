<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// World Cup results sync (Laravel 13 registers schedules here, not in a Console Kernel).
Schedule::command('wc:sync-results')
    ->everyFiveMinutes()
    ->withoutOverlapping();

// Re-match fixtures daily at 3am Adelaide time in case new knockout fixtures are added upstream.
Schedule::command('wc:match-fixtures')
    ->dailyAt('03:00')
    ->timezone('Australia/Adelaide');
