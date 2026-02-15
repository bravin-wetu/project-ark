<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'donor_id',
        'department_id',
        'project_manager_id',
        'start_date',
        'end_date',
        'total_budget',
        'status',
        'currency',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_budget' => 'decimal:2',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_CLOSED = 'closed';
    const STATUS_SUSPENDED = 'suspended';

    /**
     * Donor funding this project
     */
    public function donor(): BelongsTo
    {
        return $this->belongsTo(Donor::class);
    }

    /**
     * Department owning this project
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Project manager
     */
    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    /**
     * Hubs assigned to this project
     */
    public function hubs(): BelongsToMany
    {
        return $this->belongsToMany(Hub::class)->withTimestamps();
    }

    /**
     * Budget lines for this project
     */
    public function budgetLines(): MorphMany
    {
        return $this->morphMany(BudgetLine::class, 'budgetable');
    }

    /**
     * Requisitions for this project
     */
    public function requisitions(): MorphMany
    {
        return $this->morphMany(Requisition::class, 'requisitionable');
    }

    /**
     * RFQs for this project
     */
    public function rfqs(): MorphMany
    {
        return $this->morphMany(Rfq::class, 'rfqable');
    }

    /**
     * Purchase Orders for this project
     */
    public function purchaseOrders(): MorphMany
    {
        return $this->morphMany(PurchaseOrder::class, 'purchaseable');
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
     * Check if project is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if project is draft
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Activate the project
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
     * Scope for active projects
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for draft projects
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Budget locks for this project
     */
    public function locks(): MorphMany
    {
        return $this->morphMany(BudgetLock::class, 'lockable');
    }

    /**
     * Active lock for this project
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
     * Check if project budget is locked
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
     * Lock this project's budget
     */
    public function lock(
        User $user,
        string $lockType = BudgetLock::LOCK_SOFT,
        ?string $reason = null,
        ?\DateTimeInterface $lockUntil = null
    ): BudgetLock {
        return BudgetLock::createLock($this, $user, $lockType, $reason, $lockUntil);
    }

    /**
     * Unlock this project's budget
     */
    public function unlock(?User $user = null): bool
    {
        $lock = $this->getActiveLock();
        if ($lock) {
            return $lock->deactivate($user);
        }
        return true;
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
