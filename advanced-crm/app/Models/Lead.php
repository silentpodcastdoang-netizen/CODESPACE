<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'company_id',
        'contact_id',
        'source',
        'status',
        'priority',
        'estimated_value',
        'probability',
        'expected_close_date',
        'assigned_to',
        'created_by',
        'closed_date',
        'closed_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'estimated_value' => 'decimal:2',
        'probability' => 'integer',
        'expected_close_date' => 'date',
        'closed_date' => 'date',
    ];

    /**
     * Lead sources
     */
    const SOURCE_WEBSITE = 'website';
    const SOURCE_REFERRAL = 'referral';
    const SOURCE_COLD_CALL = 'cold_call';
    const SOURCE_EMAIL = 'email';
    const SOURCE_SOCIAL_MEDIA = 'social_media';
    const SOURCE_TRADE_SHOW = 'trade_show';
    const SOURCE_EXISTING_CUSTOMER = 'existing_customer';
    const SOURCE_OTHER = 'other';

    /**
     * Lead statuses
     */
    const STATUS_NEW = 'new';
    const STATUS_CONTACTED = 'contacted';
    const STATUS_QUALIFIED = 'qualified';
    const STATUS_PROPOSAL = 'proposal';
    const STATUS_NEGOTIATION = 'negotiation';
    const STATUS_CLOSED_WON = 'closed_won';
    const STATUS_CLOSED_LOST = 'closed_lost';

    /**
     * Lead priorities
     */
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URENT = 'urgent';

    /**
     * Get the company associated with the lead.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the contact associated with the lead.
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Get the user assigned to the lead.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created the lead.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the sales orders converted from this lead.
     */
    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    /**
     * Get the activities associated with the lead.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class);
    }

    /**
     * Check if lead is closed (won or lost).
     */
    public function isClosed(): bool
    {
        return in_array($this->status, [self::STATUS_CLOSED_WON, self::STATUS_CLOSED_LOST]);
    }

    /**
     * Check if lead is won.
     */
    public function isWon(): bool
    {
        return $this->status === self::STATUS_CLOSED_WON;
    }

    /**
     * Check if lead is lost.
     */
    public function isLost(): bool
    {
        return $this->status === self::STATUS_CLOSED_LOST;
    }

    /**
     * Get the age of the lead in days.
     */
    public function getAgeInDaysAttribute(): int
    {
        return $this->created_at->diffInDays(now());
    }

    /**
     * Get the days until expected close date.
     */
    public function getDaysUntilCloseAttribute(): ?int
    {
        if (!$this->expected_close_date) {
            return null;
        }

        return now()->diffInDays($this->expected_close_date, false);
    }

    /**
     * Check if lead is overdue (past expected close date and not closed).
     */
    public function isOverdue(): bool
    {
        if ($this->isClosed() || !$this->expected_close_date) {
            return false;
        }

        return $this->expected_close_date->isPast();
    }

    /**
     * Get lead score based on various factors.
     */
    public function getLeadScoreAttribute(): int
    {
        $score = 0;

        // Status-based scoring
        $statusScores = [
            self::STATUS_NEW => 10,
            self::STATUS_CONTACTED => 20,
            self::STATUS_QUALIFIED => 40,
            self::STATUS_PROPOSAL => 60,
            self::STATUS_NEGOTIATION => 80,
        ];

        $score += $statusScores[$this->status] ?? 0;

        // Priority-based scoring
        $priorityScores = [
            self::PRIORITY_LOW => 5,
            self::PRIORITY_MEDIUM => 10,
            self::PRIORITY_HIGH => 20,
            self::PRIORITY_URENT => 30,
        ];

        $score += $priorityScores[$this->priority] ?? 0;

        // Value-based scoring
        if ($this->estimated_value) {
            $score += min(30, floor($this->estimated_value / 1000));
        }

        // Probability scoring
        $score += $this->probability * 0.3;

        return (int) $score;
    }

    /**
     * Get all available lead sources.
     */
    public static function getSources()
    {
        return [
            self::SOURCE_WEBSITE => 'Website',
            self::SOURCE_REFERRAL => 'Referral',
            self::SOURCE_COLD_CALL => 'Cold Call',
            self::SOURCE_EMAIL => 'Email',
            self::SOURCE_SOCIAL_MEDIA => 'Social Media',
            self::SOURCE_TRADE_SHOW => 'Trade Show',
            self::SOURCE_EXISTING_CUSTOMER => 'Existing Customer',
            self::SOURCE_OTHER => 'Other',
        ];
    }

    /**
     * Get all available statuses.
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_CONTACTED => 'Contacted',
            self::STATUS_QUALIFIED => 'Qualified',
            self::STATUS_PROPOSAL => 'Proposal',
            self::STATUS_NEGOTIATION => 'Negotiation',
            self::STATUS_CLOSED_WON => 'Closed Won',
            self::STATUS_CLOSED_LOST => 'Closed Lost',
        ];
    }

    /**
     * Get all available priorities.
     */
    public static function getPriorities()
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_URGENT => 'Urgent',
        ];
    }

    /**
     * Scope a query to only include leads with a specific status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include leads with a specific priority.
     */
    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope a query to only include leads assigned to a specific user.
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope a query to only include open leads (not closed).
     */
    public function scopeOpen($query)
    {
        return $query->whereNotIn('status', [self::STATUS_CLOSED_WON, self::STATUS_CLOSED_LOST]);
    }

    /**
     * Scope a query to only include overdue leads.
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotNull('expected_close_date')
            ->whereDate('expected_close_date', '<', now())
            ->open();
    }

    /**
     * Scope a query to only include leads due to close soon.
     */
    public function scopeClosingSoon($query, $days = 7)
    {
        return $query->whereNotNull('expected_close_date')
            ->whereDate('expected_close_date', '<=', now()->addDays($days))
            ->whereDate('expected_close_date', '>=', now())
            ->open();
    }

    /**
     * Scope a query to search leads by title, description, or company name.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhereHas('company', function ($subQuery) use ($search) {
                  $subQuery->where('name', 'like', "%{$search}%");
              })
              ->orWhereHas('contact', function ($subQuery) use ($search) {
                  $subQuery->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Close the lead as won.
     */
    public function closeWon($reason = null)
    {
        $this->update([
            'status' => self::STATUS_CLOSED_WON,
            'closed_date' => now(),
            'closed_reason' => $reason,
        ]);
    }

    /**
     * Close the lead as lost.
     */
    public function closeLost($reason)
    {
        $this->update([
            'status' => self::STATUS_CLOSED_LOST,
            'closed_date' => now(),
            'closed_reason' => $reason,
        ]);
    }
}