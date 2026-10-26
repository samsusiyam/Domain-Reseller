<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Shared Hosting cron jobs - All runs via `php artisan schedule:run` in cPanel Cron
Schedule::command('domains:check-expiry')->dailyAt('01:00');
Schedule::command('domains:process-autorenew')->dailyAt('02:00');
Schedule::command('domains:sync-status')->hourly();
Schedule::command('registrars:check-balances')->everySixHours();
Schedule::command('currency:update-rates')->dailyAt('00:30');
Schedule::command('webhooks:retry-failed')->everyFifteenMinutes();
Schedule::command('reports:daily-summary')->dailyAt('23:55');
Schedule::command('system:cleanup-logs')->weeklyOn(0, '03:00');
Schedule::command('queue:work --stop-when-empty --max-time=50')->everyMinute()->withoutOverlapping();
