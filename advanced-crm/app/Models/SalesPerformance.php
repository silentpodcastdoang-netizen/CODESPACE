<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesPerformance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'period_type',
        'period_start',
        'period_end',
        'total_orders',
        'total_revenue',
        'total_profit',
        'new_customers',
        'target_revenue',
        'achievement_percentage',
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
        'total_revenue' => 'decimal:2',
        'total_profit' => 'decimal:2',
        'new_customers' => 'integer',
        'target_revenue' => 'decimal:2',
        'achievement_percentage' => 'decimal:2',
    ];

    /**
     * Period types
     */
    const PERIOD_DAILY = 'daily';
    const PERIOD_WEEKLY = 'weekly';
    const PERIOD_MONTHLY = 'monthly';
    const PERIOD_QUARTERLY = 'quarterly';
    const PERIOD_YEARLY = 'yearly';

    /**
     * Get the user that owns the performance record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the average order value for the period.
     */
    public function getAverageOrderValueAttribute(): float
    {
        if ($this->total_orders === 0) {
            return 0;
        }

        return $this->total_revenue / $this->total_orders;
    }

    /**
     * Get the conversion rate (new customers to total customers).
     */
    public function getConversionRateAttribute(): float
    {
        // This would need to be calculated based on total leads/opportunities
        // For now, returning placeholder value
        return 0;
    }

    /**
     * Get performance status based on achievement percentage.
     */
    public function getPerformanceStatusAttribute(): string
    {
        if ($this->achievement_percentage >= 100) {
            return 'excellent';
        } elseif ($this->achievement_percentage >= 80) {
            return 'good';
        } elseif ($this->achievement_percentage >= 60) {
            return 'average';
        } else {
            return 'below_average';
        }
    }

    /**
     * Calculate and update achievement percentage.
     */
    public function calculateAchievementPercentage()
    {
        if ($this->target_revenue > 0) {
            $this->achievement_percentage = min(100, ($this->total_revenue / $this->target_revenue) * 100);
        } else {
            $this->achievement_percentage = 0;
        }

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
     * Scope a query to only include records for a specific user.
     */
    public function scopeUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get all available period types.
     */
    public static function getPeriodTypes()
    {
        return [
            self::PERIOD_DAILY => 'Daily',
            self::PERIOD_WEEKLY => 'Weekly',
            self::PERIOD_MONTHLY => 'Monthly',
            self::PERIOD_QUARTERLY => 'Quarterly',
            self::PERIOD_YEARLY => 'Yearly',
        ];
    }

    /**
     * Create or update performance record for a user and period.
     */
    public static function updatePerformance($userId, $periodType, $periodStart, $periodEnd, $data = [])
    {
        return static::updateOrCreate(
            [
                'user_id' => $userId,
                'period_type' => $periodType,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
            ],
            $data
        );
    }

    /**
     * Get performance trend for a user over multiple periods.
     */
    public static function getTrend($userId, $periodType, $periods = 6)
    {
        return static::where('user_id', $userId)
            ->where('period_type', $periodType)
            ->orderBy('period_start', 'desc')
            ->limit($periods)
            ->get()
            ->reverse();
    }
}