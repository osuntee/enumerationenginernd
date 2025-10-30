<?php

namespace App\Console\Commands;

use App\Models\Enumeration;
use App\Models\ProjectPayment;
use Illuminate\Console\Command;

class CreateWeeklyEnumerationPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:create-weekly
                            {--dry-run : Run without creating payments}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create weekly enumeration payments for all enumerations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('Running in DRY RUN mode - no payments will be created');
        }

        $this->info('Starting weekly payment creation...');

        // Get all active weekly project payments
        $weeklyPayments = ProjectPayment::where('frequency', 'weekly')
            ->active()
            ->validForDate()
            ->with('project')
            ->get();

        if ($weeklyPayments->isEmpty()) {
            $this->info('No active weekly payment configurations found.');
            return 0;
        }

        $this->info("Found {$weeklyPayments->count()} active weekly payment configurations.");

        $totalCreated = 0;
        $totalSkipped = 0;

        foreach ($weeklyPayments as $projectPayment) {
            $this->line("\nProcessing: {$projectPayment->name} for project {$projectPayment->project->name}");

            // Get all enumerations for this project
            $enumerations = Enumeration::where('project_id', $projectPayment->project_id)
                ->with('enumerationPayments')
                ->get();

            $this->info("  Found {$enumerations->count()} enumerations");

            foreach ($enumerations as $enumeration) {
                // Check if a payment already exists for this week
                $existingPayment = $enumeration->enumerationPayments()
                    ->where('project_payment_id', $projectPayment->id)
                    ->where('due_date', '>=', now()->startOfWeek())
                    ->where('due_date', '<=', now()->endOfWeek())
                    ->exists();

                if ($existingPayment) {
                    $totalSkipped++;
                    continue;
                }

                // Calculate the due date (end of current week)
                $dueDate = now()->endOfWeek();

                if (!$isDryRun) {
                    // Create the payment
                    $enumeration->enumerationPayments()->create([
                        'project_payment_id' => $projectPayment->id,
                        'amount_due' => $projectPayment->amount,
                        'amount_paid' => 0,
                        'status' => 'pending',
                        'due_date' => $dueDate,
                    ]);
                }

                $totalCreated++;
            }
        }

        $this->newLine();
        $this->info("✓ Completed successfully!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Payments Created', $totalCreated],
                ['Payments Skipped', $totalSkipped],
            ]
        );

        if ($isDryRun) {
            $this->warn('DRY RUN: No payments were actually created');
        }

        return 0;
    }
}
