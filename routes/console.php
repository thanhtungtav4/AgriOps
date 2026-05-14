<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule alert checks
Schedule::command('alerts:check --farm=' . config('app.farm_id'))
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/alerts.log'));

// Run alert checks every 6 hours for active farms
Schedule::command('alerts:check')
    ->twiceDaily(8, 14)
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/alerts.log'));