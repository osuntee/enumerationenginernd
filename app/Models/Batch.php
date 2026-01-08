<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    protected $fillable = [
        'project_id',
        'number',
        'code',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the project that owns the batch.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the codes for the batch.
     */
    public function codes(): HasMany
    {
        return $this->hasMany(Code::class);
    }

    /**
     * Get the count of unused codes in the batch.
     */
    public function unusedCodesCount()
    {
        return $this->codes()->where('is_used', false)->count();
    }
}
