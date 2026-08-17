<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

    Artisan::command('inspire', function () {
        $this->comment(Inspiring::quote());
    })->purpose('Display an inspiring quote');

    Schedule::command('search-console:sync --queue')
        ->dailyAt('03:00')
        ->withoutOverlapping()
        ->onOneServer();

    Schedule::command('analytics:sync --queue')
        ->dailyAt('03:30')
        ->withoutOverlapping()
        ->onOneServer();

    Schedule::command('business-profile:sync --queue')
        ->dailyAt('04:00')
        ->withoutOverlapping()
        ->onOneServer();

    Schedule::command('google-ads:sync --queue')
    ->dailyAt('04:30')
    ->withoutOverlapping()
    ->onOneServer();

    Schedule::command('meta:sync --days=7')
        ->hourly()
        ->withoutOverlapping()
        ->runInBackground();