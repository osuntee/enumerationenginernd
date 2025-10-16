<?php
// app/Models/Staff.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'staff';

    const STAFF_TYPE_SUPER_ADMIN = 'super_admin';
    const STAFF_TYPE_ADMIN = 'admin';
    const STAFF_TYPE_USER = 'user';

    protected $fillable = [
        'customer_id',
        'name',
        'email',
        'password',
        'phone',
        'staff_type',
        'is_active'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the customer this staff belongs to
     * Note: super_admin staff will have null customer_id
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get all projects this staff is assigned to (many-to-many)
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_staff')
            ->withTimestamps();
    }

    /**
     * Get currently active projects for this staff
     */
    public function activeProjects(): BelongsToMany
    {
        return $this->projects()->where('projects.is_active', true);
    }

    /**
     * Get the enumerations conducted by this staff member.
     */
    public function enumerations(): HasMany
    {
        return $this->hasMany(Enumeration::class);
    }

    /**
     * Get the count of enumerations conducted by this staff member.
     */
    public function getEnumerationsCountAttribute(): int
    {
        return $this->enumerations()->count();
    }

    /**
     * Get the count of enumerations for this staff member on a given project.
     */
    public function countEnumerationsForProject(int $projectId): int
    {
        return $this->enumerations()
            ->where('project_id', $projectId)
            ->count();
    }

    /**
     * Scope a query to only include active staff.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include suspended staff.
     */
    public function scopeSuspended($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope for super admin staff
     */
    public function scopeSuperAdmin($query)
    {
        return $query->where('staff_type', self::STAFF_TYPE_SUPER_ADMIN);
    }

    /**
     * Scope for admin staff
     */
    public function scopeAdmin($query)
    {
        return $query->where('staff_type', self::STAFF_TYPE_ADMIN);
    }

    /**
     * Scope for user staff
     */
    public function scopeUser($query)
    {
        return $query->where('staff_type', self::STAFF_TYPE_USER);
    }

    /**
     * Scope for staff by customer
     */
    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Get the staff member's full contact information.
     */
    public function getFullContactAttribute()
    {
        $contact = $this->name;
        if ($this->email) {
            $contact .= ' (' . $this->email . ')';
        }
        return $contact;
    }

    /**
     * Check if the staff member is active.
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * Check if the staff member is suspended.
     */
    public function isSuspended()
    {
        return !$this->is_active;
    }

    /**
     * Check if staff is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->staff_type === self::STAFF_TYPE_SUPER_ADMIN;
    }

    /**
     * Check if staff is admin
     */
    public function isAdmin(): bool
    {
        return $this->staff_type === self::STAFF_TYPE_ADMIN;
    }

    /**
     * Check if staff is user
     */
    public function isUser(): bool
    {
        return $this->staff_type === self::STAFF_TYPE_USER;
    }

    /**
     * Get available staff types
     */
    public static function getStaffTypes(): array
    {
        return [
            self::STAFF_TYPE_SUPER_ADMIN => 'Super Admin',
            self::STAFF_TYPE_ADMIN => 'Admin',
            self::STAFF_TYPE_USER => 'User',
        ];
    }

    /**
     * Set the password attribute with hashing.
     * Only needed if not using Laravel 10+ auto-hashing
     */
    public function setPasswordAttribute($password)
    {
        if ($password) {
            $this->attributes['password'] = Hash::make($password);
        }
    }

    /**
     * Get all notifications for the staff member.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get all activities for the staff member.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }
}
