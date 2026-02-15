<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectWorkspaceController extends Controller
{
    /**
     * Budget tracker page - shows all budget lines with utilization
     */
    public function budget(Project $project): View
    {
        $project->load(['budgetLines.category', 'donor', 'department']);
        
        $budgetLines = $project->budgetLines()->with('category')->get();
        
        $stats = [
            'total_allocated' => $budgetLines->sum('allocated'),
            'total_committed' => $budgetLines->sum('committed'),
            'total_spent' => $budgetLines->sum('spent'),
            'total_remaining' => $budgetLines->sum('allocated') - $budgetLines->sum('spent'),
            'lines_count' => $budgetLines->count(),
            'over_budget_count' => $budgetLines->filter(fn($l) => $l->spent > $l->allocated)->count(),
        ];

        return view('projects.budget.index', compact('project', 'budgetLines', 'stats'));
    }

    /**
     * Requisitions listing
     */
    public function requisitions(Project $project): View
    {
        $project->load(['donor', 'department']);
        
        // Requisitions will be implemented in Sprint 4
        $requisitions = collect();
        
        return view('projects.requisitions.index', compact('project', 'requisitions'));
    }

    /**
     * RFQs listing
     */
    public function rfqs(Project $project): View
    {
        $project->load(['donor', 'department']);
        
        // RFQs will be implemented in Sprint 5
        $rfqs = collect();
        
        return view('projects.rfqs.index', compact('project', 'rfqs'));
    }

    /**
     * Quote analysis page
     */
    public function quotes(Project $project): View
    {
        $project->load(['donor', 'department']);
        
        // Quotes will be implemented in Sprint 5
        $quotes = collect();
        
        return view('projects.quotes.index', compact('project', 'quotes'));
    }

    /**
     * Purchase orders listing
     */
    public function purchaseOrders(Project $project): View
    {
        $project->load(['donor', 'department']);
        
        // POs will be implemented in Sprint 6
        $purchaseOrders = collect();
        
        return view('projects.purchase-orders.index', compact('project', 'purchaseOrders'));
    }

    /**
     * Goods receipts listing
     */
    public function receipts(Project $project): View
    {
        $project->load(['donor', 'department']);
        
        // Receipts will be implemented in Sprint 6
        $receipts = collect();
        
        return view('projects.receipts.index', compact('project', 'receipts'));
    }

    /**
     * Assets & stock listing
     */
    public function assets(Project $project): View
    {
        $project->load(['donor', 'department']);
        
        // Assets will be implemented in Sprint 7
        $assets = collect();
        $stocks = collect();
        
        return view('projects.assets.index', compact('project', 'assets', 'stocks'));
    }

    /**
     * Reports dashboard
     */
    public function reports(Project $project): View
    {
        $project->load(['budgetLines.category', 'donor', 'department']);
        
        return view('projects.reports.index', compact('project'));
    }
}
