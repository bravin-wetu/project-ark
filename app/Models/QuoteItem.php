<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_id',
        'requisition_item_id',
        'name',
        'description',
        'specifications',
        'quantity',
        'unit',
        'unit_price',
        'total_price',
        'notes',
        'delivery_days',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

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

        // Recalculate quote totals after save
        static::saved(function ($item) {
            $item->quote->recalculateTotals();
        });

        // Recalculate quote totals after delete
        static::deleted(function ($item) {
            $item->quote->recalculateTotals();
        });
    }

    /**
     * Relationships
     */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function requisitionItem(): BelongsTo
    {
        return $this->belongsTo(RequisitionItem::class);
    }

    /**
     * Get the price difference from requisition estimate
     */
    public function getPriceDifferenceAttribute(): ?float
    {
        if (!$this->requisitionItem) {
            return null;
        }

        return $this->unit_price - $this->requisitionItem->estimated_unit_price;
    }

    /**
     * Get price variance percentage
     */
    public function getPriceVariancePercentAttribute(): ?float
    {
        if (!$this->requisitionItem || $this->requisitionItem->estimated_unit_price == 0) {
            return null;
        }

        return (($this->unit_price - $this->requisitionItem->estimated_unit_price) / $this->requisitionItem->estimated_unit_price) * 100;
    }
}
