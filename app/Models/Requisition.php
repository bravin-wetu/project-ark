<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Requisition extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'requisition_number',
        'title',
        'description',
        'justification',
        'requisitionable_type',
        'requisitionable_id',
        'budget_line_id',
        'requested_by',
        'requested_at',
        'delivery_hub_id',
        'required_date',
        'estimated_total',
        'currency',
        'status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'priority',
        'notes',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'required_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'estimated_total' => 'decimal:2',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';

    // Priority constants
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Get the parent requisitionable model (Project or DepartmentBudget).
     */
    public function requisitionable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Budget line this requisition is charged to.
     */
    public function budgetLine(): BelongsTo
    {
        return $this->belongsTo(BudgetLine::class);
    }

    /**
     * User who requested.
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * User who approved.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * User who rejected.
     */
    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Delivery hub.
     */
    public function deliveryHub(): BelongsTo
    {
        return $this->belongsTo(Hub::class, 'delivery_hub_id');
    }

    /**
     * Line items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(RequisitionItem::class)->orderBy('sort_order');
    }

    /**
     * RFQs created from this requisition.
     */
    public function rfqs(): HasMany
    {
        return $this->hasMany(Rfq::class);
    }

    /**
     * Generate unique requisition number.
     */
    public static function generateNumber(): string
    {
        $year = date('Y');
        $prefix = 'REQ';
        
        $lastReq = static::withTrashed()
            ->where('requisition_number', 'like', "{$prefix}-{$year}-%")
            ->orderByRaw('CAST(SUBSTRING_INDEX(requisition_number, "-", -1) AS UNSIGNED) DESC')
            ->first();

        if ($lastReq) {
            $lastNumber = (int) substr($lastReq->requisition_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $year, $newNumber);
    }

    /**
     * Calculate and update estimated total from items.
     */
    public function recalculateTotal(): void
    {
        $this->estimated_total = $this->items()->sum('estimated_total');
        $this->save();
    }

    /**
     * Submit for approval.
     */
    public function submitForApproval(): bool
    {
        if ($this->status !== self::STATUS_DRAFT) {
            return false;
        }

        $this->status = self::STATUS_PENDING_APPROVAL;
        return $this->save();
    }

    /**
     * Approve the requisition.
     */
    public function approve(User $approver): bool
    {
        if ($this->status !== self::STATUS_PENDING_APPROVAL) {
            return false;
        }

        $this->status = self::STATUS_APPROVED;
        $this->approved_by = $approver->id;
        $this->approved_at = now();
        
        // Commit budget
        $this->budgetLine->addCommitment($this->estimated_total);

        return $this->save();
    }

    /**
     * Reject the requisition.
     */
    public function reject(User $rejecter, string $reason): bool
    {
        if ($this->status !== self::STATUS_PENDING_APPROVAL) {
            return false;
        }

        $this->status = self::STATUS_REJECTED;
        $this->rejected_by = $rejecter->id;
        $this->rejected_at = now();
        $this->rejection_reason = $reason;

        return $this->save();
    }

    /**
     * Cancel the requisition.
     */
    public function cancel(): bool
    {
        if (in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED])) {
            return false;
        }

        // Release commitment if approved
        if ($this->status === self::STATUS_APPROVED || $this->status === self::STATUS_IN_PROGRESS) {
            $this->budgetLine->releaseCommitment($this->estimated_total);
        }

        $this->status = self::STATUS_CANCELLED;
        return $this->save();
    }

    /**
     * Check if requisition can be edited.
     */
    public function canEdit(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED]);
    }

    /**
     * Check if requisition can be submitted.
     */
    public function canSubmit(): bool
    {
        return $this->status === self::STATUS_DRAFT && $this->items()->count() > 0;
    }

    /**
     * Check if requisition can be approved.
     */
    public function canApprove(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'smoke',
            self::STATUS_PENDING_APPROVAL => 'amber',
            self::STATUS_APPROVED => 'emerald',
            self::STATUS_REJECTED => 'red',
            self::STATUS_CANCELLED => 'smoke',
            self::STATUS_IN_PROGRESS => 'blue',
            self::STATUS_COMPLETED => 'emerald',
            default => 'smoke',
        };
    }

    /**
     * Get priority badge color.
     */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            self::PRIORITY_LOW => 'smoke',
            self::PRIORITY_NORMAL => 'blue',
            self::PRIORITY_HIGH => 'amber',
            self::PRIORITY_URGENT => 'red',
            default => 'smoke',
        };
    }

    /**
     * Scope for pending approval.
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', self::STATUS_PENDING_APPROVAL);
    }

    /**
     * Scope for approved.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($requisition) {
            if (empty($requisition->requisition_number)) {
                $requisition->requisition_number = static::generateNumber();
            }
            if (empty($requisition->requested_at)) {
                $requisition->requested_at = now();
            }
        });
    }
}
