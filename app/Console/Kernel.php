<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // Run daily at 00:30 to mark overdue payments
        $schedule->command('payments:mark-overdue')
            ->dailyAt('00:30')
            ->withoutOverlapping();

        // Run on January 1st at 00:01
        $schedule->command('payments:create-yearly')
            ->yearlyOn(1, 1, '00:01')
            ->withoutOverlapping();

        // Run on the 1st of every month at 00:01
        $schedule->command('payments:create-monthly')
            ->monthlyOn(1, '00:01')
            ->withoutOverlapping();

        // Run every Monday at 00:01
        $schedule->command('payments:create-weekly')
            ->weeklyOn(1, '00:01')
            ->withoutOverlapping();
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
