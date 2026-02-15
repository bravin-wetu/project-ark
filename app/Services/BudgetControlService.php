<?php

namespace App\Services;

use App\Models\BudgetLine;
use App\Models\BudgetLock;
use App\Models\BudgetRevision;
use App\Models\BudgetThreshold;
use App\Models\DepartmentBudget;
use App\Models\Project;
use App\Models\User;
use App\Notifications\BudgetThresholdAlert;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class BudgetControlService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Lock a budget (Project or DepartmentBudget)
     */
    public function lockBudget(
        Model $budgetable,
        User $user,
        string $lockType = BudgetLock::LOCK_SOFT,
        ?string $reason = null,
        ?\DateTimeInterface $lockUntil = null
    ): BudgetLock {
        return BudgetLock::createLock($budgetable, $user, $lockType, $reason, $lockUntil);
    }

    /**
     * Unlock a budget
     */
    public function unlockBudget(Model $budgetable, User $user): bool
    {
        $lock = BudgetLock::getActiveLock($budgetable);
        if ($lock) {
            return $lock->deactivate($user);
        }
        return false;
    }

    /**
     * Check if a budget change is allowed
     */
    public function canModifyBudget(Model $budgetable, ?User $user = null): array
    {
        $lock = BudgetLock::getActiveLock($budgetable);
        
        if (!$lock) {
            return ['allowed' => true, 'message' => null];
        }

        if ($lock->isHardLock()) {
            return [
                'allowed' => false,
                'message' => 'Budget is hard-locked. No modifications allowed.',
                'lock' => $lock,
            ];
        }

        // Soft lock - can be overridden with approval
        return [
            'allowed' => true,
            'requires_approval' => true,
            'message' => 'Budget is soft-locked. Changes require approval.',
            'lock' => $lock,
        ];
    }

    /**
     * Request a budget line allocation change
     */
    public function requestAllocationChange(
        BudgetLine $budgetLine,
        float $newAllocated,
        string $reason,
        string $type = BudgetRevision::TYPE_ALLOCATION_CHANGE
    ): array {
        $budgetable = $budgetLine->budgetable;
        $canModify = $this->canModifyBudget($budgetable);

        if (!$canModify['allowed']) {
            return [
                'success' => false,
                'message' => $canModify['message'],
            ];
        }

        $revision = $budgetLine->requestAllocationChange($newAllocated, $reason, $type);

        // If no approval required (budget not locked), auto-approve
        if (!isset($canModify['requires_approval']) || !$canModify['requires_approval']) {
            // Check if budget line requires approval for changes
            if (!$budgetLine->requires_approval_for_changes) {
                $revision->approve(Auth::user());
                return [
                    'success' => true,
                    'message' => 'Budget allocation updated.',
                    'revision' => $revision,
                    'auto_approved' => true,
                ];
            }
        }

        return [
            'success' => true,
            'message' => 'Budget revision request submitted for approval.',
            'revision' => $revision,
            'auto_approved' => false,
        ];
    }

    /**
     * Approve a budget revision
     */
    public function approveRevision(BudgetRevision $revision, User $approver, ?string $comments = null): bool
    {
        if (!$revision->isPending()) {
            return false;
        }

        return $revision->approve($approver, $comments);
    }

    /**
     * Reject a budget revision
     */
    public function rejectRevision(BudgetRevision $revision, User $approver, ?string $reason = null): bool
    {
        if (!$revision->isPending()) {
            return false;
        }

        return $revision->reject($approver, $reason);
    }

    /**
     * Check budget thresholds and send alerts if needed
     */
    public function checkThresholds(Model $budgetable): array
    {
        $threshold = BudgetThreshold::getOrCreateFor($budgetable);
        $utilization = $budgetable->utilization;
        $level = $threshold->getLevel($utilization);

        $alerts = [];

        // Check if warning should be sent
        if ($threshold->shouldSendWarning($utilization)) {
            $this->notificationService->checkBudgetThreshold($budgetable, $utilization);
            $threshold->markWarningSent();
            $alerts[] = 'warning';
        }

        // Check if critical alert should be sent
        if ($threshold->shouldSendCritical($utilization)) {
            $this->notificationService->checkBudgetThreshold($budgetable, $utilization);
            $threshold->markCriticalSent();
            $alerts[] = 'critical';
        }

        return [
            'utilization' => $utilization,
            'level' => $level,
            'alerts_sent' => $alerts,
            'is_blocked' => $threshold->shouldBlock($utilization),
        ];
    }

    /**
     * Update threshold settings
     */
    public function updateThresholds(
        Model $budgetable,
        array $settings
    ): BudgetThreshold {
        $threshold = BudgetThreshold::getOrCreateFor($budgetable);
        
        $threshold->update([
            'warning_percentage' => $settings['warning_percentage'] ?? $threshold->warning_percentage,
            'critical_percentage' => $settings['critical_percentage'] ?? $threshold->critical_percentage,
            'block_percentage' => $settings['block_percentage'] ?? $threshold->block_percentage,
            'send_warning_alert' => $settings['send_warning_alert'] ?? $threshold->send_warning_alert,
            'send_critical_alert' => $settings['send_critical_alert'] ?? $threshold->send_critical_alert,
            'block_on_exceed' => $settings['block_on_exceed'] ?? $threshold->block_on_exceed,
        ]);

        return $threshold;
    }

    /**
     * Get pending revisions for a user (based on their approval capabilities)
     */
    public function getPendingRevisionsForUser(User $user): Collection
    {
        // TODO: Add role-based filtering when roles are fully implemented
        return BudgetRevision::pending()
            ->with(['budgetLine.budgetable', 'user'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get revision history for a budget
     */
    public function getRevisionHistory(Model $budgetable): Collection
    {
        $budgetableType = get_class($budgetable);
        
        return BudgetRevision::whereHas('budgetLine', function ($query) use ($budgetableType, $budgetable) {
            $query->where('budgetable_type', $budgetableType)
                  ->where('budgetable_id', $budgetable->id);
        })
        ->with(['budgetLine', 'user', 'approver'])
        ->orderBy('created_at', 'desc')
        ->get();
    }

    /**
     * Get lock history for a budget
     */
    public function getLockHistory(Model $budgetable): Collection
    {
        return BudgetLock::where('lockable_type', get_class($budgetable))
            ->where('lockable_id', $budgetable->id)
            ->with(['locker', 'unlocker'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get budget control summary for a budget
     */
    public function getBudgetControlSummary(Model $budgetable): array
    {
        $threshold = BudgetThreshold::getOrCreateFor($budgetable);
        $lock = BudgetLock::getActiveLock($budgetable);

        return [
            'is_locked' => $lock !== null,
            'lock_type' => $lock?->lock_type,
            'lock_reason' => $lock?->reason,
            'locked_by' => $lock?->locker?->name,
            'locked_at' => $lock?->locked_at,
            'utilization' => $budgetable->utilization,
            'threshold_level' => $threshold->getLevel($budgetable->utilization),
            'warning_percentage' => $threshold->warning_percentage,
            'critical_percentage' => $threshold->critical_percentage,
            'block_percentage' => $threshold->block_percentage,
            'is_spending_blocked' => $threshold->shouldBlock($budgetable->utilization),
            'pending_revisions_count' => $budgetable->pending_revisions->count(),
        ];
    }

    /**
     * Reallocate budget between lines
     */
    public function reallocateBudget(
        BudgetLine $fromLine,
        BudgetLine $toLine,
        float $amount,
        string $reason
    ): array {
        // Ensure both lines belong to the same budget
        if ($fromLine->budgetable_type !== $toLine->budgetable_type ||
            $fromLine->budgetable_id !== $toLine->budgetable_id) {
            return [
                'success' => false,
                'message' => 'Cannot reallocate between different budgets.',
            ];
        }

        // Check if amount is available
        if ($amount > $fromLine->available) {
            return [
                'success' => false,
                'message' => 'Insufficient available budget for reallocation.',
            ];
        }

        // Check if budget is locked
        $canModify = $this->canModifyBudget($fromLine->budgetable);
        if (!$canModify['allowed']) {
            return [
                'success' => false,
                'message' => $canModify['message'],
            ];
        }

        // Create revision records for both lines
        $fromRevision = $fromLine->requestAllocationChange(
            $fromLine->allocated - $amount,
            "Reallocation to {$toLine->name}: {$reason}",
            BudgetRevision::TYPE_REALLOCATION
        );

        $toRevision = $toLine->requestAllocationChange(
            $toLine->allocated + $amount,
            "Reallocation from {$fromLine->name}: {$reason}",
            BudgetRevision::TYPE_REALLOCATION
        );

        $requiresApproval = isset($canModify['requires_approval']) && $canModify['requires_approval'];
        
        if (!$requiresApproval && !$fromLine->requires_approval_for_changes && !$toLine->requires_approval_for_changes) {
            // Auto-approve both revisions
            $fromRevision->approve(Auth::user());
            $toRevision->approve(Auth::user());

            return [
                'success' => true,
                'message' => 'Budget reallocation completed.',
                'auto_approved' => true,
            ];
        }

        return [
            'success' => true,
            'message' => 'Budget reallocation request submitted for approval.',
            'auto_approved' => false,
            'from_revision' => $fromRevision,
            'to_revision' => $toRevision,
        ];
    }
}
