<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'enumeration_payment_id',
        'amount',
        'type',
        'status',
        'payment_method',
        'reference',
        'gateway_transaction_id',
        'gateway_response',
        'payment_source',
        'recorded_by_user_id',
        'recorded_by_staff_id',
        'transaction_date',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'transaction_date' => 'datetime',
    ];

    /**
     * Get the enumeration payment that owns this transaction.
     */
    public function enumerationPayment(): BelongsTo
    {
        return $this->belongsTo(EnumerationPayment::class);
    }

    /**
     * Get the User who recorded this transaction.
     */
    public function recordedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    /**
     * Get the staff member who recorded this transaction.
     */
    public function recordedByStaff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'recorded_by_staff_id');
    }

    /**
     * Scope for payment transactions only.
     */
    public function scopePayments($query)
    {
        return $query->where('type', 'payment');
    }

    /**
     * Scope for refund transactions only.
     */
    public function scopeRefunds($query)
    {
        return $query->where('type', 'refund');
    }

    /**
     * Scope for adjustment transactions only.
     */
    public function scopeAdjustments($query)
    {
        return $query->where('type', 'adjustment');
    }

    /**
     * Scope for manual transactions.
     */
    public function scopeManual($query)
    {
        return $query->where('payment_source', 'manual');
    }

    /**
     * Scope for gateway transactions.
     */
    public function scopeGateway($query)
    {
        return $query->where('payment_source', 'gateway');
    }

    /**
     * Check if this is a gateway transaction.
     */
    public function isGatewayTransaction(): bool
    {
        return $this->payment_source === 'gateway';
    }

    /**
     * Check if this is a manual transaction.
     */
    public function isManualTransaction(): bool
    {
        return $this->payment_source === 'manual';
    }

    /**
     * Get the display name for this transaction.
     */
    public function getDisplayNameAttribute(): string
    {
        return ucfirst($this->type) . ' of ' . number_format($this->amount, 2);
    }

    /**
     * Get the name of user that recorded this transaction.
     */
    public function getRecordedByAttribute(): string
    {
        if ($this->payment_source === 'gateway') {
            return $this->enumerationPayment->enumeration->enumerationData->first()->value;
        }

        if ($this->recorded_by_user_id) {
            return $this->recordedByUser->name;
        }

        if ($this->recorded_by_staff_id && $this->recordedByStaff) {
            return $this->recordedByStaff->name;
        }

        return 'System';
    }
}
