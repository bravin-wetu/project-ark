<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\BudgetLine;
use App\Models\Hub;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;

class RequisitionController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of requisitions for a project.
     */
    public function index(Project $project): View
    {
        $requisitions = Requisition::where('requisitionable_type', Project::class)
            ->where('requisitionable_id', $project->id)
            ->with(['requester', 'budgetLine', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'total' => Requisition::where('requisitionable_type', Project::class)
                ->where('requisitionable_id', $project->id)->count(),
            'pending' => Requisition::where('requisitionable_type', Project::class)
                ->where('requisitionable_id', $project->id)
                ->where('status', Requisition::STATUS_PENDING_APPROVAL)->count(),
            'approved' => Requisition::where('requisitionable_type', Project::class)
                ->where('requisitionable_id', $project->id)
                ->where('status', Requisition::STATUS_APPROVED)->count(),
            'total_value' => Requisition::where('requisitionable_type', Project::class)
                ->where('requisitionable_id', $project->id)
                ->whereNotIn('status', [Requisition::STATUS_REJECTED, Requisition::STATUS_CANCELLED])
                ->sum('estimated_total'),
        ];

        return view('projects.requisitions.index', compact('project', 'requisitions', 'stats'));
    }

    /**
     * Show the form for creating a new requisition.
     */
    public function create(Project $project): View
    {
        $budgetLines = $project->budgetLines()->active()->with('category')->get();
        $hubs = Hub::active()->orderBy('name')->get();

        return view('projects.requisitions.create', compact('project', 'budgetLines', 'hubs'));
    }

    /**
     * Store a newly created requisition.
     */
    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'justification' => 'nullable|string',
            'budget_line_id' => 'required|exists:budget_lines,id',
            'delivery_hub_id' => 'nullable|exists:hubs,id',
            'required_date' => 'nullable|date|after:today',
            'priority' => 'required|in:low,normal,high,urgent',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.specifications' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string|max:50',
            'items.*.estimated_unit_price' => 'required|numeric|min:0',
            'items.*.item_type' => 'required|in:goods,services,works',
            'action' => 'nullable|in:draft,submit',
        ]);

        // Verify budget line belongs to this project
        $budgetLine = BudgetLine::findOrFail($validated['budget_line_id']);
        if ($budgetLine->budgetable_type !== Project::class || $budgetLine->budgetable_id !== $project->id) {
            return back()->withErrors(['budget_line_id' => 'Invalid budget line selected.']);
        }

        DB::transaction(function () use ($validated, $project, &$requisition, $request) {
            // Create requisition
            $requisition = Requisition::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'justification' => $validated['justification'] ?? null,
                'requisitionable_type' => Project::class,
                'requisitionable_id' => $project->id,
                'budget_line_id' => $validated['budget_line_id'],
                'requested_by' => Auth::id(),
                'delivery_hub_id' => $validated['delivery_hub_id'] ?? null,
                'required_date' => $validated['required_date'] ?? null,
                'priority' => $validated['priority'],
                'currency' => $project->currency ?? 'USD',
                'notes' => $validated['notes'] ?? null,
                'status' => Requisition::STATUS_DRAFT,
            ]);

            // Create items
            foreach ($validated['items'] as $index => $itemData) {
                if (empty($itemData['name'])) continue;

                RequisitionItem::create([
                    'requisition_id' => $requisition->id,
                    'name' => $itemData['name'],
                    'description' => $itemData['description'] ?? null,
                    'specifications' => $itemData['specifications'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'unit' => $itemData['unit'],
                    'estimated_unit_price' => $itemData['estimated_unit_price'],
                    'item_type' => $itemData['item_type'],
                    'sort_order' => $index,
                ]);
            }

            // Submit if requested
            if (($validated['action'] ?? 'draft') === 'submit') {
                $requisition->submitForApproval();
            }
        });

        $message = $requisition->status === Requisition::STATUS_PENDING_APPROVAL
            ? 'Requisition submitted for approval.'
            : 'Requisition saved as draft.';

        return redirect()
            ->route('projects.requisitions.show', [$project, $requisition])
            ->with('success', $message);
    }

    /**
     * Display the specified requisition.
     */
    public function show(Project $project, Requisition $requisition): View
    {
        $requisition->load(['requester', 'approver', 'rejecter', 'budgetLine.category', 'deliveryHub', 'items']);

        return view('projects.requisitions.show', compact('project', 'requisition'));
    }

    /**
     * Show the form for editing a requisition.
     */
    public function edit(Project $project, Requisition $requisition): View
    {
        if (!$requisition->canEdit()) {
            abort(403, 'This requisition cannot be edited.');
        }

        $requisition->load(['items']);
        $budgetLines = $project->budgetLines()->active()->with('category')->get();
        $hubs = Hub::active()->orderBy('name')->get();

        return view('projects.requisitions.edit', compact('project', 'requisition', 'budgetLines', 'hubs'));
    }

    /**
     * Update the specified requisition.
     */
    public function update(Request $request, Project $project, Requisition $requisition): RedirectResponse
    {
        if (!$requisition->canEdit()) {
            abort(403, 'This requisition cannot be edited.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'justification' => 'nullable|string',
            'budget_line_id' => 'required|exists:budget_lines,id',
            'delivery_hub_id' => 'nullable|exists:hubs,id',
            'required_date' => 'nullable|date|after:today',
            'priority' => 'required|in:low,normal,high,urgent',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:requisition_items,id',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.specifications' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string|max:50',
            'items.*.estimated_unit_price' => 'required|numeric|min:0',
            'items.*.item_type' => 'required|in:goods,services,works',
            'action' => 'nullable|in:draft,submit',
        ]);

        DB::transaction(function () use ($validated, $requisition, $request) {
            // Update requisition
            $requisition->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'justification' => $validated['justification'] ?? null,
                'budget_line_id' => $validated['budget_line_id'],
                'delivery_hub_id' => $validated['delivery_hub_id'] ?? null,
                'required_date' => $validated['required_date'] ?? null,
                'priority' => $validated['priority'],
                'notes' => $validated['notes'] ?? null,
                'status' => Requisition::STATUS_DRAFT, // Reset to draft if was rejected
            ]);

            // Get existing item IDs
            $existingIds = collect($validated['items'])
                ->pluck('id')
                ->filter()
                ->toArray();

            // Delete removed items
            $requisition->items()->whereNotIn('id', $existingIds)->delete();

            // Update or create items
            foreach ($validated['items'] as $index => $itemData) {
                if (empty($itemData['name'])) continue;

                if (!empty($itemData['id'])) {
                    // Update existing
                    RequisitionItem::where('id', $itemData['id'])->update([
                        'name' => $itemData['name'],
                        'description' => $itemData['description'] ?? null,
                        'specifications' => $itemData['specifications'] ?? null,
                        'quantity' => $itemData['quantity'],
                        'unit' => $itemData['unit'],
                        'estimated_unit_price' => $itemData['estimated_unit_price'],
                        'item_type' => $itemData['item_type'],
                        'sort_order' => $index,
                    ]);
                } else {
                    // Create new
                    RequisitionItem::create([
                        'requisition_id' => $requisition->id,
                        'name' => $itemData['name'],
                        'description' => $itemData['description'] ?? null,
                        'specifications' => $itemData['specifications'] ?? null,
                        'quantity' => $itemData['quantity'],
                        'unit' => $itemData['unit'],
                        'estimated_unit_price' => $itemData['estimated_unit_price'],
                        'item_type' => $itemData['item_type'],
                        'sort_order' => $index,
                    ]);
                }
            }

            // Recalculate total
            $requisition->recalculateTotal();

            // Submit if requested
            if (($validated['action'] ?? 'draft') === 'submit') {
                $requisition->submitForApproval();
            }
        });

        $message = $requisition->status === Requisition::STATUS_PENDING_APPROVAL
            ? 'Requisition updated and submitted for approval.'
            : 'Requisition updated.';

        return redirect()
            ->route('projects.requisitions.show', [$project, $requisition])
            ->with('success', $message);
    }

    /**
     * Submit requisition for approval.
     */
    public function submit(Project $project, Requisition $requisition): RedirectResponse
    {
        if (!$requisition->canSubmit()) {
            return back()->with('error', 'This requisition cannot be submitted.');
        }

        $requisition->submitForApproval();

        // Send notifications to approvers
        $this->notificationService->notifyRequisitionSubmitted($requisition, Auth::user());

        return back()->with('success', 'Requisition submitted for approval.');
    }

    /**
     * Approve the requisition.
     */
    public function approve(Request $request, Project $project, Requisition $requisition): RedirectResponse
    {
        if (!$requisition->canApprove()) {
            return back()->with('error', 'This requisition cannot be approved.');
        }

        // Check budget availability
        $budgetLine = $requisition->budgetLine;
        if (!$budgetLine->canCommit($requisition->estimated_total)) {
            return back()->with('error', 'Insufficient budget available. Available: $' . number_format($budgetLine->available, 2));
        }

        $requisition->approve(Auth::user());

        // Notify the requester
        $this->notificationService->notifyRequisitionApproved($requisition, Auth::user());

        return back()->with('success', 'Requisition approved successfully.');
    }

    /**
     * Reject the requisition.
     */
    public function reject(Request $request, Project $project, Requisition $requisition): RedirectResponse
    {
        if (!$requisition->canApprove()) {
            return back()->with('error', 'This requisition cannot be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:10',
        ]);

        $requisition->reject(Auth::user(), $validated['rejection_reason']);

        // Notify the requester
        $this->notificationService->notifyRequisitionRejected($requisition, Auth::user(), $validated['rejection_reason']);

        return back()->with('success', 'Requisition rejected.');
    }

    /**
     * Cancel the requisition.
     */
    public function cancel(Project $project, Requisition $requisition): RedirectResponse
    {
        if (!$requisition->cancel()) {
            return back()->with('error', 'This requisition cannot be cancelled.');
        }

        return back()->with('success', 'Requisition cancelled.');
    }

    /**
     * Delete the requisition.
     */
    public function destroy(Project $project, Requisition $requisition): RedirectResponse
    {
        if (!in_array($requisition->status, [Requisition::STATUS_DRAFT, Requisition::STATUS_CANCELLED])) {
            return back()->with('error', 'Only draft or cancelled requisitions can be deleted.');
        }

        $requisition->delete();

        return redirect()
            ->route('projects.requisitions.index', $project)
            ->with('success', 'Requisition deleted.');
    }
}
