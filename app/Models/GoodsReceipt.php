<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class GoodsReceipt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'receipt_number',
        'purchase_order_id',
        'delivery_note_number',
        'invoice_number',
        'received_by',
        'received_at',
        'hub_id',
        'receiving_location',
        'overall_condition',
        'status',
        'notes',
        'discrepancy_notes',
        'created_by',
        'confirmed_by',
        'confirmed_at',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PARTIAL = 'partial';
    const STATUS_COMPLETE = 'complete';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Condition constants
     */
    const CONDITION_EXCELLENT = 'excellent';
    const CONDITION_GOOD = 'good';
    const CONDITION_ACCEPTABLE = 'acceptable';
    const CONDITION_DAMAGED = 'damaged';
    const CONDITION_REJECTED = 'rejected';

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($receipt) {
            if (empty($receipt->receipt_number)) {
                $receipt->receipt_number = self::generateNumber();
            }
            if (empty($receipt->created_by)) {
                $receipt->created_by = Auth::id();
            }
            if (empty($receipt->received_at)) {
                $receipt->received_at = now();
            }
        });
    }

    /**
     * Generate unique receipt number
     */
    public static function generateNumber(): string
    {
        $year = date('Y');
        $prefix = "GR-{$year}-";
        
        $lastReceipt = self::where('receipt_number', 'LIKE', "{$prefix}%")
            ->orderBy('receipt_number', 'desc')
            ->first();

        if ($lastReceipt) {
            $lastNumber = (int) substr($lastReceipt->receipt_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $newNumber;
    }

    /**
     * Relationships
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function hub(): BelongsTo
    {
        return $this->belongsTo(Hub::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Status checks
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isConfirmed(): bool
    {
        return in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_PARTIAL, self::STATUS_COMPLETE]);
    }

    public function isComplete(): bool
    {
        return $this->status === self::STATUS_COMPLETE;
    }

    /**
     * Confirm the receipt
     */
    public function confirm(): void
    {
        if (!$this->isDraft()) {
            throw new \Exception('Only draft receipts can be confirmed.');
        }

        $this->confirmed_by = Auth::id();
        $this->confirmed_at = now();

        // Update PO item received quantities
        foreach ($this->items as $receiptItem) {
            $poItem = $receiptItem->purchaseOrderItem;
            $poItem->recordReceipt($receiptItem->accepted_quantity);
        }

        // Determine status based on whether all items fully received
        $po = $this->purchaseOrder;
        $allReceived = $po->items->every(fn ($item) => $item->isReceived());
        
        $this->status = $allReceived ? self::STATUS_COMPLETE : self::STATUS_CONFIRMED;
        $this->save();

        // Update PO receipt status
        $po->updateReceiptStatus();
    }

    /**
     * Cancel the receipt
     */
    public function cancel(): void
    {
        if (!$this->isDraft()) {
            throw new \Exception('Only draft receipts can be cancelled.');
        }

        $this->status = self::STATUS_CANCELLED;
        $this->save();
    }

    /**
     * Create receipt from PO
     */
    public static function createFromPurchaseOrder(PurchaseOrder $po, array $data = []): self
    {
        $receipt = self::create(array_merge([
            'purchase_order_id' => $po->id,
            'hub_id' => $po->purchaseable->hub_id ?? null,
            'received_at' => now(),
        ], $data));

        // Pre-populate items from PO
        foreach ($po->items as $poItem) {
            if ($poItem->remaining_quantity > 0) {
                $receipt->items()->create([
                    'purchase_order_item_id' => $poItem->id,
                    'expected_quantity' => $poItem->remaining_quantity,
                    'received_quantity' => 0,
                    'accepted_quantity' => 0,
                    'rejected_quantity' => 0,
                ]);
            }
        }

        return $receipt;
    }

    /**
     * Get total received value
     */
    public function getTotalReceivedValueAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->accepted_quantity * $item->purchaseOrderItem->unit_price;
        });
    }

    /**
     * Condition badge color
     */
    public function getConditionColorAttribute(): string
    {
        return match($this->overall_condition) {
            self::CONDITION_EXCELLENT => 'green',
            self::CONDITION_GOOD => 'blue',
            self::CONDITION_ACCEPTABLE => 'yellow',
            self::CONDITION_DAMAGED => 'orange',
            self::CONDITION_REJECTED => 'red',
            default => 'gray',
        };
    }

    /**
     * Status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_CONFIRMED => 'blue',
            self::STATUS_PARTIAL => 'yellow',
            self::STATUS_COMPLETE => 'green',
            self::STATUS_CANCELLED => 'red',
            default => 'gray',
        };
    }
}
