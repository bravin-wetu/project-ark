<?php

namespace App\Http\Controllers;

use App\Models\DepartmentBudget;
use App\Models\BudgetLine;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class DepartmentBudgetController extends Controller
{
    /**
     * Display a listing of department budgets.
     */
    public function index(): View
    {
        $budgets = DepartmentBudget::with(['department', 'budgetLines'])
            ->orderByRaw("FIELD(status, 'active', 'draft', 'closed')")
            ->orderBy('fiscal_year', 'desc')
            ->paginate(12);

        return view('department-budgets.index', compact('budgets'));
    }

    /**
     * Show the form for creating a new department budget.
     */
    public function create(): View
    {
        $departments = Department::active()->orderBy('name')->get();
        
        // Generate fiscal year options
        $currentYear = (int) date('Y');
        $fiscalYears = [];
        for ($i = -1; $i <= 2; $i++) {
            $year = $currentYear + $i;
            $fiscalYears[] = "FY{$year}";
        }

        return view('department-budgets.create', compact('departments', 'fiscalYears'));
    }

    /**
     * Store a newly created department budget with budget lines.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'fiscal_year' => 'required|string|max:10',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'currency' => 'required|in:USD,KES,EUR,GBP',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'budget_lines' => 'nullable|array',
            'budget_lines.*.code' => 'nullable|string|max:50',
            'budget_lines.*.name' => 'nullable|string|max:255',
            'budget_lines.*.category_id' => 'nullable|exists:budget_categories,id',
            'budget_lines.*.allocated' => 'nullable|numeric|min:0',
            'action' => 'nullable|in:draft,activate',
        ]);

        // Check for existing budget for this department and fiscal year
        $existing = DepartmentBudget::where('department_id', $validated['department_id'])
            ->where('fiscal_year', $validated['fiscal_year'])
            ->exists();

        if ($existing) {
            return back()
                ->withInput()
                ->withErrors(['fiscal_year' => 'A budget already exists for this department and fiscal year.']);
        }

        DB::transaction(function () use ($validated, $request, &$budget) {
            $budget = DepartmentBudget::create([
                'department_id' => $validated['department_id'],
                'fiscal_year' => $validated['fiscal_year'],
                'name' => $validated['name'] ?? null,
                'description' => $validated['description'] ?? null,
                'currency' => $validated['currency'],
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'status' => 'draft',
            ]);

            // Create budget lines
            $totalBudget = 0;
            if (!empty($validated['budget_lines'])) {
                foreach ($validated['budget_lines'] as $lineData) {
                    if (empty($lineData['code']) && empty($lineData['name'])) {
                        continue;
                    }

                    $allocated = floatval($lineData['allocated'] ?? 0);
                    $totalBudget += $allocated;

                    BudgetLine::create([
                        'budgetable_type' => DepartmentBudget::class,
                        'budgetable_id' => $budget->id,
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
            $budget->update(['total_budget' => $totalBudget]);

            // Activate if requested and has budget lines
            if ($request->input('action') === 'activate' && $budget->budgetLines()->count() > 0) {
                $budget->update(['status' => 'active']);
            }
        });

        $message = $budget->status === 'active' 
            ? 'Department budget created and workspace activated!'
            : 'Department budget saved as draft.';

        return redirect()
            ->route('department-budgets.show', $budget)
            ->with('success', $message);
    }

    /**
     * Display the department budget workspace.
     */
    public function show(DepartmentBudget $departmentBudget): View
    {
        $departmentBudget->load(['department', 'budgetLines.category']);

        // Get recent requisitions (placeholder)
        $recentRequisitions = collect();

        // Get workspace stats
        $stats = [
            'requisitions' => 0,
            'rfqs' => 0,
            'purchase_orders' => 0,
            'receipts' => 0,
        ];

        return view('department-budgets.show', [
            'budget' => $departmentBudget,
            'workspace' => $departmentBudget,
            'workspaceType' => 'department-budgets',
            'recentRequisitions' => $recentRequisitions,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the department budget.
     */
    public function edit(DepartmentBudget $departmentBudget): View
    {
        $departments = Department::active()->orderBy('name')->get();

        return view('department-budgets.edit', compact('departmentBudget', 'departments'));
    }

    /**
     * Update the department budget.
     */
    public function update(Request $request, DepartmentBudget $departmentBudget): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'currency' => 'required|in:USD,KES,EUR,GBP',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $departmentBudget->update($validated);

        return redirect()
            ->route('department-budgets.show', $departmentBudget)
            ->with('success', 'Department budget updated successfully.');
    }

    /**
     * Remove the department budget.
     */
    public function destroy(DepartmentBudget $departmentBudget): RedirectResponse
    {
        $departmentBudget->delete();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Department budget deleted successfully.');
    }
}
