<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BudgetThreshold extends Model
{
    use HasFactory;

    protected $fillable = [
        'thresholdable_type',
        'thresholdable_id',
        'warning_percentage',
        'critical_percentage',
        'block_percentage',
        'send_warning_alert',
        'send_critical_alert',
        'block_on_exceed',
        'warning_sent_at',
        'critical_sent_at',
    ];

    protected $casts = [
        'warning_percentage' => 'decimal:2',
        'critical_percentage' => 'decimal:2',
        'block_percentage' => 'decimal:2',
        'send_warning_alert' => 'boolean',
        'send_critical_alert' => 'boolean',
        'block_on_exceed' => 'boolean',
        'warning_sent_at' => 'datetime',
        'critical_sent_at' => 'datetime',
    ];

    // Alert levels
    const LEVEL_NORMAL = 'normal';
    const LEVEL_WARNING = 'warning';
    const LEVEL_CRITICAL = 'critical';
    const LEVEL_EXCEEDED = 'exceeded';

    /**
     * The entity with this threshold (Project or DepartmentBudget)
     */
    public function thresholdable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get threshold level based on utilization percentage
     */
    public function getLevel(float $utilization): string
    {
        if ($utilization >= $this->block_percentage) {
            return self::LEVEL_EXCEEDED;
        }
        if ($utilization >= $this->critical_percentage) {
            return self::LEVEL_CRITICAL;
        }
        if ($utilization >= $this->warning_percentage) {
            return self::LEVEL_WARNING;
        }
        return self::LEVEL_NORMAL;
    }

    /**
     * Check if warning should be sent
     */
    public function shouldSendWarning(float $utilization): bool
    {
        if (!$this->send_warning_alert) {
            return false;
        }

        if ($utilization < $this->warning_percentage) {
            return false;
        }

        // Don't send if already sent within 24 hours
        if ($this->warning_sent_at && $this->warning_sent_at->diffInHours(now()) < 24) {
            return false;
        }

        return true;
    }

    /**
     * Check if critical alert should be sent
     */
    public function shouldSendCritical(float $utilization): bool
    {
        if (!$this->send_critical_alert) {
            return false;
        }

        if ($utilization < $this->critical_percentage) {
            return false;
        }

        // Don't send if already sent within 12 hours
        if ($this->critical_sent_at && $this->critical_sent_at->diffInHours(now()) < 12) {
            return false;
        }

        return true;
    }

    /**
     * Check if spending should be blocked
     */
    public function shouldBlock(float $utilization): bool
    {
        return $this->block_on_exceed && $utilization >= $this->block_percentage;
    }

    /**
     * Mark warning as sent
     */
    public function markWarningSent(): bool
    {
        $this->warning_sent_at = now();
        return $this->save();
    }

    /**
     * Mark critical alert as sent
     */
    public function markCriticalSent(): bool
    {
        $this->critical_sent_at = now();
        return $this->save();
    }

    /**
     * Get CSS class for threshold level
     */
    public static function getLevelClass(string $level): string
    {
        return match ($level) {
            self::LEVEL_WARNING => 'text-yellow-600 bg-yellow-100',
            self::LEVEL_CRITICAL => 'text-orange-600 bg-orange-100',
            self::LEVEL_EXCEEDED => 'text-red-600 bg-red-100',
            default => 'text-green-600 bg-green-100',
        };
    }

    /**
     * Get icon for threshold level
     */
    public static function getLevelIcon(string $level): string
    {
        return match ($level) {
            self::LEVEL_WARNING => 'exclamation-triangle',
            self::LEVEL_CRITICAL => 'exclamation-circle',
            self::LEVEL_EXCEEDED => 'x-circle',
            default => 'check-circle',
        };
    }

    /**
     * Get or create threshold for an entity with defaults
     */
    public static function getOrCreateFor(Model $entity): self
    {
        return self::firstOrCreate(
            [
                'thresholdable_type' => get_class($entity),
                'thresholdable_id' => $entity->id,
            ],
            [
                'warning_percentage' => 80.00,
                'critical_percentage' => 95.00,
                'block_percentage' => 100.00,
                'send_warning_alert' => true,
                'send_critical_alert' => true,
                'block_on_exceed' => false,
            ]
        );
    }
}
