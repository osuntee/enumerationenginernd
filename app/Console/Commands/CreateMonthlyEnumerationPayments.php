<?php

namespace App\Console\Commands;

use App\Models\Enumeration;
use App\Models\ProjectPayment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CreateMonthlyEnumerationPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:create-monthly
                            {--dry-run : Run without creating payments}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create monthly enumeration payments for all enumerations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('Running in DRY RUN mode - no payments will be created');
        }

        $this->info('Starting monthly payment creation...');

        // Get all active monthly project payments
        $monthlyPayments = ProjectPayment::where('frequency', 'monthly')
            ->active()
            ->validForDate()
            ->with('project')
            ->get();

        if ($monthlyPayments->isEmpty()) {
            $this->info('No active monthly payment configurations found.');
            return 0;
        }

        $this->info("Found {$monthlyPayments->count()} active monthly payment configurations.");

        $totalCreated = 0;
        $totalSkipped = 0;

        foreach ($monthlyPayments as $projectPayment) {
            $this->line("\nProcessing: {$projectPayment->name} for project {$projectPayment->project->name}");

            // Get all enumerations for this project
            $enumerations = Enumeration::where('project_id', $projectPayment->project_id)
                ->with('enumerationPayments')
                ->get();

            $this->info("  Found {$enumerations->count()} enumerations");

            foreach ($enumerations as $enumeration) {
                // Check if a payment already exists for this month
                $existingPayment = $enumeration->enumerationPayments()
                    ->where('project_payment_id', $projectPayment->id)
                    ->where('due_date', '>=', now()->startOfMonth())
                    ->where('due_date', '<=', now()->endOfMonth())
                    ->exists();

                if ($existingPayment) {
                    $totalSkipped++;
                    continue;
                }

                // Calculate the due date (end of current month)
                $dueDate = now()->endOfMonth();

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
