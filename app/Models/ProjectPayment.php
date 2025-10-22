<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class ProjectPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'amount',
        'frequency',
        'description',
        'is_active',
        'allow_partial_payments',
        'payment_type',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
        'allow_partial_payments' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the project that owns this payment configuration.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get all enumeration payments for this project payment.
     */
    public function enumerationPayments(): HasMany
    {
        return $this->hasMany(EnumerationPayment::class);
    }

    /**
     * Scope a query to only include active project payments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include active project payments.
     */
    public function scopeAllowsPartialPayments($query)
    {
        return $query->where('allow_partial_payments', true);
    }

    /**
     * Scope a query to include payments valid for a specific date.
     */
    public function scopeValidForDate($query, $date = null)
    {
        $date = $date ? Carbon::parse($date) : now();

        return $query->where(function ($q) use ($date) {
            $q->whereNull('start_date')->orWhere('start_date', '<=', $date);
        })->where(function ($q) use ($date) {
            $q->whereNull('end_date')->orWhere('end_date', '>=', $date);
        });
    }

    /**
     * Check if this payment configuration is currently valid.
     */
    public function isValidForDate($date = null): bool
    {
        $date = $date ? Carbon::parse($date) : now();

        $startValid = !$this->start_date || $this->start_date->lte($date);
        $endValid = !$this->end_date || $this->end_date->gte($date);

        return $this->is_active && $startValid && $endValid;
    }

    /**
     * Calculate next due date based on frequency and last payment.
     */
    public function calculateNextDueDate($lastDueDate = null): Carbon
    {
        $baseDate = $lastDueDate ? Carbon::parse($lastDueDate) : ($this->start_date ?? now());

        return match ($this->frequency) {
            'weekly' => $baseDate->addWeek(),
            'monthly' => $baseDate->addMonth(),
            'yearly' => $baseDate->addYear(),
            'one_off' => $baseDate,
            default => $baseDate,
        };
    }

    /**
     * Check if this payment allows manual processing.
     */
    public function allowsManualPayment(): bool
    {
        return in_array($this->payment_type, ['manual', 'both']);
    }

    /**
     * Check if this payment allows gateway processing.
     */
    public function allowsGatewayPayment(): bool
    {
        return in_array($this->payment_type, ['gateway', 'both']);
    }

    /**
     * Get total amount collected for this payment configuration.
     */
    public function getTotalCollected()
    {
        return $this->enumerationPayments()
            ->sum('amount_paid');
    }

    /**
     * Get total amount outstanding for this payment configuration.
     */
    public function getTotalOutstanding()
    {
        return $this->enumerationPayments()
            ->selectRaw('SUM(amount_due - amount_paid) as outstanding')
            ->value('outstanding') ?? 0;
    }
}
