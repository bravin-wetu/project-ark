<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalMatrix extends Model
{
    use HasFactory;

    protected $fillable = [
        'context_type',
        'document_type',
        'min_amount',
        'max_amount',
        'approver_role_id',
        'approval_level',
        'is_active',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Context types
    const CONTEXT_PROJECT = 'project';
    const CONTEXT_DEPARTMENT = 'department';

    // Document types
    const DOC_REQUISITION = 'requisition';
    const DOC_RFQ = 'rfq';
    const DOC_PURCHASE_ORDER = 'purchase_order';

    /**
     * Approver role
     */
    public function approverRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'approver_role_id');
    }

    /**
     * Check if amount falls within this matrix threshold
     */
    public function matchesAmount(float $amount): bool
    {
        if ($amount < $this->min_amount) {
            return false;
        }

        if ($this->max_amount !== null && $amount > $this->max_amount) {
            return false;
        }

        return true;
    }

    /**
     * Find applicable approval matrix
     */
    public static function findForDocument(
        string $contextType,
        string $documentType,
        float $amount
    ): ?self {
        return self::where('context_type', $contextType)
            ->where('document_type', $documentType)
            ->where('is_active', true)
            ->where('min_amount', '<=', $amount)
            ->where(function ($query) use ($amount) {
                $query->whereNull('max_amount')
                    ->orWhere('max_amount', '>=', $amount);
            })
            ->orderBy('approval_level')
            ->first();
    }

    /**
     * Get all approval levels for a document
     */
    public static function getApprovalLevels(
        string $contextType,
        string $documentType,
        float $amount
    ): \Illuminate\Database\Eloquent\Collection {
        return self::where('context_type', $contextType)
            ->where('document_type', $documentType)
            ->where('is_active', true)
            ->where('min_amount', '<=', $amount)
            ->where(function ($query) use ($amount) {
                $query->whereNull('max_amount')
                    ->orWhere('max_amount', '>=', $amount);
            })
            ->orderBy('approval_level')
            ->get();
    }

    /**
     * Scope for active matrices
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
