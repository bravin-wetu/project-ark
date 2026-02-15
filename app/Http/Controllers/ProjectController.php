<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\BudgetLine;
use App\Models\Donor;
use App\Models\Department;
use App\Models\Hub;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects.
     */
    public function index(): View
    {
        $projects = Project::with(['donor', 'department', 'budgetLines'])
            ->orderByRaw("FIELD(status, 'active', 'draft', 'closed', 'suspended')")
            ->orderBy('updated_at', 'desc')
            ->paginate(12);

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create(): View
    {
        $donors = Donor::active()->orderBy('name')->get();
        $departments = Department::active()->orderBy('name')->get();
        $hubs = Hub::active()->orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('projects.create', compact('donors', 'departments', 'hubs', 'users'));
    }

    /**
     * Store a newly created project with budget lines.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:projects,code',
            'description' => 'nullable|string',
            'donor_id' => 'required|exists:donors,id',
            'department_id' => 'required|exists:departments,id',
            'project_manager_id' => 'nullable|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'currency' => 'required|in:USD,KES,EUR,GBP',
            'hubs' => 'nullable|array',
            'hubs.*' => 'exists:hubs,id',
            'budget_lines' => 'nullable|array',
            'budget_lines.*.code' => 'nullable|string|max:50',
            'budget_lines.*.name' => 'nullable|string|max:255',
            'budget_lines.*.category_id' => 'nullable|exists:budget_categories,id',
            'budget_lines.*.allocated' => 'nullable|numeric|min:0',
            'action' => 'nullable|in:draft,activate',
        ]);

        DB::transaction(function () use ($validated, $request, &$project) {
            // Create project
            $project = Project::create([
                'name' => $validated['name'],
                'code' => strtoupper($validated['code']),
                'description' => $validated['description'] ?? null,
                'donor_id' => $validated['donor_id'],
                'department_id' => $validated['department_id'],
                'project_manager_id' => $validated['project_manager_id'] ?? null,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'currency' => $validated['currency'],
                'status' => 'draft',
            ]);

            // Sync hubs
            if (!empty($validated['hubs'])) {
                $project->hubs()->sync($validated['hubs']);
            }

            // Create budget lines
            $totalBudget = 0;
            if (!empty($validated['budget_lines'])) {
                foreach ($validated['budget_lines'] as $lineData) {
                    // Skip empty lines
                    if (empty($lineData['code']) && empty($lineData['name'])) {
                        continue;
                    }

                    $allocated = floatval($lineData['allocated'] ?? 0);
                    $totalBudget += $allocated;

                    BudgetLine::create([
                        'budgetable_type' => Project::class,
                        'budgetable_id' => $project->id,
                        'code' => $lineData['code'] ?? null,
                        'name' => $lineData['name'] ?? 'Unnamed Line',
                        'budget_category_id' => $lineData['category_id'] ?? null,
                        'allocated' => $allocated,
                        'committed' => 0,
                        'spent' => 0,
                        'is_active' => true,
                    ]);
                }
            }

            // Update total budget
            $project->update(['total_budget' => $totalBudget]);

            // Activate if requested and has budget lines
            if ($request->input('action') === 'activate' && $project->budgetLines()->count() > 0) {
                $project->update(['status' => 'active']);
            }
        });

        $message = $project->status === 'active' 
            ? 'Project created and workspace activated successfully!'
            : 'Project saved as draft. Add budget lines to activate.';

        return redirect()
            ->route('projects.show', $project)
            ->with('success', $message);
    }

    /**
     * Display the project workspace.
     */
    public function show(Project $project): View
    {
        $project->load(['donor', 'department', 'projectManager', 'hubs', 'budgetLines.category']);

        // Get recent requisitions (placeholder - will be implemented in Sprint 4)
        $recentRequisitions = collect();

        // Get workspace stats
        $stats = [
            'requisitions' => 0,
            'rfqs' => 0,
            'purchase_orders' => 0,
            'receipts' => 0,
        ];

        return view('projects.show', [
            'project' => $project,
            'workspace' => $project,
            'workspaceType' => 'projects',
            'recentRequisitions' => $recentRequisitions,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing project.
     */
    public function edit(Project $project): View
    {
        $donors = Donor::active()->orderBy('name')->get();
        $departments = Department::active()->orderBy('name')->get();
        $hubs = Hub::active()->orderBy('name')->get();

        return view('projects.edit', compact('project', 'donors', 'departments', 'hubs'));
    }

    /**
     * Update the project.
     */
    public function update(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:projects,code,' . $project->id,
            'description' => 'nullable|string',
            'donor_id' => 'required|exists:donors,id',
            'department_id' => 'required|exists:departments,id',
            'project_manager_id' => 'nullable|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'currency' => 'required|in:USD,KES,EUR,GBP',
            'hubs' => 'nullable|array',
            'hubs.*' => 'exists:hubs,id',
        ]);

        $project->update($validated);

        if (isset($validated['hubs'])) {
            $project->hubs()->sync($validated['hubs']);
        }

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the project.
     */
    public function destroy(Project $project): RedirectResponse
    {
        // Soft delete
        $project->delete();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Project deleted successfully.');
    }
}
