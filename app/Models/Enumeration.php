<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enumeration extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'staff_id',
        'enumerated_at',
        'qrcode',
        'reference',
        'notes',
        'is_verified'
    ];

    protected $casts = [
        'enumerated_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    /**
     * Get the project that this enumeration belongs to.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the staff member who conducted this enumeration.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Get the enumeration data for this enumeration.
     */
    public function enumerationData()
    {
        return $this->hasMany(EnumerationData::class);
    }

    /**
     * Set field values for this enumeration.
     */
    public function setFieldValues(array $data)
    {
        // Delete existing data
        $this->enumerationData()->delete();

        // Create new data
        foreach ($data as $fieldName => $value) {
            $field = $this->project->projectFields()->where('name', $fieldName)->first();

            if ($field && $value !== null && $value !== '') {
                // Handle array values (checkboxes, etc.)
                if (is_array($value)) {
                    $value = array_filter($value); // Remove empty values
                    if (!empty($value)) {
                        $value = json_encode($value);
                    } else {
                        continue; // Skip empty arrays
                    }
                }

                EnumerationData::create([
                    'enumeration_id' => $this->id,
                    'project_field_id' => $field->id,
                    'value' => $value,
                ]);
            }
        }
    }

    /**
     * Get field values for this enumeration.
     */
    public function getFieldValues()
    {
        $values = [];

        foreach ($this->enumerationData as $data) {
            $fieldName = $data->projectField->name;
            $value = $data->value;

            // Handle JSON values (checkboxes, etc.)
            if ($data->projectField->type === 'checkboxes' && is_string($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $value = $decoded;
                }
            }

            // Convert checkbox values
            if ($data->projectField->type === 'checkbox') {
                $value = ($value == '1' || $value === true || $value === 'true');
            }

            $values[$fieldName] = $value;
        }

        return $values;
    }

    /**
     * Get a specific field value.
     */
    public function getFieldValue($fieldName)
    {
        $values = $this->getFieldValues();
        return $values[$fieldName] ?? null;
    }

    /**
     * Check if this enumeration is verified.
     */
    public function isVerified()
    {
        return $this->is_verified;
    }

    /**
     * Get the enumeration's display name.
     */
    public function getDisplayNameAttribute()
    {
        return "Enumeration #{$this->id}";
    }

    public function enumerationPayments(): HasMany
    {
        return $this->hasMany(EnumerationPayment::class);
    }

    public function pendingPayments(): HasMany
    {
        return $this->enumerationPayments()->pending();
    }

    public function overduePayments(): HasMany
    {
        return $this->enumerationPayments()->overdue();
    }

    public function getTotalAmountDue(): float
    {
        return $this->enumerationPayments()->sum('amount_due');
    }

    public function getTotalAmountPaid(): float
    {
        return $this->enumerationPayments()->sum('amount_paid');
    }

    public function getOutstandingBalance(): float
    {
        return $this->getTotalAmountDue() - $this->getTotalAmountPaid();
    }

    public function hasOutstandingPayments(): bool
    {
        return $this->getOutstandingBalance() > 0;
    }
}
