<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAnalytics extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'period_type',
        'period_start',
        'period_end',
        'total_orders',
        'total_spent',
        'average_order_value',
        'last_order_date',
        'loyalty_score',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_orders' => 'integer',
        'total_spent' => 'decimal:2',
        'average_order_value' => 'decimal:2',
        'last_order_date' => 'date',
        'loyalty_score' => 'integer',
    ];

    /**
     * Period types
     */
    const PERIOD_MONTHLY = 'monthly';
    const PERIOD_QUARTERLY = 'quarterly';
    const PERIOD_YEARLY = 'yearly';

    /**
     * Get the company that owns the analytics record.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get customer segment based on spending.
     */
    public function getCustomerSegmentAttribute(): string
    {
        if ($this->total_spent >= 100000) {
            return 'enterprise';
        } elseif ($this->total_spent >= 50000) {
            return 'premium';
        } elseif ($this->total_spent >= 10000) {
            return 'standard';
        } else {
            return 'basic';
        }
    }

    /**
     * Get customer activity level.
     */
    public function getActivityLevelAttribute(): string
    {
        if (!$this->last_order_date) {
            return 'inactive';
        }

        $daysSinceLastOrder = $this->last_order_date->diffInDays(now());

        if ($daysSinceLastOrder <= 30) {
            return 'active';
        } elseif ($daysSinceLastOrder <= 90) {
            return 'at_risk';
        } else {
            return 'inactive';
        }
    }

    /**
     * Calculate and update average order value.
     */
    public function calculateAverageOrderValue()
    {
        if ($this->total_orders > 0) {
            $this->average_order_value = $this->total_spent / $this->total_orders;
        } else {
            $this->average_order_value = 0;
        }

        $this->save();
    }

    /**
     * Calculate and update loyalty score based on various factors.
     */
    public function calculateLoyaltyScore()
    {
        $score = 0;

        // Order frequency (max 40 points)
        if ($this->total_orders >= 50) {
            $score += 40;
        } elseif ($this->total_orders >= 20) {
            $score += 30;
        } elseif ($this->total_orders >= 10) {
            $score += 20;
        } elseif ($this->total_orders >= 5) {
            $score += 10;
        }

        // Total spending (max 35 points)
        if ($this->total_spent >= 100000) {
            $score += 35;
        } elseif ($this->total_spent >= 50000) {
            $score += 25;
        } elseif ($this->total_spent >= 20000) {
            $score += 15;
        } elseif ($this->total_spent >= 5000) {
            $score += 5;
        }

        // Recency (max 25 points)
        if ($this->last_order_date) {
            $daysSinceLastOrder = $this->last_order_date->diffInDays(now());

            if ($daysSinceLastOrder <= 7) {
                $score += 25;
            } elseif ($daysSinceLastOrder <= 30) {
                $score += 20;
            } elseif ($daysSinceLastOrder <= 90) {
                $score += 10;
            } elseif ($daysSinceLastOrder <= 180) {
                $score += 5;
            }
        }

        $this->loyalty_score = min(100, $score);
        $this->save();
    }

    /**
     * Scope a query to only include records for a specific period type.
     */
    public function scopePeriodType($query, $periodType)
    {
        return $query->where('period_type', $periodType);
    }

    /**
     * Scope a query to only include records within a date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->where('period_start', '>=', $startDate)
                    ->where('period_end', '<=', $endDate);
    }

    /**
     * Scope a query to only include records for a specific company.
     */
    public function scopeCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to only include active customers.
     */
    public function scopeActive($query)
    {
        return $query->whereHas('company', function ($subQuery) {
            $subQuery->where('status', Company::STATUS_ACTIVE);
        });
    }

    /**
     * Scope a query to filter by customer segment.
     */
    public function scopeSegment($query, $segment)
    {
        return $query->whereRaw("
            CASE
                WHEN total_spent >= 100000 THEN 'enterprise'
                WHEN total_spent >= 50000 THEN 'premium'
                WHEN total_spent >= 10000 THEN 'standard'
                ELSE 'basic'
            END = ?
        ", [$segment]);
    }

    /**
     * Scope a query to filter by activity level.
     */
    public function scopeActivityLevel($query, $activityLevel)
    {
        if ($activityLevel === 'active') {
            $query->where('last_order_date', '>=', now()->subDays(30));
        } elseif ($activityLevel === 'at_risk') {
            $query->where('last_order_date', '>=', now()->subDays(90))
                  ->where('last_order_date', '<', now()->subDays(30));
        } elseif ($activityLevel === 'inactive') {
            $query->where(function ($subQuery) {
                $subQuery->whereNull('last_order_date')
                        ->orWhere('last_order_date', '<', now()->subDays(90));
            });
        }
    }

    /**
     * Get all available period types.
     */
    public static function getPeriodTypes()
    {
        return [
            self::PERIOD_MONTHLY => 'Monthly',
            self::PERIOD_QUARTERLY => 'Quarterly',
            self::PERIOD_YEARLY => 'Yearly',
        ];
    }

    /**
     * Get all available customer segments.
     */
    public static function getSegments()
    {
        return [
            'enterprise' => 'Enterprise',
            'premium' => 'Premium',
            'standard' => 'Standard',
            'basic' => 'Basic',
        ];
    }

    /**
     * Get all available activity levels.
     */
    public static function getActivityLevels()
    {
        return [
            'active' => 'Active',
            'at_risk' => 'At Risk',
            'inactive' => 'Inactive',
        ];
    }

    /**
     * Create or update analytics record for a company and period.
     */
    public static function updateAnalytics($companyId, $periodType, $periodStart, $periodEnd, $data = [])
    {
        return static::updateOrCreate(
            [
                'company_id' => $companyId,
                'period_type' => $periodType,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
            ],
            $data
        );
    }
}