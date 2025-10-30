<?php

namespace App\Console\Commands;

use App\Models\EnumerationPayment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkOverduePayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:mark-overdue
                            {--dry-run : Run without updating payments}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark pending payments as overdue when past their due date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('Running in DRY RUN mode - no payments will be updated');
        }

        $this->info('Starting overdue payment check...');

        // Find all pending or partial payments that are past their due date
        $overduePayments = EnumerationPayment::whereIn('status', ['pending', 'partial'])
            ->where('due_date', '<', now())
            ->with(['enumeration.project', 'projectPayment'])
            ->get();

        if ($overduePayments->isEmpty()) {
            $this->info('No overdue payments found.');
            return 0;
        }

        $this->info("Found {$overduePayments->count()} overdue payments.");

        $totalMarked = 0;
        $projectsSummary = [];

        foreach ($overduePayments as $payment) {
            $projectName = $payment->enumeration->project->name ?? 'Unknown Project';
            $paymentName = $payment->projectPayment->name ?? 'Unknown Payment';
            $daysOverdue = now()->diffInDays($payment->due_date);

            if (!isset($projectsSummary[$projectName])) {
                $projectsSummary[$projectName] = [
                    'count' => 0,
                    'total_amount' => 0,
                ];
            }

            $projectsSummary[$projectName]['count']++;
            $projectsSummary[$projectName]['total_amount'] += $payment->getOutstandingAmount();

            $this->line("  Enumeration #{$payment->enumeration_id} - {$paymentName} ({$daysOverdue} days overdue)");

            if (!$isDryRun) {
                $payment->status = 'overdue';
                $payment->save();
            }

            $totalMarked++;
        }

        $this->newLine();
        $this->info("✓ Completed successfully!");

        // Summary by project
        if (!empty($projectsSummary)) {
            $this->newLine();
            $this->info('Summary by Project:');

            $tableData = [];
            foreach ($projectsSummary as $projectName => $data) {
                $tableData[] = [
                    $projectName,
                    $data['count'],
                    number_format($data['total_amount'], 2),
                ];
            }

            $this->table(
                ['Project', 'Overdue Payments', 'Outstanding Amount'],
                $tableData
            );
        }

        $this->newLine();
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Marked as Overdue', $totalMarked],
            ]
        );

        if ($isDryRun) {
            $this->warn('DRY RUN: No payments were actually updated');
        }

        return 0;
    }
}
