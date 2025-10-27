<?php

namespace App\Console\Commands;

use App\Models\Enumeration;
use App\Models\EnumerationPayment;
use App\Models\ProjectPayment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateRecurringPayments extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'payments:create-recurring 
                            {--frequency= : Specific frequency to process (weekly, monthly, yearly)}
                            {--project= : Specific project ID to process}
                            {--dry-run : Run without creating payments}
                            {--days-ahead=7 : Days ahead to look for due payments}';

    /**
     * The console command description.
     */
    protected $description = 'Create recurring enumeration payments based on project payment schedules';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $frequency = $this->option('frequency');
        $projectId = $this->option('project');
        $dryRun = $this->option('dry-run');
        $daysAhead = (int) $this->option('days-ahead');

        $this->info('Starting recurring payment generation...');

        if ($dryRun) {
            $this->warn('Running in DRY RUN mode - no payments will be created');
        }

        $frequencies = $frequency ? [$frequency] : ['weekly', 'monthly', 'yearly'];
        $totalCreated = 0;

        foreach ($frequencies as $freq) {
            $created = $this->processFrequency($freq, $projectId, $daysAhead, $dryRun);
            $totalCreated += $created;

            $this->info("Created {$created} {$freq} payments");
        }

        $this->info("Total payments created: {$totalCreated}");

        Log::info('Recurring payments processed', [
            'total_created' => $totalCreated,
            'frequency' => $frequency,
            'project_id' => $projectId,
            'dry_run' => $dryRun
        ]);

        return Command::SUCCESS;
    }

    /**
     * Process payments for a specific frequency.
     */
    protected function processFrequency(string $frequency, ?int $projectId, int $daysAhead, bool $dryRun): int
    {
        $lookAheadDate = now()->addDays($daysAhead);
        $created = 0;

        // Get active project payments with this frequency
        $query = ProjectPayment::where('frequency', $frequency)
            ->active()
            ->validForDate()
            ->with('project');

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $projectPayments = $query->get();

        $this->info("Processing {$projectPayments->count()} {$frequency} project payments...");

        $bar = $this->output->createProgressBar($projectPayments->count());
        $bar->start();

        foreach ($projectPayments as $projectPayment) {
            $created += $this->processProjectPayment($projectPayment, $lookAheadDate, $dryRun);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        return $created;
    }

    /**
     * Process a single project payment configuration.
     */
    protected function processProjectPayment(ProjectPayment $projectPayment, Carbon $lookAheadDate, bool $dryRun): int
    {
        $created = 0;

        // Get all enumerations for this project
        $enumerations = Enumeration::where('project_id', $projectPayment->project_id)
            ->whereHas('project', function ($q) {
                $q->where('status', 'active'); // Only active projects
            })
            ->get();

        foreach ($enumerations as $enumeration) {
            if ($this->shouldCreatePayment($enumeration, $projectPayment, $lookAheadDate)) {
                if (!$dryRun) {
                    $this->createEnumerationPayment($enumeration, $projectPayment);
                }
                $created++;
            }
        }

        return $created;
    }

    /**
     * Check if a new payment should be created for this enumeration.
     */
    protected function shouldCreatePayment(Enumeration $enumeration, ProjectPayment $projectPayment, Carbon $lookAheadDate): bool
    {
        // Get the last payment for this enumeration and project payment
        $lastPayment = EnumerationPayment::where('enumeration_id', $enumeration->id)
            ->where('project_payment_id', $projectPayment->id)
            ->orderBy('due_date', 'desc')
            ->first();

        if (!$lastPayment) {
            // No previous payment exists, check if enumeration date is before or equal to start date
            if ($projectPayment->start_date) {
                return $enumeration->enumerated_at->lte($projectPayment->start_date);
            }
            return true;
        }

        // Calculate next due date based on last payment
        $nextDueDate = $projectPayment->calculateNextDueDate($lastPayment->due_date);

        // Check if next due date is within our look-ahead window
        if ($nextDueDate->gt($lookAheadDate)) {
            return false;
        }

        // Check if payment already exists for this due date
        $existingPayment = EnumerationPayment::where('enumeration_id', $enumeration->id)
            ->where('project_payment_id', $projectPayment->id)
            ->where('due_date', $nextDueDate)
            ->exists();

        return !$existingPayment;
    }

    /**
     * Create an enumeration payment.
     */
    protected function createEnumerationPayment(Enumeration $enumeration, ProjectPayment $projectPayment): void
    {
        try {
            DB::beginTransaction();

            // Get the last payment to calculate next due date
            $lastPayment = EnumerationPayment::where('enumeration_id', $enumeration->id)
                ->where('project_payment_id', $projectPayment->id)
                ->orderBy('due_date', 'desc')
                ->first();

            $dueDate = $lastPayment
                ? $projectPayment->calculateNextDueDate($lastPayment->due_date)
                : ($projectPayment->start_date ?? now());

            EnumerationPayment::create([
                'enumeration_id' => $enumeration->id,
                'project_payment_id' => $projectPayment->id,
                'amount_due' => $projectPayment->amount,
                'amount_paid' => 0,
                'status' => 'pending',
                'due_date' => $dueDate,
            ]);

            DB::commit();

            Log::info('Recurring payment created', [
                'enumeration_id' => $enumeration->id,
                'project_payment_id' => $projectPayment->id,
                'due_date' => $dueDate,
                'amount' => $projectPayment->amount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create recurring payment', [
                'enumeration_id' => $enumeration->id,
                'project_payment_id' => $projectPayment->id,
                'error' => $e->getMessage()
            ]);

            $this->error("Failed to create payment for enumeration {$enumeration->id}: {$e->getMessage()}");
        }
    }
}
