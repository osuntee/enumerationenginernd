<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class EnumerationPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'enumeration_id',
        'project_payment_id',
        'amount_due',
        'amount_paid',
        'status',
        'due_date',
        'paid_at',
        'payment_method',
        'payment_reference',
        'notes'
    ];

    protected $casts = [
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the enumeration that owns this payment.
     */
    public function enumeration(): BelongsTo
    {
        return $this->belongsTo(Enumeration::class);
    }

    /**
     * Get the project payment configuration.
     */
    public function projectPayment(): BelongsTo
    {
        return $this->belongsTo(ProjectPayment::class);
    }

    /**
     * Get all payment transactions for this enumeration payment.
     */
    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /**
     * Get only payment transactions (excluding refunds/adjustments).
     */
    public function payments(): HasMany
    {
        return $this->paymentTransactions()->where('type', 'payment');
    }

    /**
     * Scope for pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for overdue payments.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->where('status', 'pending')
                    ->where('due_date', '<', now());
            });
    }

    /**
     * Scope for paid payments.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Check if this payment is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date->isPast();
    }

    /**
     * Check if this payment is fully paid.
     */
    public function isFullyPaid(): bool
    {
        return $this->status === 'paid' && $this->amount_paid >= $this->amount_due;
    }

    /**
     * Check if partial payments are allowed.
     */
    public function allowsPartialPayments(): bool
    {
        return $this->projectPayment->allow_partial_payments;
    }

    /**
     * Get the outstanding amount.
     */
    public function getOutstandingAmount(): float
    {
        return max(0, $this->amount_due - $this->amount_paid);
    }

    /**
     * Record a payment transaction.
     */
    public function recordPayment(
        float $amount,
        string $paymentMethod,
        string $source = 'manual',
        ?int $staffId = null,
        ?string $reference = null,
        ?string $gatewayTransactionId = null,
        ?array $gatewayResponse = null,
        ?string $notes = null
    ): PaymentTransaction {
        $transaction = $this->paymentTransactions()->create([
            'amount' => $amount,
            'type' => 'payment',
            'payment_method' => $paymentMethod,
            'reference' => $reference,
            'gateway_transaction_id' => $gatewayTransactionId,
            'gateway_response' => $gatewayResponse,
            'payment_source' => $source,
            'recorded_by_staff_id' => $staffId,
            'transaction_date' => now(),
            'notes' => $notes,
        ]);

        $this->updatePaymentStatus();

        return $transaction;
    }

    /**
     * Update payment status based on transactions.
     */
    public function updatePaymentStatus(): void
    {
        $totalPaid = $this->paymentTransactions()
            ->where('type', 'payment')
            ->sum('amount');

        // Handle refunds and adjustments
        $totalRefunds = $this->paymentTransactions()
            ->where('type', 'refund')
            ->sum('amount');

        $totalAdjustments = $this->paymentTransactions()
            ->where('type', 'adjustment')
            ->sum('amount');

        $netPaid = $totalPaid - $totalRefunds + $totalAdjustments;

        $this->amount_paid = max(0, $netPaid);

        if ($this->amount_paid >= $this->amount_due) {
            $this->status = 'paid';
            $this->paid_at = $this->paid_at ?? now();
        } elseif ($this->amount_paid > 0) {
            $this->status = 'partial';
        } elseif ($this->isOverdue()) {
            $this->status = 'overdue';
        } else {
            $this->status = 'pending';
        }

        $this->save();
    }

    /**
     * Mark payment as waived.
     */
    public function markAsWaived(?int $staffId = null, ?string $notes = null): void
    {
        $this->status = 'waived';
        $this->notes = $notes;
        $this->save();

        // Record an adjustment transaction
        if ($this->getOutstandingAmount() > 0) {
            $this->paymentTransactions()->create([
                'amount' => $this->getOutstandingAmount(),
                'type' => 'adjustment',
                'payment_method' => 'waived',
                'payment_source' => 'manual',
                'recorded_by_staff_id' => $staffId,
                'transaction_date' => now(),
                'notes' => 'Payment waived: ' . ($notes ?? 'No reason provided'),
            ]);
        }

        $this->updatePaymentStatus();
    }
}
