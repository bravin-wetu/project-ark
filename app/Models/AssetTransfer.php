<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_number',
        'asset_id',
        'from_hub_id',
        'from_user_id',
        'from_location',
        'to_hub_id',
        'to_user_id',
        'to_location',
        'status',
        'transfer_date',
        'expected_arrival',
        'received_date',
        'reason',
        'notes',
        'condition_on_transfer',
        'condition_on_receipt',
        'initiated_by',
        'received_by',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'expected_arrival' => 'date',
        'received_date' => 'date',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_RECEIVED = 'received';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transfer) {
            if (empty($transfer->transfer_number)) {
                $transfer->transfer_number = self::generateTransferNumber();
            }
            if (empty($transfer->initiated_by) && auth()->check()) {
                $transfer->initiated_by = auth()->id();
            }
        });
    }

    /**
     * Generate unique transfer number
     */
    public static function generateTransferNumber(): string
    {
        $year = date('Y');
        $prefix = "TRF-{$year}-";
        
        $last = self::where('transfer_number', 'like', $prefix . '%')
            ->orderBy('transfer_number', 'desc')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->transfer_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // Relationships

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function fromHub(): BelongsTo
    {
        return $this->belongsTo(Hub::class, 'from_hub_id');
    }

    public function toHub(): BelongsTo
    {
        return $this->belongsTo(Hub::class, 'to_hub_id');
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    // Status helpers

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isInTransit(): bool
    {
        return $this->status === self::STATUS_IN_TRANSIT;
    }

    public function isReceived(): bool
    {
        return $this->status === self::STATUS_RECEIVED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    // Actions

    public function startTransit(): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->status = self::STATUS_IN_TRANSIT;
        $this->save();

        // Update asset status
        $this->asset->update(['status' => Asset::STATUS_IN_TRANSIT]);

        return true;
    }

    public function confirmReceipt(string $condition, ?string $notes = null): bool
    {
        if (!in_array($this->status, [self::STATUS_PENDING, self::STATUS_IN_TRANSIT])) {
            return false;
        }

        $this->status = self::STATUS_RECEIVED;
        $this->received_date = now();
        $this->condition_on_receipt = $condition;
        $this->received_by = auth()->id();
        if ($notes) {
            $this->notes = $notes;
        }
        $this->save();

        // Update asset
        $asset = $this->asset;
        $asset->hub_id = $this->to_hub_id;
        $asset->assigned_to = $this->to_user_id;
        $asset->location = $this->to_location;
        $asset->status = Asset::STATUS_ACTIVE;
        $asset->condition = $condition;
        $asset->save();

        return true;
    }

    public function cancel(?string $reason = null): bool
    {
        if ($this->status === self::STATUS_RECEIVED) {
            return false;
        }

        $this->status = self::STATUS_CANCELLED;
        if ($reason) {
            $this->notes = $reason;
        }
        $this->save();

        // Restore asset status
        if ($this->asset->status === Asset::STATUS_IN_TRANSIT) {
            $this->asset->update(['status' => Asset::STATUS_ACTIVE]);
        }

        return true;
    }

    // Helpers

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
            self::STATUS_IN_TRANSIT => 'bg-blue-100 text-blue-800',
            self::STATUS_RECEIVED => 'bg-green-100 text-green-800',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_TRANSIT => 'In Transit',
            self::STATUS_RECEIVED => 'Received',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }
}
