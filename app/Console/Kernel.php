<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Create weekly payments - runs every Monday at 1:00 AM
        $schedule->command('payments:create-recurring --frequency=weekly')
            ->weeklyOn(1, '1:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->onSuccess(function () {
                \Log::info('Weekly payment generation completed successfully');
            })
            ->onFailure(function () {
                \Log::error('Weekly payment generation failed');
            });

        // Create monthly payments - runs on the 1st of each month at 2:00 AM
        $schedule->command('payments:create-recurring --frequency=monthly')
            ->monthlyOn(1, '2:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->onSuccess(function () {
                \Log::info('Monthly payment generation completed successfully');
            })
            ->onFailure(function () {
                \Log::error('Monthly payment generation failed');
            });

        // Create yearly payments - runs on January 1st at 3:00 AM
        $schedule->command('payments:create-recurring --frequency=yearly')
            ->yearlyOn(1, 1, '3:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->onSuccess(function () {
                \Log::info('Yearly payment generation completed successfully');
            })
            ->onFailure(function () {
                \Log::error('Yearly payment generation failed');
            });

        // Mark overdue payments - runs daily at 12:30 AM
        $schedule->command('payments:mark-overdue')
            ->dailyAt('00:30')
            ->withoutOverlapping()
            ->runInBackground()
            ->onSuccess(function () {
                \Log::info('Overdue payment marking completed successfully');
            })
            ->onFailure(function () {
                \Log::error('Overdue payment marking failed');
            });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
