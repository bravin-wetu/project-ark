<?php

namespace App\Http\Controllers;

use App\Models\BudgetLine;
use App\Models\BudgetLock;
use App\Models\BudgetRevision;
use App\Models\DepartmentBudget;
use App\Models\Project;
use App\Services\BudgetControlService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BudgetControlController extends Controller
{
    protected BudgetControlService $budgetControlService;

    public function __construct(BudgetControlService $budgetControlService)
    {
        $this->budgetControlService = $budgetControlService;
    }

    /**
     * Budget control dashboard for a project
     */
    public function projectIndex(Project $project): View
    {
        $summary = $this->budgetControlService->getBudgetControlSummary($project);
        $revisions = $this->budgetControlService->getRevisionHistory($project)->take(10);
        $locks = $this->budgetControlService->getLockHistory($project)->take(5);

        return view('budget-control.project-index', compact('project', 'summary', 'revisions', 'locks'));
    }

    /**
     * Budget control dashboard for a department budget
     */
    public function departmentIndex(DepartmentBudget $departmentBudget): View
    {
        $summary = $this->budgetControlService->getBudgetControlSummary($departmentBudget);
        $revisions = $this->budgetControlService->getRevisionHistory($departmentBudget)->take(10);
        $locks = $this->budgetControlService->getLockHistory($departmentBudget)->take(5);

        return view('budget-control.department-index', compact('departmentBudget', 'summary', 'revisions', 'locks'));
    }

    /**
     * Lock a project budget
     */
    public function lockProject(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'lock_type' => 'required|in:soft,hard',
            'reason' => 'nullable|string|max:500',
            'lock_until' => 'nullable|date|after:today',
        ]);

        $lockUntil = $validated['lock_until'] ? \Carbon\Carbon::parse($validated['lock_until']) : null;

        $this->budgetControlService->lockBudget(
            $project,
            Auth::user(),
            $validated['lock_type'],
            $validated['reason'],
            $lockUntil
        );

        return back()->with('success', 'Project budget has been locked.');
    }

    /**
     * Unlock a project budget
     */
    public function unlockProject(Project $project): RedirectResponse
    {
        $this->budgetControlService->unlockBudget($project, Auth::user());
        return back()->with('success', 'Project budget has been unlocked.');
    }

    /**
     * Lock a department budget
     */
    public function lockDepartment(Request $request, DepartmentBudget $departmentBudget): RedirectResponse
    {
        $validated = $request->validate([
            'lock_type' => 'required|in:soft,hard',
            'reason' => 'nullable|string|max:500',
            'lock_until' => 'nullable|date|after:today',
        ]);

        $lockUntil = $validated['lock_until'] ? \Carbon\Carbon::parse($validated['lock_until']) : null;

        $departmentBudget->lock(
            Auth::user(),
            $validated['lock_type'],
            $validated['reason'],
            $lockUntil
        );

        return back()->with('success', 'Department budget has been locked.');
    }

    /**
     * Unlock a department budget
     */
    public function unlockDepartment(DepartmentBudget $departmentBudget): RedirectResponse
    {
        $departmentBudget->unlock(Auth::user());
        return back()->with('success', 'Department budget has been unlocked.');
    }

    /**
     * Request budget line allocation change
     */
    public function requestChange(Request $request, BudgetLine $budgetLine): RedirectResponse
    {
        $validated = $request->validate([
            'new_allocated' => 'required|numeric|min:0',
            'reason' => 'required|string|max:500',
            'revision_type' => 'required|in:allocation_change,adjustment,correction',
        ]);

        $result = $this->budgetControlService->requestAllocationChange(
            $budgetLine,
            $validated['new_allocated'],
            $validated['reason'],
            $validated['revision_type']
        );

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Request budget reallocation
     */
    public function requestReallocation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'from_budget_line_id' => 'required|exists:budget_lines,id',
            'to_budget_line_id' => 'required|exists:budget_lines,id|different:from_budget_line_id',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:500',
        ]);

        $fromLine = BudgetLine::findOrFail($validated['from_budget_line_id']);
        $toLine = BudgetLine::findOrFail($validated['to_budget_line_id']);

        $result = $this->budgetControlService->reallocateBudget(
            $fromLine,
            $toLine,
            $validated['amount'],
            $validated['reason']
        );

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * List pending budget revisions
     */
    public function pendingRevisions(): View
    {
        $revisions = $this->budgetControlService->getPendingRevisionsForUser(Auth::user());
        return view('budget-control.pending-revisions', compact('revisions'));
    }

    /**
     * Show revision details
     */
    public function showRevision(BudgetRevision $revision): View
    {
        $revision->load(['budgetLine.budgetable', 'user', 'approver']);
        return view('budget-control.show-revision', compact('revision'));
    }

    /**
     * Approve a budget revision
     */
    public function approveRevision(Request $request, BudgetRevision $revision): RedirectResponse
    {
        $validated = $request->validate([
            'comments' => 'nullable|string|max:500',
        ]);

        $success = $this->budgetControlService->approveRevision(
            $revision,
            Auth::user(),
            $validated['comments'] ?? null
        );

        if (!$success) {
            return back()->with('error', 'Failed to approve revision. It may have already been processed.');
        }

        return redirect()
            ->route('budget-control.pending-revisions')
            ->with('success', 'Budget revision approved and changes applied.');
    }

    /**
     * Reject a budget revision
     */
    public function rejectRevision(Request $request, BudgetRevision $revision): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $success = $this->budgetControlService->rejectRevision(
            $revision,
            Auth::user(),
            $validated['rejection_reason']
        );

        if (!$success) {
            return back()->with('error', 'Failed to reject revision. It may have already been processed.');
        }

        return redirect()
            ->route('budget-control.pending-revisions')
            ->with('success', 'Budget revision rejected.');
    }

    /**
     * Update threshold settings for a project
     */
    public function updateProjectThresholds(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'warning_percentage' => 'required|numeric|min:0|max:100',
            'critical_percentage' => 'required|numeric|min:0|max:100|gte:warning_percentage',
            'block_percentage' => 'required|numeric|min:0|max:150|gte:critical_percentage',
            'send_warning_alert' => 'boolean',
            'send_critical_alert' => 'boolean',
            'block_on_exceed' => 'boolean',
        ]);

        $this->budgetControlService->updateThresholds($project, $validated);

        return back()->with('success', 'Threshold settings updated.');
    }

    /**
     * Update threshold settings for a department budget
     */
    public function updateDepartmentThresholds(Request $request, DepartmentBudget $departmentBudget): RedirectResponse
    {
        $validated = $request->validate([
            'warning_percentage' => 'required|numeric|min:0|max:100',
            'critical_percentage' => 'required|numeric|min:0|max:100|gte:warning_percentage',
            'block_percentage' => 'required|numeric|min:0|max:150|gte:critical_percentage',
            'send_warning_alert' => 'boolean',
            'send_critical_alert' => 'boolean',
            'block_on_exceed' => 'boolean',
        ]);

        $this->budgetControlService->updateThresholds($departmentBudget, $validated);

        return back()->with('success', 'Threshold settings updated.');
    }

    /**
     * Get revision history as JSON (for AJAX)
     */
    public function getRevisionHistory(Request $request): JsonResponse
    {
        $type = $request->input('type'); // 'project' or 'department'
        $id = $request->input('id');

        $budgetable = $type === 'project' 
            ? Project::findOrFail($id)
            : DepartmentBudget::findOrFail($id);

        $revisions = $this->budgetControlService->getRevisionHistory($budgetable);

        return response()->json([
            'revisions' => $revisions->map(fn($r) => [
                'id' => $r->id,
                'reference_number' => $r->reference_number,
                'budget_line' => $r->budgetLine->name,
                'revision_type' => $r->revision_type,
                'change_amount' => $r->formatted_change,
                'status' => $r->status,
                'user' => $r->user->name,
                'created_at' => $r->created_at->format('M d, Y H:i'),
            ]),
        ]);
    }
}
