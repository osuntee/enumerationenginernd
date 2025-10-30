<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestCronJobsRunning extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-cron-jobs-running';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \Log::info('⚡ Scheduler Cron Job test executed successfully at ' . now());
    }
}
