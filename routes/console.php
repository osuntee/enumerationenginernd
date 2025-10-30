<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Artisan Commands
|--------------------------------------------------------------------------
| You can define custom Artisan commands directly here or
| reference existing command classes.
|
| Example:
| Artisan::command('app:test-cron-jobs-running', function () {
|     \Log::info('Test cron job executed at ' . now());
| });
|
*/

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
| Define your scheduled commands here. These will be executed when
| your system cron runs `php artisan schedule:run` every minute.
|
*/

// Run every minute (for testing)
Schedule::command('app:test-cron-jobs-running')
    ->everyMinute()
    ->withoutOverlapping();

// Run daily at 00:30 to mark overdue payments
Schedule::command('payments:mark-overdue')
    ->dailyAt('00:30')
    ->withoutOverlapping();

// Run on January 1st at 00:01
Schedule::command('payments:create-yearly')
    ->yearlyOn(1, 1, '00:01')
    ->withoutOverlapping();

// Run on the 1st of every month at 00:01
Schedule::command('payments:create-monthly')
    ->monthlyOn(1, '00:01')
    ->withoutOverlapping();

// Run every Monday at 00:01
Schedule::command('payments:create-weekly')
    ->weeklyOn(1, '00:01')
    ->withoutOverlapping();
