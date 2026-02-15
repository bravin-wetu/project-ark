<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiptItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'goods_receipt_id',
        'purchase_order_item_id',
        'expected_quantity',
        'received_quantity',
        'accepted_quantity',
        'rejected_quantity',
        'condition',
        'inspected',
        'inspected_by',
        'inspected_at',
        'notes',
        'rejection_reason',
        'storage_location',
        'batch_number',
        'expiry_date',
    ];

    protected $casts = [
        'expected_quantity' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'accepted_quantity' => 'decimal:2',
        'rejected_quantity' => 'decimal:2',
        'inspected' => 'boolean',
        'inspected_at' => 'datetime',
        'expiry_date' => 'date',
    ];

    /**
     * Condition constants
     */
    const CONDITION_EXCELLENT = 'excellent';
    const CONDITION_GOOD = 'good';
    const CONDITION_ACCEPTABLE = 'acceptable';
    const CONDITION_DAMAGED = 'damaged';
    const CONDITION_REJECTED = 'rejected';

    /**
     * Relationships
     */
    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    /**
     * Get the item name via PO item
     */
    public function getNameAttribute(): string
    {
        return $this->purchaseOrderItem->name ?? '';
    }

    /**
     * Get the unit via PO item
     */
    public function getUnitAttribute(): string
    {
        return $this->purchaseOrderItem->unit ?? '';
    }

    /**
     * Get unit price via PO item
     */
    public function getUnitPriceAttribute(): float
    {
        return $this->purchaseOrderItem->unit_price ?? 0;
    }

    /**
     * Calculate accepted value
     */
    public function getAcceptedValueAttribute(): float
    {
        return $this->accepted_quantity * $this->unit_price;
    }

    /**
     * Check if fully received
     */
    public function isFullyReceived(): bool
    {
        return $this->accepted_quantity >= $this->expected_quantity;
    }

    /**
     * Get receipt percentage
     */
    public function getReceiptPercentageAttribute(): float
    {
        if ($this->expected_quantity == 0) return 0;
        return round(($this->accepted_quantity / $this->expected_quantity) * 100, 1);
    }

    /**
     * Get variance (expected - accepted)
     */
    public function getVarianceAttribute(): float
    {
        return $this->expected_quantity - $this->accepted_quantity;
    }

    /**
     * Mark as inspected
     */
    public function markInspected(string $inspectedBy = null): void
    {
        $this->inspected = true;
        $this->inspected_by = $inspectedBy ?? auth()->user()?->name;
        $this->inspected_at = now();
        $this->save();
    }

    /**
     * Condition badge color
     */
    public function getConditionColorAttribute(): string
    {
        return match($this->condition) {
            self::CONDITION_EXCELLENT => 'green',
            self::CONDITION_GOOD => 'blue',
            self::CONDITION_ACCEPTABLE => 'yellow',
            self::CONDITION_DAMAGED => 'orange',
            self::CONDITION_REJECTED => 'red',
            default => 'gray',
        };
    }
}
