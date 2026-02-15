<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Approval extends Model
{
    use HasFactory;

    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'user_id',
        'approval_matrix_id',
        'status',
        'level',
        'comments',
        'actioned_at',
    ];

    protected $casts = [
        'actioned_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_RETURNED = 'returned';

    /**
     * The document being approved
     */
    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * User who actioned this approval
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Approval matrix rule
     */
    public function approvalMatrix(): BelongsTo
    {
        return $this->belongsTo(ApprovalMatrix::class);
    }

    /**
     * Approve this item
     */
    public function approve(?string $comments = null): bool
    {
        $this->status = self::STATUS_APPROVED;
        $this->comments = $comments;
        $this->actioned_at = now();
        $this->user_id = auth()->id();

        return $this->save();
    }

    /**
     * Reject this item
     */
    public function reject(?string $comments = null): bool
    {
        $this->status = self::STATUS_REJECTED;
        $this->comments = $comments;
        $this->actioned_at = now();
        $this->user_id = auth()->id();

        return $this->save();
    }

    /**
     * Return for revision
     */
    public function return(?string $comments = null): bool
    {
        $this->status = self::STATUS_RETURNED;
        $this->comments = $comments;
        $this->actioned_at = now();
        $this->user_id = auth()->id();

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
     * Scope for pending approvals
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for user's pending approvals
     */
    public function scopeForUser($query, User $user)
    {
        $roleIds = $user->roles()->pluck('id');

        return $query->whereHas('approvalMatrix', function ($q) use ($roleIds) {
            $q->whereIn('approver_role_id', $roleIds);
        });
    }
}
