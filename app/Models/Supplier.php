<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'trading_name',
        'description',
        'contact_person',
        'email',
        'phone',
        'mobile',
        'website',
        'address',
        'city',
        'country',
        'postal_code',
        'tax_id',
        'registration_number',
        'categories',
        'payment_terms',
        'currency',
        'status',
        'rating',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'categories' => 'array',
        'payment_terms' => 'array',
        'rating' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_BLACKLISTED = 'blacklisted';
    const STATUS_PENDING_APPROVAL = 'pending_approval';

    /**
     * Category constants
     */
    const CATEGORY_GOODS = 'goods';
    const CATEGORY_SERVICES = 'services';
    const CATEGORY_WORKS = 'works';

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($supplier) {
            if (empty($supplier->code)) {
                $supplier->code = self::generateCode();
            }
            if (empty($supplier->created_by) && auth()->check()) {
                $supplier->created_by = auth()->id();
            }
        });
    }

    /**
     * Generate unique supplier code
     */
    public static function generateCode(): string
    {
        $year = date('Y');
        $lastSupplier = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastSupplier && preg_match('/SUP-' . $year . '-(\d+)/', $lastSupplier->code, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return 'SUP-' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relationships
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rfqs(): HasMany
    {
        return $this->hasMany(RfqSupplier::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Status checks
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isPendingApproval(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }

    public function isBlacklisted(): bool
    {
        return $this->status === self::STATUS_BLACKLISTED;
    }

    /**
     * Approve the supplier
     */
    public function approve(?User $approver = null): bool
    {
        if (!$this->isPendingApproval()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_ACTIVE,
            'approved_by' => $approver?->id ?? auth()->id(),
            'approved_at' => now(),
        ]);

        return true;
    }

    /**
     * Blacklist the supplier
     */
    public function blacklist(?string $reason = null): bool
    {
        $this->update([
            'status' => self::STATUS_BLACKLISTED,
            'notes' => $reason ? $this->notes . "\n\nBlacklisted: " . $reason : $this->notes,
        ]);

        return true;
    }

    /**
     * Check if supplier handles a category
     */
    public function handlesCategory(string $category): bool
    {
        return in_array($category, $this->categories ?? []);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', self::STATUS_PENDING_APPROVAL);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->whereJsonContains('categories', $category);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'badge-success',
            self::STATUS_INACTIVE => 'badge-secondary',
            self::STATUS_BLACKLISTED => 'badge-danger',
            self::STATUS_PENDING_APPROVAL => 'badge-warning',
            default => 'badge-secondary',
        };
    }

    /**
     * Get display name
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->trading_name ?? $this->name;
    }

    /**
     * Get full address
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }
}
