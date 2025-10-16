<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectField extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'label',
        'type',
        'required',
        'placeholder',
        'help_text',
        'default_value',
        'validation_rules',
        'options',
        'attributes',
        'order',
        'is_active'
    ];

    protected $casts = [
        'required' => 'boolean',
        'options' => 'array',
        'attributes' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function enumerationData()
    {
        return $this->hasMany(EnumerationData::class);
    }

    /**
     * Get validation rules for this field
     */
    public function getValidationRules()
    {
        $rules = [];

        if ($this->required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        switch ($this->type) {
            case 'email':
                $rules[] = 'email';
                break;
            case 'number':
                $rules[] = 'numeric';
                if (isset($this->attributes['min'])) {
                    $rules[] = 'min:' . $this->attributes['min'];
                }
                if (isset($this->attributes['max'])) {
                    $rules[] = 'max:' . $this->attributes['max'];
                }
                break;
            case 'date':
                $rules[] = 'date';
                break;
            case 'time':
                $rules[] = 'date_format:H:i';
                break;
            case 'datetime-local':
                $rules[] = 'date';
                break;
            case 'url':
                $rules[] = 'url';
                break;
            case 'tel':
                $rules[] = 'string';
                break;
            case 'text':
            case 'textarea':
                $rules[] = 'string';
                if (isset($this->attributes['maxlength'])) {
                    $rules[] = 'max:' . $this->attributes['maxlength'];
                }
                break;
            case 'select':
            case 'radio':
                if ($this->options && is_array($this->options)) {
                    $rules[] = 'in:' . implode(',', $this->options);
                }
                break;
            case 'checkboxes':
                $rules[] = 'array';
                if ($this->options && is_array($this->options)) {
                    $rules[] = 'in:' . implode(',', $this->options);
                }
                break;
            case 'checkbox':
                $rules[] = 'boolean';
                break;
            case 'file':
                $rules[] = 'file';
                if (isset($this->attributes['accept'])) {
                    // Convert accept attribute to validation rule
                    $accept = $this->attributes['accept'];
                    if (strpos($accept, 'image/*') !== false) {
                        $rules[] = 'image';
                    }
                }
                if (isset($this->attributes['max_size'])) {
                    $rules[] = 'max:' . $this->attributes['max_size'];
                }
                break;
        }

        // Add custom validation rules if specified
        if ($this->validation_rules) {
            $customRules = explode('|', $this->validation_rules);
            $rules = array_merge($rules, $customRules);
        }

        return implode('|', array_unique($rules));
    }

    /**
     * Get HTML attributes for the input field
     */
    public function getHtmlAttributes()
    {
        $attributes = $this->attributes ?? [];

        if ($this->placeholder) {
            $attributes['placeholder'] = $this->placeholder;
        }

        if ($this->required) {
            $attributes['required'] = true;
        }

        return $attributes;
    }

    /**
     * Scope for active fields
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered fields
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('created_at');
    }
}
