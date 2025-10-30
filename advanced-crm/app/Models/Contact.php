<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'position',
        'department',
        'is_primary',
        'is_decision_maker',
        'date_of_birth',
        'notes',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_primary' => 'boolean',
        'is_decision_maker' => 'boolean',
        'date_of_birth' => 'date',
    ];

    /**
     * Get the full name attribute.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the company that owns the contact.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who created the contact.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the leads associated with the contact.
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * Get the sales orders associated with the contact.
     */
    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    /**
     * Get the primary phone number.
     */
    public function getPrimaryPhoneAttribute(): ?string
    {
        return $this->mobile ?: $this->phone;
    }

    /**
     * Scope a query to only include primary contacts.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope a query to only include decision makers.
     */
    public function scopeDecisionMakers($query)
    {
        return $query->where('is_decision_maker', true);
    }

    /**
     * Scope a query to only include contacts for a specific company.
     */
    public function scopeCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to search contacts by name, email, or phone.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('mobile', 'like', "%{$search}%")
              ->orWhere('position', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to filter by department.
     */
    public function scopeDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Get upcoming birthdays (within next 30 days).
     */
    public static function getUpcomingBirthdays()
    {
        return static::whereNotNull('date_of_birth')
            ->whereRaw('MONTH(date_of_birth) BETWEEN ? AND ?', [now()->month, now()->addDays(30)->month])
            ->whereRaw('DAY(date_of_birth) BETWEEN ? AND ?', [now()->day, now()->addDays(30)->day])
            ->orderByRaw('MONTH(date_of_birth), DAY(date_of_birth)')
            ->with('company')
            ->get();
    }
}