<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockBatch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'batch_number',
        'stock_item_id',
        'goods_receipt_id',
        'goods_receipt_item_id',
        'purchase_order_id',
        'supplier_id',
        'batchable_type',
        'batchable_id',
        'budget_line_id',
        'hub_id',
        'storage_location',
        'quantity_received',
        'quantity_available',
        'quantity_reserved',
        'quantity_issued',
        'unit_cost',
        'total_cost',
        'currency',
        'received_date',
        'expiry_date',
        'lot_number',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'received_date' => 'date',
        'expiry_date' => 'date',
        'quantity_received' => 'decimal:2',
        'quantity_available' => 'decimal:2',
        'quantity_reserved' => 'decimal:2',
        'quantity_issued' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_RESERVED = 'reserved';
    const STATUS_DEPLETED = 'depleted';
    const STATUS_EXPIRED = 'expired';
    const STATUS_DAMAGED = 'damaged';

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($batch) {
            if (empty($batch->batch_number)) {
                $batch->batch_number = self::generateBatchNumber();
            }
            if (empty($batch->created_by) && auth()->check()) {
                $batch->created_by = auth()->id();
            }
            // Default quantity_available to quantity_received
            if (!isset($batch->quantity_available)) {
                $batch->quantity_available = $batch->quantity_received;
            }
        });
    }

    /**
     * Generate unique batch number
     */
    public static function generateBatchNumber(): string
    {
        $year = date('Y');
        $prefix = "BAT-{$year}-";
        
        $last = self::withTrashed()
            ->where('batch_number', 'like', $prefix . '%')
            ->orderBy('batch_number', 'desc')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->batch_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create batch from goods receipt item
     */
    public static function createFromReceiptItem(GoodsReceiptItem $item, StockItem $stockItem, array $additionalData = []): self
    {
        $receipt = $item->goodsReceipt;
        $po = $receipt->purchaseOrder;

        return self::create(array_merge([
            'stock_item_id' => $stockItem->id,
            'goods_receipt_id' => $receipt->id,
            'goods_receipt_item_id' => $item->id,
            'purchase_order_id' => $po->id,
            'supplier_id' => $po->supplier_id,
            'batchable_type' => $po->purchaseable_type,
            'batchable_id' => $po->purchaseable_id,
            'budget_line_id' => $po->budget_line_id,
            'hub_id' => $receipt->receiving_hub_id,
            'quantity_received' => $item->quantity_accepted,
            'quantity_available' => $item->quantity_accepted,
            'unit_cost' => $item->unit_price,
            'total_cost' => $item->quantity_accepted * $item->unit_price,
            'received_date' => $receipt->received_date ?? now(),
            'status' => self::STATUS_ACTIVE,
        ], $additionalData));
    }

    // Relationships

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }

    public function batchable(): MorphTo
    {
        return $this->morphTo();
    }

    public function hub(): BelongsTo
    {
        return $this->belongsTo(Hub::class);
    }

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function goodsReceiptItem(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptItem::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function budgetLine(): BelongsTo
    {
        return $this->belongsTo(BudgetLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    // Status helpers

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isDepleted(): bool
    {
        return $this->status === self::STATUS_DEPLETED;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED || 
               ($this->expiry_date && $this->expiry_date->isPast());
    }

    public function hasAvailable(): bool
    {
        return $this->quantity_available > 0;
    }

    // Actions

    public function issue(float $quantity): bool
    {
        if ($quantity > $this->quantity_available) {
            return false;
        }

        $this->quantity_available -= $quantity;
        $this->quantity_issued += $quantity;

        if ($this->quantity_available <= 0) {
            $this->status = self::STATUS_DEPLETED;
        }

        return $this->save();
    }

    public function reserve(float $quantity): bool
    {
        if ($quantity > $this->quantity_available) {
            return false;
        }

        $this->quantity_available -= $quantity;
        $this->quantity_reserved += $quantity;

        if ($this->quantity_available <= 0) {
            $this->status = self::STATUS_RESERVED;
        }

        return $this->save();
    }

    public function releaseReservation(float $quantity): bool
    {
        if ($quantity > $this->quantity_reserved) {
            return false;
        }

        $this->quantity_reserved -= $quantity;
        $this->quantity_available += $quantity;

        if ($this->status === self::STATUS_RESERVED && $this->quantity_available > 0) {
            $this->status = self::STATUS_ACTIVE;
        }

        return $this->save();
    }

    // Helpers

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'bg-green-100 text-green-800',
            self::STATUS_RESERVED => 'bg-yellow-100 text-yellow-800',
            self::STATUS_DEPLETED => 'bg-gray-100 text-gray-800',
            self::STATUS_EXPIRED => 'bg-red-100 text-red-800',
            self::STATUS_DAMAGED => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_RESERVED => 'Reserved',
            self::STATUS_DEPLETED => 'Depleted',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_DAMAGED => 'Damaged',
        ];
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByHub($query, $hubId)
    {
        return $query->where('hub_id', $hubId);
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('batchable_type', Project::class)
                     ->where('batchable_id', $projectId);
    }

    public function scopeForDepartment($query, $departmentBudgetId)
    {
        return $query->where('batchable_type', DepartmentBudget::class)
                     ->where('batchable_id', $departmentBudgetId);
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereNotNull('expiry_date')
                     ->where('expiry_date', '<=', now()->addDays($days))
                     ->where('status', self::STATUS_ACTIVE);
    }
}
