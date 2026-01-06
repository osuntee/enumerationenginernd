<?php
// app/Models/Project.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'name',
        'description',
        'requires_verification',
        'is_active',
        'code',
        'is_published',
        'allow_api',
        'pre_generate',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_published' => 'boolean',
        'requires_verification' => 'boolean',
        'allow_api' => 'boolean',
        'pre_generate' => 'boolean',
    ];

    /**
     * Get the customer that owns this project
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the project fields for this project.
     */
    public function projectFields(): HasMany
    {
        return $this->hasMany(ProjectField::class);
    }

    /**
     * Get the enumerations for this project.
     */
    public function enumerations(): HasMany
    {
        return $this->hasMany(Enumeration::class);
    }

    /**
     * Get all staff assigned to this project (many-to-many)
     */
    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(Staff::class, 'project_staff')
            ->withPivot(['assigned_at', 'removed_at'])
            ->withTimestamps()
            ->wherePivot('removed_at', null);
    }

    /**
     * Get all staff assignments including removed ones
     */
    public function allStaffAssignments(): BelongsToMany
    {
        return $this->belongsToMany(Staff::class, 'project_staff')
            ->withPivot(['role', 'assigned_at', 'removed_at'])
            ->withTimestamps();
    }

    /**
     * Get currently active staff for this project
     */
    public function activeStaff(): BelongsToMany
    {
        return $this->staff()->where('staff.is_active', true);
    }

    /**
     * Scope a query to only include active projects.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for projects by customer
     */
    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Get active project fields ordered by their order column.
     */
    public function getActiveFields()
    {
        return $this->projectFields()->active()->ordered()->get();
    }

    /**
     * Get validation rules for this project's fields.
     */
    public function getValidationRules()
    {
        $rules = [];

        foreach ($this->getActiveFields() as $field) {
            $fieldRules = [];

            if ($field->required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            // Add type-specific rules
            switch ($field->type) {
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'url':
                    $fieldRules[] = 'url';
                    break;
                case 'number':
                case 'range':
                    $fieldRules[] = 'numeric';
                    if (isset($field->attributes['min'])) {
                        $fieldRules[] = 'min:' . $field->attributes['min'];
                    }
                    if (isset($field->attributes['max'])) {
                        $fieldRules[] = 'max:' . $field->attributes['max'];
                    }
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
                case 'time':
                    $fieldRules[] = 'date_format:H:i';
                    break;
                case 'datetime-local':
                    $fieldRules[] = 'date';
                    break;
                case 'file':
                    $fieldRules[] = 'file';
                    if (isset($field->attributes['max_size'])) {
                        $fieldRules[] = 'max:' . $field->attributes['max_size'];
                    }
                    if (isset($field->attributes['accept'])) {
                        $mimes = str_replace('.', '', $field->attributes['accept']);
                        $mimes = str_replace(',', '|', $mimes);
                        $fieldRules[] = 'mimes:' . $mimes;
                    }
                    break;
                case 'checkbox':
                    $fieldRules[] = 'boolean';
                    break;
                case 'checkboxes':
                    $fieldRules[] = 'array';
                    break;
                default:
                    $fieldRules[] = 'string';
                    if (isset($field->attributes['maxlength'])) {
                        $fieldRules[] = 'max:' . $field->attributes['maxlength'];
                    }
                    break;
            }

            // Add custom validation rules if specified
            if ($field->validation_rules) {
                $customRules = explode('|', $field->validation_rules);
                $fieldRules = array_merge($fieldRules, $customRules);
            }

            $rules["data.{$field->name}"] = $fieldRules;
        }

        return $rules;
    }

    public function projectPayments(): HasMany
    {
        return $this->hasMany(ProjectPayment::class);
    }

    public function activeProjectPayments(): HasMany
    {
        return $this->projectPayments()->active();
    }

    public function enumerationPayments(): HasManyThrough
    {
        return $this->hasManyThrough(EnumerationPayment::class, Enumeration::class);
    }

    public function getTotalPaymentsCollected(): float
    {
        return $this->enumerationPayments()->sum('amount_paid');
    }

    public function getTotalPaymentsOutstanding(): float
    {
        return $this->enumerationPayments()
            ->selectRaw('SUM(amount_due - amount_paid) as outstanding')
            ->value('outstanding') ?? 0;
    }
}
