<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'quote_item_id',
        'requisition_item_id',
        'name',
        'description',
        'specifications',
        'quantity',
        'received_quantity',
        'unit',
        'unit_price',
        'total_price',
        'status',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PARTIALLY_RECEIVED = 'partially_received';
    const STATUS_RECEIVED = 'received';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-calculate total_price before saving
        static::saving(function ($item) {
            $item->total_price = $item->quantity * $item->unit_price;
        });

        // Recalculate PO totals after save
        static::saved(function ($item) {
            $item->purchaseOrder->recalculateTotals();
        });

        // Recalculate PO totals after delete
        static::deleted(function ($item) {
            $item->purchaseOrder->recalculateTotals();
        });
    }

    /**
     * Relationships
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function quoteItem(): BelongsTo
    {
        return $this->belongsTo(QuoteItem::class);
    }

    public function requisitionItem(): BelongsTo
    {
        return $this->belongsTo(RequisitionItem::class);
    }

    /**
     * Status checks
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isReceived(): bool
    {
        return $this->status === self::STATUS_RECEIVED;
    }

    public function isPartiallyReceived(): bool
    {
        return $this->status === self::STATUS_PARTIALLY_RECEIVED;
    }

    /**
     * Record receipt of items
     */
    public function recordReceipt(float $quantity): void
    {
        $this->received_quantity += $quantity;

        if ($this->received_quantity >= $this->quantity) {
            $this->received_quantity = $this->quantity;
            $this->status = self::STATUS_RECEIVED;
        } else {
            $this->status = self::STATUS_PARTIALLY_RECEIVED;
        }

        $this->save();

        // Update PO status
        $this->purchaseOrder->updateReceiptStatus();
    }

    /**
     * Get remaining quantity to receive
     */
    public function getRemainingQuantityAttribute(): float
    {
        return max(0, $this->quantity - $this->received_quantity);
    }

    /**
     * Get receipt percentage
     */
    public function getReceiptPercentageAttribute(): float
    {
        if ($this->quantity == 0) return 0;
        return round(($this->received_quantity / $this->quantity) * 100, 1);
    }
}
