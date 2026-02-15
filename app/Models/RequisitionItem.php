<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequisitionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'requisition_id',
        'name',
        'description',
        'specifications',
        'quantity',
        'unit',
        'estimated_unit_price',
        'estimated_total',
        'item_type',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'estimated_unit_price' => 'decimal:2',
        'estimated_total' => 'decimal:2',
    ];

    // Item type constants
    const TYPE_GOODS = 'goods';
    const TYPE_SERVICES = 'services';
    const TYPE_WORKS = 'works';

    /**
     * Parent requisition.
     */
    public function requisition(): BelongsTo
    {
        return $this->belongsTo(Requisition::class);
    }

    /**
     * Calculate estimated total.
     */
    public function calculateTotal(): void
    {
        $this->estimated_total = $this->quantity * $this->estimated_unit_price;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-calculate total on save
        static::saving(function ($item) {
            $item->estimated_total = $item->quantity * $item->estimated_unit_price;
        });

        // Update requisition total when item changes
        static::saved(function ($item) {
            $item->requisition->recalculateTotal();
        });

        static::deleted(function ($item) {
            $item->requisition->recalculateTotal();
        });
    }
}
