<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BudgetLine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'budget_category_id',
        'budgetable_type',
        'budgetable_id',
        'allocated',
        'committed',
        'spent',
        'is_active',
        'requires_approval_for_changes',
        'original_allocated',
    ];

    protected $casts = [
        'allocated' => 'decimal:2',
        'committed' => 'decimal:2',
        'spent' => 'decimal:2',
        'original_allocated' => 'decimal:2',
        'is_active' => 'boolean',
        'requires_approval_for_changes' => 'boolean',
    ];

    /**
     * The parent budgetable model (Project or DepartmentBudget)
     */
    public function budgetable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Budget category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(BudgetCategory::class, 'budget_category_id');
    }

    /**
     * Budget revisions for this line
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(BudgetRevision::class)->orderBy('created_at', 'desc');
    }

    /**
     * Pending revisions for this line
     */
    public function pendingRevisions(): HasMany
    {
        return $this->hasMany(BudgetRevision::class)->where('status', BudgetRevision::STATUS_PENDING);
    }

    /**
     * Calculate remaining budget
     */
    public function getRemainingAttribute(): float
    {
        return $this->allocated - $this->committed - $this->spent;
    }

    /**
     * Calculate available for commitment (allocated - committed - spent)
     */
    public function getAvailableAttribute(): float
    {
        return $this->allocated - $this->committed - $this->spent;
    }

    /**
     * Calculate utilization percentage
     */
    public function getUtilizationAttribute(): float
    {
        if ($this->allocated == 0) {
            return 0;
        }
        return round(($this->spent / $this->allocated) * 100, 1);
    }

    /**
     * Check if amount can be committed
     */
    public function canCommit(float $amount): bool
    {
        return $amount <= $this->available;
    }

    /**
     * Add a commitment
     */
    public function addCommitment(float $amount): bool
    {
        if (!$this->canCommit($amount)) {
            return false;
        }

        $this->committed += $amount;
        return $this->save();
    }

    /**
     * Release a commitment
     */
    public function releaseCommitment(float $amount): bool
    {
        $this->committed = max(0, $this->committed - $amount);
        return $this->save();
    }

    /**
     * Convert commitment to spent
     */
    public function commitmentToSpent(float $amount): bool
    {
        if ($amount > $this->committed) {
            return false;
        }

        $this->committed -= $amount;
        $this->spent += $amount;
        return $this->save();
    }

    /**
     * Scope for active budget lines
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Request a budget allocation change (creates pending revision)
     */
    public function requestAllocationChange(
        float $newAllocated,
        string $reason,
        string $type = BudgetRevision::TYPE_ALLOCATION_CHANGE
    ): BudgetRevision {
        return BudgetRevision::create([
            'budget_line_id' => $this->id,
            'user_id' => auth()->id(),
            'revision_type' => $type,
            'previous_allocated' => $this->allocated,
            'new_allocated' => $newAllocated,
            'change_amount' => $newAllocated - $this->allocated,
            'status' => BudgetRevision::STATUS_PENDING,
            'reason' => $reason,
            'reference_number' => BudgetRevision::generateReferenceNumber(),
        ]);
    }

    /**
     * Check if budget line has pending revisions
     */
    public function hasPendingRevisions(): bool
    {
        return $this->pendingRevisions()->exists();
    }

    /**
     * Get variance from original allocation
     */
    public function getVarianceAttribute(): float
    {
        return $this->allocated - $this->original_allocated;
    }

    /**
     * Get variance percentage from original
     */
    public function getVariancePercentageAttribute(): float
    {
        if ($this->original_allocated == 0) {
            return $this->allocated > 0 ? 100 : 0;
        }
        return round((($this->allocated - $this->original_allocated) / $this->original_allocated) * 100, 1);
    }

    /**
     * Check if parent budget is locked
     */
    public function isParentLocked(): bool
    {
        return BudgetLock::isLocked($this->budgetable);
    }

    /**
     * Get parent lock if exists
     */
    public function getParentLock(): ?BudgetLock
    {
        return BudgetLock::getActiveLock($this->budgetable);
    }
}
