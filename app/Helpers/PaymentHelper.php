<?php

namespace App\Helpers;

use App\Models\ProjectPayment;
use App\Models\EnumerationPayment;
use Carbon\Carbon;

class PaymentHelper
{
    /**
     * Generate enumeration payments for a project payment configuration.
     */
    public static function generateNewEnumerationPayments(ProjectPayment $projectPayment, $dueDate = null): int
    {
        $project = $projectPayment->project;
        $dueDate = $dueDate ? Carbon::parse($dueDate) : now()->addDays(7);
        $generated = 0;

        // Get enumerations that don't already have this payment
        $enumerations = $project->enumerations()
            ->whereDoesntHave('enumerationPayments', function ($query) use ($projectPayment) {
                $query->where('project_payment_id', $projectPayment->id);
            })
            ->get();

        foreach ($enumerations as $enumeration) {
            EnumerationPayment::create([
                'enumeration_id' => $enumeration->id,
                'project_payment_id' => $projectPayment->id,
                'amount_due' => $projectPayment->amount,
                'due_date' => $dueDate,
                'status' => 'pending',
            ]);

            $generated++;
        }

        return $generated;
    }
}
