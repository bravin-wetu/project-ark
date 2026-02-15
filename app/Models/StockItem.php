<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'category',
        'subcategory',
        'unit',
        'reorder_level',
        'reorder_quantity',
        'unit_cost',
        'currency',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'reorder_level' => 'decimal:2',
        'reorder_quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Category constants
    const CATEGORY_OFFICE_SUPPLIES = 'Office Supplies';
    const CATEGORY_CLEANING = 'Cleaning';
    const CATEGORY_WASH = 'WASH';
    const CATEGORY_MEDICAL = 'Medical';
    const CATEGORY_FOOD = 'Food';
    const CATEGORY_FUEL = 'Fuel';
    const CATEGORY_STATIONERY = 'Stationery';
    const CATEGORY_OTHER = 'Other';

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->sku)) {
                $item->sku = self::generateSku();
            }
            if (empty($item->created_by) && auth()->check()) {
                $item->created_by = auth()->id();
            }
        });
    }

    /**
     * Generate unique SKU
     */
    public static function generateSku(): string
    {
        $year = date('Y');
        $prefix = "STK-{$year}-";
        
        $last = self::withTrashed()
            ->where('sku', 'like', $prefix . '%')
            ->orderBy('sku', 'desc')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->sku, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // Relationships

    public function batches(): HasMany
    {
        return $this->hasMany(StockBatch::class);
    }

    public function activeBatches(): HasMany
    {
        return $this->hasMany(StockBatch::class)->where('status', 'active');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Computed properties

    public function getTotalAvailableAttribute(): float
    {
        return $this->activeBatches()->sum('quantity_available');
    }

    public function getTotalReservedAttribute(): float
    {
        return $this->activeBatches()->sum('quantity_reserved');
    }

    public function isLowStock(): bool
    {
        return $this->total_available <= $this->reorder_level;
    }

    public function needsReorder(): bool
    {
        return $this->is_active && $this->isLowStock();
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereHas('activeBatches', function ($q) {
            $q->havingRaw('SUM(quantity_available) <= stock_items.reorder_level');
        });
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Helpers

    public static function getCategories(): array
    {
        return [
            self::CATEGORY_OFFICE_SUPPLIES,
            self::CATEGORY_CLEANING,
            self::CATEGORY_WASH,
            self::CATEGORY_MEDICAL,
            self::CATEGORY_FOOD,
            self::CATEGORY_FUEL,
            self::CATEGORY_STATIONERY,
            self::CATEGORY_OTHER,
        ];
    }
}
