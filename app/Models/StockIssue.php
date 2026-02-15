<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockIssue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'issue_number',
        'issueable_type',
        'issueable_id',
        'hub_id',
        'issued_to',
        'department_id',
        'status',
        'purpose',
        'notes',
        'issue_date',
        'approved_by',
        'approved_at',
        'created_by',
        'issued_by',
        'issued_at',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'approved_at' => 'datetime',
        'issued_at' => 'datetime',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_ISSUED = 'issued';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($issue) {
            if (empty($issue->issue_number)) {
                $issue->issue_number = self::generateIssueNumber();
            }
            if (empty($issue->created_by) && auth()->check()) {
                $issue->created_by = auth()->id();
            }
        });
    }

    /**
     * Generate unique issue number
     */
    public static function generateIssueNumber(): string
    {
        $year = date('Y');
        $prefix = "ISS-{$year}-";
        
        $last = self::withTrashed()
            ->where('issue_number', 'like', $prefix . '%')
            ->orderBy('issue_number', 'desc')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->issue_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // Relationships

    public function issueable(): MorphTo
    {
        return $this->morphTo();
    }

    public function hub(): BelongsTo
    {
        return $this->belongsTo(Hub::class);
    }

    public function issuedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_to');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockIssueItem::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function issuedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    // Computed

    public function getTotalCostAttribute(): float
    {
        return $this->items->sum('total_cost') ?? 0;
    }

    // Status helpers

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPendingApproval(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isIssued(): bool
    {
        return $this->status === self::STATUS_ISSUED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    // Actions

    public function submit(): bool
    {
        if ($this->status !== self::STATUS_DRAFT) {
            return false;
        }

        $this->status = self::STATUS_PENDING_APPROVAL;
        return $this->save();
    }

    public function approve(): bool
    {
        if ($this->status !== self::STATUS_PENDING_APPROVAL) {
            return false;
        }

        $this->status = self::STATUS_APPROVED;
        $this->approved_by = auth()->id();
        $this->approved_at = now();
        return $this->save();
    }

    public function issue(): bool
    {
        if ($this->status !== self::STATUS_APPROVED) {
            return false;
        }

        // Process each item
        foreach ($this->items as $item) {
            if ($item->stock_batch_id) {
                $batch = $item->stockBatch;
                if (!$batch->issue($item->quantity_requested)) {
                    return false;
                }
                $item->quantity_issued = $item->quantity_requested;
                $item->save();
            }
        }

        $this->status = self::STATUS_ISSUED;
        $this->issued_by = auth()->id();
        $this->issued_at = now();
        return $this->save();
    }

    public function cancel(): bool
    {
        if (in_array($this->status, [self::STATUS_ISSUED, self::STATUS_CANCELLED])) {
            return false;
        }

        $this->status = self::STATUS_CANCELLED;
        return $this->save();
    }

    // Helpers

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'bg-gray-100 text-gray-800',
            self::STATUS_PENDING_APPROVAL => 'bg-yellow-100 text-yellow-800',
            self::STATUS_APPROVED => 'bg-blue-100 text-blue-800',
            self::STATUS_ISSUED => 'bg-green-100 text-green-800',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING_APPROVAL => 'Pending Approval',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_ISSUED => 'Issued',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    // Scopes

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByHub($query, $hubId)
    {
        return $query->where('hub_id', $hubId);
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('issueable_type', Project::class)
                     ->where('issueable_id', $projectId);
    }
}
