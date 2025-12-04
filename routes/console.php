<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command("inspire", function () {
    $this->comment(Inspiring::quote());
})->purpose("Display an inspiring quote");

/*
|--------------------------------------------------------------------------
| Scheduled Commands
|--------------------------------------------------------------------------
*/

// Escalate order priorities daily at 6:00 AM
Schedule::command("orders:escalate-priorities --notify")
    ->dailyAt("06:00")
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path("logs/order-priority-escalation.log"));
