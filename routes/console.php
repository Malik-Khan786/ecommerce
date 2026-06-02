<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Abandoned cart recovery — run hourly
Schedule::command('carts:send-recovery-emails')->hourly();

// Inventory alerts — run daily at 9am
Schedule::command('inventory:send-alerts')->dailyAt('09:00');

// Publish scheduled products — run every 5 minutes
Schedule::command('products:publish-scheduled')->everyFiveMinutes();

// Weekly newsletter — every Monday at 10:00 AM
Schedule::command('newsletter:send-weekly')->weeklyOn(1, '10:00');
