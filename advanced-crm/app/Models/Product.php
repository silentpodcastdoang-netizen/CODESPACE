<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sku',
        'barcode',
        'name',
        'slug',
        'description',
        'short_description',
        'category_id',
        'brand',
        'unit',
        'weight',
        'dimensions',
        'cost_price',
        'selling_price',
        'mrp',
        'reorder_level',
        'max_stock',
        'is_active',
        'is_featured',
        'image',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'mrp' => 'decimal:2',
        'weight' => 'decimal:3',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the stock levels for the product.
     */
    public function stockLevels(): HasMany
    {
        return $this->hasMany(StockLevel::class);
    }

    /**
     * Get the sales order items for the product.
     */
    public function salesOrderItems(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    /**
     * Get total stock across all warehouses.
     */
    public function getTotalStockAttribute(): int
    {
        return $this->stockLevels()->sum('quantity');
    }

    /**
     * Get available stock across all warehouses.
     */
    public function getAvailableStockAttribute(): int
    {
        return $this->stockLevels()->sum('available_quantity');
    }

    /**
     * Get reserved stock across all warehouses.
     */
    public function getReservedStockAttribute(): int
    {
        return $this->stockLevels()->sum('reserved_quantity');
    }

    /**
     * Check if product is low in stock.
     */
    public function isLowStock(): bool
    {
        return $this->getTotalStockAttribute() <= $this->reorder_level;
    }

    /**
     * Check if product is out of stock.
     */
    public function isOutOfStock(): bool
    {
        return $this->getAvailableStockAttribute() <= 0;
    }

    /**
     * Get profit margin percentage.
     */
    public function getProfitMarginAttribute(): float
    {
        if ($this->selling_price <= 0) {
            return 0;
        }

        return round((($this->selling_price - $this->cost_price) / $this->selling_price) * 100, 2);
    }

    /**
     * Get total quantity sold.
     */
    public function getTotalSoldAttribute(): int
    {
        return $this->salesOrderItems()
            ->whereHas('salesOrder', function ($query) {
                $query->where('status', 'delivered');
            })
            ->sum('quantity');
    }

    /**
     * Get total revenue from sales.
     */
    public function getTotalRevenueAttribute(): float
    {
        return $this->salesOrderItems()
            ->whereHas('salesOrder', function ($query) {
                $query->where('status', 'delivered');
            })
            ->sum('total');
    }

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include featured products.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include products in a specific category.
     */
    public function scopeCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope a query to only include low stock products.
     */
    public function scopeLowStock($query)
    {
        return $query->whereHas('stockLevels', function ($subQuery) {
            $subQuery->havingRaw('SUM(quantity) <= products.reorder_level');
        });
    }

    /**
     * Scope a query to only include out of stock products.
     */
    public function scopeOutOfStock($query)
    {
        return $query->whereHas('stockLevels', function ($subQuery) {
            $subQuery->havingRaw('SUM(available_quantity) <= 0');
        });
    }

    /**
     * Scope a query to search products by name, SKU, or description.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('sku', 'like', "%{$search}%")
              ->orWhere('barcode', 'like', "%{$search}%")
              ->orWhere('brand', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Get formatted price.
     */
    public function getFormattedSellingPriceAttribute(): string
    {
        return number_format($this->selling_price, 2);
    }

    /**
     * Get formatted cost price.
     */
    public function getFormattedCostPriceAttribute(): string
    {
        return number_format($this->cost_price, 2);
    }
}