<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_tag',
        'name',
        'description',
        'category',
        'subcategory',
        'serial_number',
        'model',
        'manufacturer',
        'goods_receipt_id',
        'goods_receipt_item_id',
        'purchase_order_id',
        'supplier_id',
        'assetable_type',
        'assetable_id',
        'budget_line_id',
        'acquisition_cost',
        'acquisition_date',
        'current_value',
        'salvage_value',
        'useful_life_months',
        'currency',
        'hub_id',
        'assigned_to',
        'location',
        'status',
        'condition',
        'warranty_expiry',
        'warranty_notes',
        'insurance_policy',
        'insurance_expiry',
        'disposal_date',
        'disposal_method',
        'disposal_value',
        'disposal_notes',
        'disposed_by',
        'notes',
        'attachments',
        'created_by',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'warranty_expiry' => 'date',
        'insurance_expiry' => 'date',
        'disposal_date' => 'date',
        'acquisition_cost' => 'decimal:2',
        'current_value' => 'decimal:2',
        'salvage_value' => 'decimal:2',
        'disposal_value' => 'decimal:2',
        'attachments' => 'array',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_IN_MAINTENANCE = 'in_maintenance';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_DISPOSED = 'disposed';
    const STATUS_LOST = 'lost';
    const STATUS_STOLEN = 'stolen';

    // Condition constants
    const CONDITION_NEW = 'new';
    const CONDITION_EXCELLENT = 'excellent';
    const CONDITION_GOOD = 'good';
    const CONDITION_FAIR = 'fair';
    const CONDITION_POOR = 'poor';
    const CONDITION_DAMAGED = 'damaged';
    const CONDITION_NON_FUNCTIONAL = 'non_functional';

    // Category constants
    const CATEGORY_EQUIPMENT = 'Equipment';
    const CATEGORY_VEHICLE = 'Vehicle';
    const CATEGORY_FURNITURE = 'Furniture';
    const CATEGORY_IT = 'IT Equipment';
    const CATEGORY_MACHINERY = 'Machinery';
    const CATEGORY_TOOLS = 'Tools';
    const CATEGORY_OTHER = 'Other';

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($asset) {
            if (empty($asset->asset_tag)) {
                $asset->asset_tag = self::generateAssetTag();
            }
            if (empty($asset->created_by) && auth()->check()) {
                $asset->created_by = auth()->id();
            }
        });
    }

    /**
     * Generate unique asset tag
     */
    public static function generateAssetTag(): string
    {
        $year = date('Y');
        $prefix = "AST-{$year}-";
        
        $lastAsset = self::withTrashed()
            ->where('asset_tag', 'like', $prefix . '%')
            ->orderBy('asset_tag', 'desc')
            ->first();

        if ($lastAsset) {
            $lastNumber = (int) substr($lastAsset->asset_tag, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create asset from goods receipt item
     */
    public static function createFromReceiptItem(GoodsReceiptItem $item, array $additionalData = []): self
    {
        $receipt = $item->goodsReceipt;
        $po = $receipt->purchaseOrder;

        return self::create(array_merge([
            'name' => $item->name,
            'description' => $item->description,
            'goods_receipt_id' => $receipt->id,
            'goods_receipt_item_id' => $item->id,
            'purchase_order_id' => $po->id,
            'supplier_id' => $po->supplier_id,
            'assetable_type' => $po->purchaseable_type,
            'assetable_id' => $po->purchaseable_id,
            'budget_line_id' => $po->budget_line_id,
            'acquisition_cost' => $item->unit_price,
            'acquisition_date' => $receipt->received_date ?? now(),
            'current_value' => $item->unit_price,
            'hub_id' => $receipt->receiving_hub_id,
            'condition' => $item->condition ?? self::CONDITION_NEW,
            'status' => self::STATUS_PENDING,
        ], $additionalData));
    }

    // Relationships

    public function assetable(): MorphTo
    {
        return $this->morphTo();
    }

    public function hub(): BelongsTo
    {
        return $this->belongsTo(Hub::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
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

    public function disposedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disposed_by');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(AssetTransfer::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class);
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

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('assetable_type', Project::class)
                     ->where('assetable_id', $projectId);
    }

    public function scopeForDepartment($query, $departmentBudgetId)
    {
        return $query->where('assetable_type', DepartmentBudget::class)
                     ->where('assetable_id', $departmentBudgetId);
    }

    // Status helpers

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isDisposed(): bool
    {
        return $this->status === self::STATUS_DISPOSED;
    }

    public function canBeTransferred(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_PENDING]);
    }

    public function canBeDisposed(): bool
    {
        return !in_array($this->status, [self::STATUS_DISPOSED, self::STATUS_IN_TRANSIT]);
    }

    // Actions

    public function activate(): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->status = self::STATUS_ACTIVE;
        return $this->save();
    }

    public function assignTo(User $user, ?string $location = null): bool
    {
        $this->assigned_to = $user->id;
        if ($location) {
            $this->location = $location;
        }
        return $this->save();
    }

    public function dispose(string $method, ?float $value = null, ?string $notes = null): bool
    {
        if (!$this->canBeDisposed()) {
            return false;
        }

        $this->status = self::STATUS_DISPOSED;
        $this->disposal_date = now();
        $this->disposal_method = $method;
        $this->disposal_value = $value;
        $this->disposal_notes = $notes;
        $this->disposed_by = auth()->id();

        return $this->save();
    }

    // Helpers

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'bg-green-100 text-green-800',
            self::STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
            self::STATUS_IN_MAINTENANCE => 'bg-blue-100 text-blue-800',
            self::STATUS_IN_TRANSIT => 'bg-purple-100 text-purple-800',
            self::STATUS_DISPOSED => 'bg-gray-100 text-gray-800',
            self::STATUS_LOST, self::STATUS_STOLEN => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getConditionBadgeClass(): string
    {
        return match($this->condition) {
            self::CONDITION_NEW, self::CONDITION_EXCELLENT => 'bg-green-100 text-green-800',
            self::CONDITION_GOOD => 'bg-blue-100 text-blue-800',
            self::CONDITION_FAIR => 'bg-yellow-100 text-yellow-800',
            self::CONDITION_POOR, self::CONDITION_DAMAGED => 'bg-orange-100 text-orange-800',
            self::CONDITION_NON_FUNCTIONAL => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public static function getCategories(): array
    {
        return [
            self::CATEGORY_EQUIPMENT,
            self::CATEGORY_VEHICLE,
            self::CATEGORY_FURNITURE,
            self::CATEGORY_IT,
            self::CATEGORY_MACHINERY,
            self::CATEGORY_TOOLS,
            self::CATEGORY_OTHER,
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_IN_MAINTENANCE => 'In Maintenance',
            self::STATUS_IN_TRANSIT => 'In Transit',
            self::STATUS_DISPOSED => 'Disposed',
            self::STATUS_LOST => 'Lost',
            self::STATUS_STOLEN => 'Stolen',
        ];
    }

    public static function getConditions(): array
    {
        return [
            self::CONDITION_NEW => 'New',
            self::CONDITION_EXCELLENT => 'Excellent',
            self::CONDITION_GOOD => 'Good',
            self::CONDITION_FAIR => 'Fair',
            self::CONDITION_POOR => 'Poor',
            self::CONDITION_DAMAGED => 'Damaged',
            self::CONDITION_NON_FUNCTIONAL => 'Non-functional',
        ];
    }
}
