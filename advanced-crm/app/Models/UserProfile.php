<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'employee_id',
        'department',
        'position',
        'manager_id',
        'region_id',
        'commission_rate',
        'target_sales',
        'bio',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'commission_rate' => 'decimal:2',
        'target_sales' => 'decimal:2',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the manager that oversees the user.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the region that the user belongs to.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Get the team members that report to this user's manager.
     */
    public function teamMembers()
    {
        return $this->hasMany(UserProfile::class, 'manager_id', 'manager_id')
            ->where('user_id', '!=', $this->user_id)
            ->with('user');
    }

    /**
     * Calculate sales achievement percentage.
     */
    public function getAchievementPercentage($actualSales)
    {
        if ($this->target_sales <= 0) {
            return 0;
        }

        return min(100, ($actualSales / $this->target_sales) * 100);
    }

    /**
     * Scope a query to only include profiles in a specific department.
     */
    public function scopeDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Scope a query to only include profiles in a specific region.
     */
    public function scopeRegion($query, $regionId)
    {
        return $query->where('region_id', $regionId);
    }

    /**
     * Scope a query to only include profiles that report to a specific manager.
     */
    public function scopeReportsTo($query, $managerId)
    {
        return $query->where('manager_id', $managerId);
    }
}