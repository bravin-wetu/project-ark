<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BudgetLock extends Model
{
    use HasFactory;

    protected $fillable = [
        'lockable_type',
        'lockable_id',
        'locked_by',
        'unlocked_by',
        'lock_type',
        'reason',
        'locked_at',
        'unlocked_at',
        'lock_until',
        'is_active',
    ];

    protected $casts = [
        'locked_at' => 'datetime',
        'unlocked_at' => 'datetime',
        'lock_until' => 'date',
        'is_active' => 'boolean',
    ];

    // Lock types
    const LOCK_SOFT = 'soft';
    const LOCK_HARD = 'hard';

    /**
     * The locked entity (Project or DepartmentBudget)
     */
    public function lockable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * User who locked this budget
     */
    public function locker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    /**
     * User who unlocked this budget
     */
    public function unlocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unlocked_by');
    }

    /**
     * Check if lock is still active
     */
    public function isActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check if lock has expired
        if ($this->lock_until && $this->lock_until->isPast()) {
            $this->deactivate();
            return false;
        }

        return true;
    }

    /**
     * Check if this is a hard lock
     */
    public function isHardLock(): bool
    {
        return $this->lock_type === self::LOCK_HARD;
    }

    /**
     * Check if this is a soft lock
     */
    public function isSoftLock(): bool
    {
        return $this->lock_type === self::LOCK_SOFT;
    }

    /**
     * Deactivate/unlock this lock
     */
    public function deactivate(?User $user = null): bool
    {
        $this->is_active = false;
        $this->unlocked_at = now();
        $this->unlocked_by = $user?->id ?? auth()->id();

        return $this->save();
    }

    /**
     * Get duration of lock
     */
    public function getDurationAttribute(): string
    {
        $end = $this->unlocked_at ?? now();
        return $this->locked_at->diffForHumans($end, true);
    }

    /**
     * Create a lock for an entity
     */
    public static function createLock(
        Model $lockable,
        User $user,
        string $lockType = self::LOCK_SOFT,
        ?string $reason = null,
        ?\DateTimeInterface $lockUntil = null
    ): self {
        // Deactivate any existing active locks
        self::where('lockable_type', get_class($lockable))
            ->where('lockable_id', $lockable->id)
            ->where('is_active', true)
            ->update(['is_active' => false, 'unlocked_at' => now(), 'unlocked_by' => $user->id]);

        return self::create([
            'lockable_type' => get_class($lockable),
            'lockable_id' => $lockable->id,
            'locked_by' => $user->id,
            'lock_type' => $lockType,
            'reason' => $reason,
            'locked_at' => now(),
            'lock_until' => $lockUntil,
            'is_active' => true,
        ]);
    }

    /**
     * Get active lock for an entity
     */
    public static function getActiveLock(Model $lockable): ?self
    {
        return self::where('lockable_type', get_class($lockable))
            ->where('lockable_id', $lockable->id)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Check if entity is locked
     */
    public static function isLocked(Model $lockable): bool
    {
        $lock = self::getActiveLock($lockable);
        return $lock && $lock->isActive();
    }

    /**
     * Scope for active locks
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for hard locks
     */
    public function scopeHard($query)
    {
        return $query->where('lock_type', self::LOCK_HARD);
    }

    /**
     * Scope for soft locks
     */
    public function scopeSoft($query)
    {
        return $query->where('lock_type', self::LOCK_SOFT);
    }
}
