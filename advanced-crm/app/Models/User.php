<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'role',
        'status',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * User roles for the CRM system
     */
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_ADMIN = 'admin';
    const ROLE_SALES_MANAGER = 'sales_manager';
    const ROLE_SALES_REP = 'sales_rep';
    const ROLE_INVENTORY_MANAGER = 'inventory_manager';
    const ROLE_MARKETING = 'marketing';

    /**
     * User statuses
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';

    /**
     * Get the user profile associated with the user.
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get the companies created by the user.
     */
    public function createdCompanies()
    {
        return $this->hasMany(Company::class, 'created_by');
    }

    /**
     * Get the companies assigned to the user.
     */
    public function assignedCompanies()
    {
        return $this->hasMany(Company::class, 'assigned_to');
    }

    /**
     * Get the leads created by the user.
     */
    public function createdLeads()
    {
        return $this->hasMany(Lead::class, 'created_by');
    }

    /**
     * Get the leads assigned to the user.
     */
    public function assignedLeads()
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    /**
     * Get the sales orders created by the user.
     */
    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class, 'sales_rep_id');
    }

    /**
     * Get the sales performance records for the user.
     */
    public function salesPerformance()
    {
        return $this->hasMany(SalesPerformance::class);
    }

    /**
     * Get the team members managed by this user.
     */
    public function teamMembers()
    {
        return $this->hasMany(UserProfile::class, 'manager_id', 'id')
            ->with('user');
    }

    /**
     * Get the manager of this user.
     */
    public function manager()
    {
        return $this->hasOneThrough(
            User::class,
            UserProfile::class,
            'user_id',
            'id',
            'id',
            'manager_id'
        );
    }

    /**
     * Check if user is a sales representative.
     */
    public function isSalesRep()
    {
        return $this->role === self::ROLE_SALES_REP;
    }

    /**
     * Check if user is a sales manager.
     */
    public function isSalesManager()
    {
        return $this->role === self::ROLE_SALES_MANAGER;
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin()
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN]);
    }

    /**
     * Check if user is active.
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Get all available roles.
     */
    public static function getRoles()
    {
        return [
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_SALES_MANAGER => 'Sales Manager',
            self::ROLE_SALES_REP => 'Sales Representative',
            self::ROLE_INVENTORY_MANAGER => 'Inventory Manager',
            self::ROLE_MARKETING => 'Marketing',
        ];
    }

    /**
     * Get all available statuses.
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_SUSPENDED => 'Suspended',
        ];
    }

    /**
     * Update last login timestamp.
     */
    public function updateLastLogin()
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope a query to only include users with a specific role.
     */
    public function scopeRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope a query to only include sales representatives.
     */
    public function scopeSalesReps($query)
    {
        return $query->where('role', self::ROLE_SALES_REP);
    }

    /**
     * Scope a query to only include sales managers.
     */
    public function scopeSalesManagers($query)
    {
        return $query->where('role', self::ROLE_SALES_MANAGER);
    }
}