<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
        'latitude',
        'longitude',
        'is_verified',
        'self_enumerated',
        'api_enumeration',
    ];

    protected $casts = [
        'enumerated_at' => 'datetime',
        'is_verified' => 'boolean',
        'self_enumerated' => 'boolean',
        'api_enumeration' => 'boolean',
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
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::deleting(function ($enumeration) {
            $enumeration->load(['enumerationData.projectField']);

            foreach ($enumeration->enumerationData as $data) {
                if ($data->projectField && $data->projectField->type === 'file') {
                    // value is stored as /storage/path/to/file
                    // we need to remove /storage/ to get the disk path (assuming 'public' disk root is linked to public/storage)
                    $relativePath = str_replace('/storage/', '', $data->value);

                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                }
            }
        });
    }

    /**
     * Set field values for this enumeration.
     */
    public function setFieldValues(array $data)
    {
        $fields = $this->project->projectFields;

        foreach ($fields as $field) {
            $fieldName = $field->name;
            $value = $data[$fieldName] ?? null;
            $existsInPayload = array_key_exists($fieldName, $data);

            // Find existing data for this field
            $existing = $this->enumerationData()->where('project_field_id', $field->id)->first();

            // Handle File Uploads
            if ($field->type === 'file') {
                if ($value instanceof UploadedFile) {
                    // Upload new file
                    $directory = 'uploads/enumerations/' . $this->project->code;
                    $path = $value->store($directory, 'public');
                    $storedValue = '/storage/' . $path;

                    if ($existing) {
                        // Delete old file from storage
                        $oldPath = str_replace('/storage/', '', $existing->value);
                        if (Storage::disk('public')->exists($oldPath)) {
                            Storage::disk('public')->delete($oldPath);
                        }

                        $existing->update(['value' => $storedValue]);
                    } else {
                        EnumerationData::create([
                            'enumeration_id' => $this->id,
                            'project_field_id' => $field->id,
                            'value' => $storedValue,
                        ]);
                    }
                }
                // If not an UploadedFile (e.g. null/empty), preserve existing.
                continue;
            }

            // Handle non-file fields
            if (is_array($value)) {
                $value = array_filter($value); // Remove empty values
                $value = !empty($value) ? json_encode($value) : null;
            }

            if ($existsInPayload && $value !== null && $value !== '') {
                // Update or Create
                if ($existing) {
                    $existing->update(['value' => $value]);
                } else {
                    EnumerationData::create([
                        'enumeration_id' => $this->id,
                        'project_field_id' => $field->id,
                        'value' => $value,
                    ]);
                }
            } else {
                // Value is empty/null or missing from payload -> Delete existing
                if ($existing) {
                    $existing->delete();
                }
            }
        }
    }

    /**
     * Get who performed this enumeration.
     */
    public function getEnumeratorSourceAttribute(): string
    {
        if ($this->staff) {
            return $this->staff->name ?? 'Staff Enumeration';
        }

        if ($this->api_enumeration) {
            return 'API Enumeration';
        }

        if ($this->self_enumerated) {
            return 'Self Enumeration';
        }

        return 'Not Assigned';
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

    /**
     * Create enumeration payments for all active one_off project payments.
     */
    public function createOneOffPayments(): void
    {
        $oneOffPayments = ProjectPayment::where('project_id', $this->project_id)
            ->where('frequency', 'one_off')
            ->active()
            ->validForDate()
            ->get();

        foreach ($oneOffPayments as $projectPayment) {
            $this->enumerationPayments()->create([
                'project_payment_id' => $projectPayment->id,
                'amount_due' => $projectPayment->amount,
                'amount_paid' => 0,
                'status' => 'pending',
                'due_date' => $projectPayment->start_date ?? now(),
            ]);
        }
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
