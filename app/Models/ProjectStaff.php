<?php
// app/Models/ProjectStaff.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectStaff extends Model
{
    use HasFactory;

    protected $table = 'project_staff';

    protected $fillable = [
        'project_id',
        'staff_id',
        'assigned_at',
        'removed_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'removed_at' => 'datetime',
    ];

    /**
     * Get the project
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the staff
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Scope for active assignments
     */
    public function scopeActive($query)
    {
        return $query->whereNull('removed_at');
    }

    /**
     * Scope for removed assignments
     */
    public function scopeRemoved($query)
    {
        return $query->whereNotNull('removed_at');
    }

    /**
     * Check if assignment is active
     */
    public function isActive(): bool
    {
        return is_null($this->removed_at);
    }

    /**
     * Mark assignment as removed
     */
    public function markAsRemoved(): void
    {
        $this->update(['removed_at' => now()]);
    }

    /**
     * Reactivate assignment
     */
    public function reactivate(): void
    {
        $this->update(['removed_at' => null]);
    }

    /**
     * Boot method to handle validation constraint
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($projectStaff) {
            // Ensure staff and project belong to the same customer
            $staff = Staff::find($projectStaff->staff_id);
            $project = Project::find($projectStaff->project_id);

            if ($staff && $project && !$staff->isSuperAdmin()) {
                if ($staff->customer_id !== $project->customer_id) {
                    throw new \InvalidArgumentException(
                        'Staff can only be assigned to projects under the same customer.'
                    );
                }
            }
        });
    }
}
