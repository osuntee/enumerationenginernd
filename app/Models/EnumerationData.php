<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnumerationData extends Model
{
    use HasFactory;

    protected $fillable = [
        'enumeration_id',
        'project_field_id',
        'value'
    ];

    public function enumeration()
    {
        return $this->belongsTo(Enumeration::class);
    }

    public function projectField()
    {
        return $this->belongsTo(ProjectField::class);
    }

    /**
     * Get the cast value based on the field type
     */
    public function getCastValue()
    {
        if (!$this->projectField) {
            return $this->value;
        }

        $fieldType = $this->projectField->type;

        if ($this->value === null || $this->value === '') {
            return null;
        }

        switch ($fieldType) {
            case 'number':
            case 'range':
                return is_numeric($this->value) ? (float) $this->value : $this->value;

            case 'checkbox':
                return (bool) $this->value;

            case 'checkboxes':
                // If it's JSON, decode it, otherwise return as array
                if (is_string($this->value) && (str_starts_with($this->value, '[') || str_starts_with($this->value, '{'))) {
                    return json_decode($this->value, true) ?? [];
                }
                return is_array($this->value) ? $this->value : [$this->value];

            case 'date':
            case 'time':
            case 'datetime-local':
                try {
                    return new \Carbon\Carbon($this->value);
                } catch (\Exception $e) {
                    return $this->value;
                }

            default:
                return $this->value;
        }
    }
}
