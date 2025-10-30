<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sales_order_id',
        'product_id',
        'quantity',
        'unit_price',
        'discount_percentage',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'total',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->calculateTotals();
        });

        static::saved(function ($item) {
            $item->salesOrder->calculateTotals();
        });
    }

    /**
     * Get the sales order that owns the item.
     */
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    /**
     * Get the product for the order item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate the totals for the order item.
     */
    public function calculateTotals()
    {
        $subtotal = $this->quantity * $this->unit_price;

        // Calculate discount amount
        if ($this->discount_percentage > 0) {
            $this->discount_amount = $subtotal * ($this->discount_percentage / 100);
        } else {
            $this->discount_amount = $this->discount_amount ?? 0;
        }

        // Calculate subtotal after discount
        $subtotalAfterDiscount = $subtotal - $this->discount_amount;

        // Calculate tax amount
        if ($this->tax_rate > 0) {
            $this->tax_amount = $subtotalAfterDiscount * ($this->tax_rate / 100);
        } else {
            $this->tax_amount = $this->tax_amount ?? 0;
        }

        // Calculate total
        $this->total = $subtotalAfterDiscount + $this->tax_amount;
    }

    /**
     * Get the effective unit price after discount.
     */
    public function getEffectiveUnitPriceAttribute(): float
    {
        return $this->unit_price - ($this->discount_amount / $this->quantity);
    }

    /**
     * Get the subtotal before discounts and taxes.
     */
    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Get the subtotal after discount but before tax.
     */
    public function getSubtotalAfterDiscountAttribute(): float
    {
        return $this->getSubtotalAttribute() - $this->discount_amount;
    }

    /**
     * Update the quantity and recalculate totals.
     */
    public function updateQuantity($newQuantity)
    {
        if ($newQuantity > 0) {
            $this->quantity = $newQuantity;
            $this->save();
        }
    }

    /**
     * Check if the product has sufficient stock.
     */
    public function hasSufficientStock(): bool
    {
        if (!$this->product) {
            return false;
        }

        return $this->product->getAvailableStockAttribute() >= $this->quantity;
    }

    /**
     * Reserve stock for this order item.
     */
    public function reserveStock()
    {
        if ($this->product && $this->hasSufficientStock()) {
            // This would need to be implemented based on stock management logic
            // For now, it's a placeholder for future implementation
            return true;
        }

        return false;
    }

    /**
     * Release reserved stock for this order item.
     */
    public function releaseStock()
    {
        // This would need to be implemented based on stock management logic
        // For now, it's a placeholder for future implementation
        return true;
    }
}