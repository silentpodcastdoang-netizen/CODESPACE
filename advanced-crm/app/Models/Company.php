<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'tax_id',
        'registration_number',
        'industry',
        'website',
        'description',
        'logo',
        'status',
        'credit_limit',
        'payment_terms',
        'created_by',
        'assigned_to',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'credit_limit' => 'decimal:2',
    ];

    /**
     * Company types
     */
    const TYPE_RETAILER = 'retailer';
    const TYPE_DISTRIBUTOR = 'distributor';
    const TYPE_WHOLESALER = 'wholesaler';
    const TYPE_DIRECT_CUSTOMER = 'direct_customer';

    /**
     * Company statuses
     */
    const STATUS_PROSPECT = 'prospect';
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_BLACKLISTED = 'blacklisted';

    /**
     * Get the addresses for the company.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(CompanyAddress::class);
    }

    /**
     * Get the contacts for the company.
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * Get the primary contact for the company.
     */
    public function primaryContact()
    {
        return $this->hasOne(Contact::class)->where('is_primary', true);
    }

    /**
     * Get the leads for the company.
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * Get the sales orders for the company.
     */
    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    /**
     * Get the user who created the company.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user assigned to the company.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the region for the company.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Get the customer analytics for the company.
     */
    public function analytics(): HasMany
    {
        return $this->hasMany(CustomerAnalytics::class);
    }

    /**
     * Get the primary billing address.
     */
    public function billingAddress()
    {
        return $this->hasOne(CompanyAddress::class)->where('type', 'billing')->where('is_primary', true);
    }

    /**
     * Get the primary shipping address.
     */
    public function shippingAddress()
    {
        return $this->hasOne(CompanyAddress::class)->where('type', 'shipping')->where('is_primary', true);
    }

    /**
     * Get total orders amount.
     */
    public function getTotalOrdersAmount()
    {
        return $this->salesOrders()->where('status', 'delivered')->sum('total_amount');
    }

    /**
     * Get total orders count.
     */
    public function getTotalOrdersCount()
    {
        return $this->salesOrders()->count();
    }

    /**
     * Get average order value.
     */
    public function getAverageOrderValue()
    {
        $totalOrders = $this->getTotalOrdersCount();
        if ($totalOrders === 0) {
            return 0;
        }

        return $this->getTotalOrdersAmount() / $totalOrders;
    }

    /**
     * Get all available company types.
     */
    public static function getTypes()
    {
        return [
            self::TYPE_RETAILER => 'Retailer',
            self::TYPE_DISTRIBUTOR => 'Distributor',
            self::TYPE_WHOLESALER => 'Wholesaler',
            self::TYPE_DIRECT_CUSTOMER => 'Direct Customer',
        ];
    }

    /**
     * Get all available statuses.
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_PROSPECT => 'Prospect',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_BLACKLISTED => 'Blacklisted',
        ];
    }

    /**
     * Scope a query to only include companies with a specific status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include companies of a specific type.
     */
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include companies assigned to a specific user.
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope a query to search companies by name or other fields.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('tax_id', 'like', "%{$search}%")
              ->orWhere('registration_number', 'like', "%{$search}%")
              ->orWhere('website', 'like', "%{$search}%");
        });
    }
}