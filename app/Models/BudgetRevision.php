<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_line_id',
        'user_id',
        'revision_type',
        'previous_allocated',
        'new_allocated',
        'change_amount',
        'status',
        'approved_by',
        'approved_at',
        'reason',
        'rejection_reason',
        'reference_number',
    ];

    protected $casts = [
        'previous_allocated' => 'decimal:2',
        'new_allocated' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    // Revision types
    const TYPE_ALLOCATION_CHANGE = 'allocation_change';
    const TYPE_REALLOCATION = 'reallocation';
    const TYPE_ADJUSTMENT = 'adjustment';
    const TYPE_CORRECTION = 'correction';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Budget line this revision belongs to
     */
    public function budgetLine(): BelongsTo
    {
        return $this->belongsTo(BudgetLine::class);
    }

    /**
     * User who requested this revision
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * User who approved/rejected this revision
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Generate reference number
     */
    public static function generateReferenceNumber(): string
    {
        $year = date('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;
        return sprintf('REV-%s-%04d', $year, $count);
    }

    /**
     * Approve this revision
     */
    public function approve(User $approver, ?string $comments = null): bool
    {
        $this->status = self::STATUS_APPROVED;
        $this->approved_by = $approver->id;
        $this->approved_at = now();

        if ($this->save()) {
            // Apply the change to the budget line
            $this->budgetLine->update([
                'allocated' => $this->new_allocated,
            ]);
            return true;
        }

        return false;
    }

    /**
     * Reject this revision
     */
    public function reject(User $approver, ?string $reason = null): bool
    {
        $this->status = self::STATUS_REJECTED;
        $this->approved_by = $approver->id;
        $this->approved_at = now();
        $this->rejection_reason = $reason;

        return $this->save();
    }

    /**
     * Check if pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if rejected
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Get change percentage
     */
    public function getChangePercentageAttribute(): float
    {
        if ($this->previous_allocated == 0) {
            return $this->change_amount > 0 ? 100 : 0;
        }
        return round(($this->change_amount / $this->previous_allocated) * 100, 1);
    }

    /**
     * Get formatted change with sign
     */
    public function getFormattedChangeAttribute(): string
    {
        $sign = $this->change_amount >= 0 ? '+' : '';
        return $sign . number_format($this->change_amount, 2);
    }

    /**
     * Scope for pending revisions
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for approved revisions
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope by revision type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('revision_type', $type);
    }
}
