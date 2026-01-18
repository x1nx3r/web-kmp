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

// Check overdue piutang daily at 8:00 AM and notify accounting
Schedule::command("piutang:check-overdue --notify-near-due")
    ->dailyAt("08:00")
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path("logs/piutang-overdue-check.log"));
// Send forecast pending reminder daily at 6:00 AM
Schedule::command("forecast:notify-pending")
    ->dailyAt("06:00")
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path("logs/forecast-pending-reminder.log"));

// Send pengiriman pending reminder daily at 6:00 AM
Schedule::command("pengiriman:notify-pending")
    ->dailyAt("06:00")
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path("logs/pengiriman-pending-reminder.log"));

// Send pengiriman review reminder daily at 6:00 AM
Schedule::command("pengiriman:notify-review")
    ->dailyAt("06:00")
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path("logs/pengiriman-review-reminder.log"));

// Push OTEL metrics every 3 hours
Schedule::command("metrics:push-otel")
    ->everyThreeHours()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path("logs/metrics-otel.log"));
