<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'adjustment_number',
        'stock_batch_id',
        'type',
        'quantity_change',
        'quantity_before',
        'quantity_after',
        'reason',
        'notes',
        'approved_by',
        'approved_at',
        'created_by',
    ];

    protected $casts = [
        'quantity_change' => 'decimal:2',
        'quantity_before' => 'decimal:2',
        'quantity_after' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    // Type constants
    const TYPE_CORRECTION = 'correction';
    const TYPE_DAMAGE = 'damage';
    const TYPE_EXPIRED = 'expired';
    const TYPE_LOSS = 'loss';
    const TYPE_RETURN = 'return';
    const TYPE_TRANSFER_IN = 'transfer_in';
    const TYPE_TRANSFER_OUT = 'transfer_out';

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($adjustment) {
            if (empty($adjustment->adjustment_number)) {
                $adjustment->adjustment_number = self::generateAdjustmentNumber();
            }
            if (empty($adjustment->created_by) && auth()->check()) {
                $adjustment->created_by = auth()->id();
            }
        });

        static::created(function ($adjustment) {
            // Apply adjustment to batch
            $batch = $adjustment->stockBatch;
            $batch->quantity_available = $adjustment->quantity_after;
            
            // Update status if needed
            if ($batch->quantity_available <= 0) {
                $batch->status = StockBatch::STATUS_DEPLETED;
            } elseif ($batch->status === StockBatch::STATUS_DEPLETED) {
                $batch->status = StockBatch::STATUS_ACTIVE;
            }
            
            $batch->save();
        });
    }

    /**
     * Generate unique adjustment number
     */
    public static function generateAdjustmentNumber(): string
    {
        $year = date('Y');
        $prefix = "ADJ-{$year}-";
        
        $last = self::where('adjustment_number', 'like', $prefix . '%')
            ->orderBy('adjustment_number', 'desc')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->adjustment_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // Relationships

    public function stockBatch(): BelongsTo
    {
        return $this->belongsTo(StockBatch::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Helpers

    public function isIncrease(): bool
    {
        return $this->quantity_change > 0;
    }

    public function isDecrease(): bool
    {
        return $this->quantity_change < 0;
    }

    public function getTypeBadgeClass(): string
    {
        return match($this->type) {
            self::TYPE_CORRECTION => 'bg-blue-100 text-blue-800',
            self::TYPE_DAMAGE => 'bg-orange-100 text-orange-800',
            self::TYPE_EXPIRED => 'bg-red-100 text-red-800',
            self::TYPE_LOSS => 'bg-red-100 text-red-800',
            self::TYPE_RETURN => 'bg-green-100 text-green-800',
            self::TYPE_TRANSFER_IN => 'bg-green-100 text-green-800',
            self::TYPE_TRANSFER_OUT => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_CORRECTION => 'Correction',
            self::TYPE_DAMAGE => 'Damage',
            self::TYPE_EXPIRED => 'Expired',
            self::TYPE_LOSS => 'Loss',
            self::TYPE_RETURN => 'Return',
            self::TYPE_TRANSFER_IN => 'Transfer In',
            self::TYPE_TRANSFER_OUT => 'Transfer Out',
        ];
    }
}
