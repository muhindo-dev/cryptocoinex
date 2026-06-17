<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Cryptocoinex Trading Scheduled Tasks ──────────────────────────────────────

// Keep Binance live-price cache warm so /trade/feed and /trade/price always read
// fresh data from Redis. Runs a single warming pass every 30 seconds.
Schedule::command('trading:warm-cache --once')
    ->everyThirtySeconds()
    ->withoutOverlapping()
    ->runInBackground();

// Activate/finalize tournaments based on their time window.
Schedule::command('trading:refresh-tournaments')
    ->everyMinute()
    ->withoutOverlapping();

// NOTE: Live Account profits are no longer accrued automatically at midnight.
// They are paid out via admin-initiated profit distributions
// (Admin → Live Account → Distributions), so there is no scheduled task here.
