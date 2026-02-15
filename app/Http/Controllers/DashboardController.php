<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\DepartmentBudget;
use App\Models\BudgetRevision;
use App\Models\BudgetLock;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard.
     */
    public function index(): View
    {
        // Get active projects
        $projects = Project::with(['donor', 'budgetLines'])
            ->orderByRaw("FIELD(status, 'active', 'draft', 'closed', 'suspended')")
            ->orderBy('updated_at', 'desc')
            ->take(9)
            ->get();

        // Get department budgets
        $departmentBudgets = DepartmentBudget::with(['department', 'budgetLines'])
            ->orderByRaw("FIELD(status, 'active', 'draft', 'closed')")
            ->orderBy('updated_at', 'desc')
            ->take(6)
            ->get();

        // Calculate stats
        $activeProjects = Project::where('status', 'active')->get();
        $activeDeptBudgets = DepartmentBudget::where('status', 'active')->get();

        $donorFunds = $activeProjects->sum('allocated');
        $internalBudgets = $activeDeptBudgets->sum('allocated');
        
        $totalSpent = $activeProjects->sum('spent') + $activeDeptBudgets->sum('spent');
        $totalAllocated = $donorFunds + $internalBudgets;
        $totalRemaining = $totalAllocated - $totalSpent;
        
        $utilization = $totalAllocated > 0 
            ? round(($totalSpent / $totalAllocated) * 100, 1) 
            : 0;

        // Budget control stats
        $pendingRevisions = BudgetRevision::pending()->with(['budgetLine', 'user'])->latest()->take(5)->get();
        $pendingRevisionsCount = BudgetRevision::pending()->count();
        $activeLocks = BudgetLock::active()->count();

        $stats = [
            'donor_funds' => $donorFunds,
            'internal_budgets' => $internalBudgets,
            'total_commitments' => $totalSpent,
            'total_remaining' => $totalRemaining,
            'utilization' => $utilization,
            'active_projects' => $activeProjects->count(),
            'active_departments' => $activeDeptBudgets->count(),
            'pending_revisions' => $pendingRevisionsCount,
            'active_locks' => $activeLocks,
        ];

        return view('dashboard', compact('projects', 'departmentBudgets', 'stats', 'pendingRevisions'));
    }
}
