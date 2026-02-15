<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetMaintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_number',
        'asset_id',
        'type',
        'status',
        'title',
        'description',
        'scheduled_date',
        'start_date',
        'completion_date',
        'service_provider',
        'technician',
        'estimated_cost',
        'actual_cost',
        'currency',
        'condition_before',
        'condition_after',
        'findings',
        'work_performed',
        'recommendations',
        'next_maintenance_due',
        'created_by',
        'completed_by',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'start_date' => 'date',
        'completion_date' => 'date',
        'next_maintenance_due' => 'date',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
    ];

    // Type constants
    const TYPE_PREVENTIVE = 'preventive';
    const TYPE_CORRECTIVE = 'corrective';
    const TYPE_INSPECTION = 'inspection';
    const TYPE_UPGRADE = 'upgrade';

    // Status constants
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($maintenance) {
            if (empty($maintenance->maintenance_number)) {
                $maintenance->maintenance_number = self::generateMaintenanceNumber();
            }
            if (empty($maintenance->created_by) && auth()->check()) {
                $maintenance->created_by = auth()->id();
            }
        });
    }

    /**
     * Generate unique maintenance number
     */
    public static function generateMaintenanceNumber(): string
    {
        $year = date('Y');
        $prefix = "MNT-{$year}-";
        
        $last = self::where('maintenance_number', 'like', $prefix . '%')
            ->orderBy('maintenance_number', 'desc')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->maintenance_number, -4);
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    // Status helpers

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    // Actions

    public function start(): bool
    {
        if ($this->status !== self::STATUS_SCHEDULED) {
            return false;
        }

        $this->status = self::STATUS_IN_PROGRESS;
        $this->start_date = now();
        $this->save();

        // Update asset status
        $this->asset->update(['status' => Asset::STATUS_IN_MAINTENANCE]);

        return true;
    }

    public function complete(array $data = []): bool
    {
        if ($this->status !== self::STATUS_IN_PROGRESS) {
            return false;
        }

        $this->status = self::STATUS_COMPLETED;
        $this->completion_date = now();
        $this->completed_by = auth()->id();
        
        if (isset($data['actual_cost'])) {
            $this->actual_cost = $data['actual_cost'];
        }
        if (isset($data['condition_after'])) {
            $this->condition_after = $data['condition_after'];
        }
        if (isset($data['work_performed'])) {
            $this->work_performed = $data['work_performed'];
        }
        if (isset($data['findings'])) {
            $this->findings = $data['findings'];
        }
        if (isset($data['recommendations'])) {
            $this->recommendations = $data['recommendations'];
        }
        if (isset($data['next_maintenance_due'])) {
            $this->next_maintenance_due = $data['next_maintenance_due'];
        }
        
        $this->save();

        // Update asset
        $asset = $this->asset;
        $asset->status = Asset::STATUS_ACTIVE;
        if (isset($data['condition_after'])) {
            $asset->condition = $data['condition_after'];
        }
        $asset->save();

        return true;
    }

    public function cancel(?string $reason = null): bool
    {
        if ($this->status === self::STATUS_COMPLETED) {
            return false;
        }

        $oldStatus = $this->status;
        $this->status = self::STATUS_CANCELLED;
        $this->save();

        // Restore asset status if it was in maintenance
        if ($oldStatus === self::STATUS_IN_PROGRESS && $this->asset->status === Asset::STATUS_IN_MAINTENANCE) {
            $this->asset->update(['status' => Asset::STATUS_ACTIVE]);
        }

        return true;
    }

    // Helpers

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_SCHEDULED => 'bg-yellow-100 text-yellow-800',
            self::STATUS_IN_PROGRESS => 'bg-blue-100 text-blue-800',
            self::STATUS_COMPLETED => 'bg-green-100 text-green-800',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getTypeBadgeClass(): string
    {
        return match($this->type) {
            self::TYPE_PREVENTIVE => 'bg-blue-100 text-blue-800',
            self::TYPE_CORRECTIVE => 'bg-orange-100 text-orange-800',
            self::TYPE_INSPECTION => 'bg-purple-100 text-purple-800',
            self::TYPE_UPGRADE => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_PREVENTIVE => 'Preventive',
            self::TYPE_CORRECTIVE => 'Corrective',
            self::TYPE_INSPECTION => 'Inspection',
            self::TYPE_UPGRADE => 'Upgrade',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }
}
