<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class DepartmentBudget extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'department_id',
        'fiscal_year',
        'name',
        'description',
        'total_budget',
        'status',
        'currency',
        'start_date',
        'end_date',
        'is_locked',
        'locked_at',
    ];

    protected $casts = [
        'total_budget' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_CLOSED = 'closed';

    /**
     * Department this budget belongs to
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Budget lines for this department budget
     */
    public function budgetLines(): MorphMany
    {
        return $this->morphMany(BudgetLine::class, 'budgetable');
    }

    /**
     * Get display name (department + fiscal year)
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?? ($this->department->name ?? 'Unknown') . ' - ' . $this->fiscal_year;
    }

    /**
     * Calculate total allocated budget
     */
    public function getAllocatedAttribute(): float
    {
        return $this->budgetLines()->sum('allocated');
    }

    /**
     * Calculate total committed amount
     */
    public function getCommittedAttribute(): float
    {
        return $this->budgetLines()->sum('committed');
    }

    /**
     * Calculate total spent amount
     */
    public function getSpentAttribute(): float
    {
        return $this->budgetLines()->sum('spent');
    }

    /**
     * Calculate remaining budget
     */
    public function getRemainingAttribute(): float
    {
        return $this->allocated - $this->spent;
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
     * Check if budget is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if budget is draft
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Activate the budget
     */
    public function activate(): bool
    {
        if ($this->budgetLines()->count() === 0) {
            return false;
        }

        $this->status = self::STATUS_ACTIVE;
        return $this->save();
    }

    /**
     * Scope for active budgets
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for draft budgets
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope for specific fiscal year
     */
    public function scopeForFiscalYear($query, string $fiscalYear)
    {
        return $query->where('fiscal_year', $fiscalYear);
    }

    /**
     * Budget locks for this budget
     */
    public function locks(): MorphMany
    {
        return $this->morphMany(BudgetLock::class, 'lockable');
    }

    /**
     * Active lock for this budget
     */
    public function activeLock(): MorphOne
    {
        return $this->morphOne(BudgetLock::class, 'lockable')->where('is_active', true);
    }

    /**
     * Budget threshold settings
     */
    public function threshold(): MorphOne
    {
        return $this->morphOne(BudgetThreshold::class, 'thresholdable');
    }

    /**
     * Check if budget is locked
     */
    public function isLocked(): bool
    {
        return BudgetLock::isLocked($this);
    }

    /**
     * Get active lock
     */
    public function getActiveLock(): ?BudgetLock
    {
        return BudgetLock::getActiveLock($this);
    }

    /**
     * Lock this budget
     */
    public function lock(
        User $user,
        string $lockType = BudgetLock::LOCK_SOFT,
        ?string $reason = null,
        ?\DateTimeInterface $lockUntil = null
    ): BudgetLock {
        $this->update(['is_locked' => true, 'locked_at' => now()]);
        return BudgetLock::createLock($this, $user, $lockType, $reason, $lockUntil);
    }

    /**
     * Unlock this budget
     */
    public function unlock(?User $user = null): bool
    {
        $lock = $this->getActiveLock();
        if ($lock) {
            $lock->deactivate($user);
        }
        return $this->update(['is_locked' => false, 'locked_at' => null]);
    }

    /**
     * Get or create threshold settings
     */
    public function getThreshold(): BudgetThreshold
    {
        return BudgetThreshold::getOrCreateFor($this);
    }

    /**
     * Get threshold level based on current utilization
     */
    public function getThresholdLevel(): string
    {
        $threshold = $this->getThreshold();
        return $threshold->getLevel($this->utilization);
    }

    /**
     * Check if spending should be blocked
     */
    public function isSpendingBlocked(): bool
    {
        $threshold = $this->getThreshold();
        return $threshold->shouldBlock($this->utilization);
    }

    /**
     * Get pending budget revisions
     */
    public function getPendingRevisionsAttribute()
    {
        return BudgetRevision::whereHas('budgetLine', function ($query) {
            $query->where('budgetable_type', self::class)
                  ->where('budgetable_id', $this->id);
        })->pending()->get();
    }

    /**
     * Get all budget revisions
     */
    public function getRevisionsAttribute()
    {
        return BudgetRevision::whereHas('budgetLine', function ($query) {
            $query->where('budgetable_type', self::class)
                  ->where('budgetable_id', $this->id);
        })->orderBy('created_at', 'desc')->get();
    }
}
