<?php

namespace App\Http\Controllers;

use App\Models\DepartmentBudget;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentBudgetWorkspaceController extends Controller
{
    /**
     * Budget tracker page - shows all budget lines with utilization
     */
    public function budget(DepartmentBudget $departmentBudget): View
    {
        $departmentBudget->load(['budgetLines.category', 'department']);
        
        $budgetLines = $departmentBudget->budgetLines()->with('category')->get();
        
        $stats = [
            'total_allocated' => $budgetLines->sum('allocated'),
            'total_committed' => $budgetLines->sum('committed'),
            'total_spent' => $budgetLines->sum('spent'),
            'total_remaining' => $budgetLines->sum('allocated') - $budgetLines->sum('spent'),
            'lines_count' => $budgetLines->count(),
            'over_budget_count' => $budgetLines->filter(fn($l) => $l->spent > $l->allocated)->count(),
        ];

        return view('department-budgets.budget.index', compact('departmentBudget', 'budgetLines', 'stats'));
    }

    /**
     * Requisitions listing
     */
    public function requisitions(DepartmentBudget $departmentBudget): View
    {
        $departmentBudget->load(['department']);
        $requisitions = collect();
        
        return view('department-budgets.requisitions.index', compact('departmentBudget', 'requisitions'));
    }

    /**
     * RFQs listing
     */
    public function rfqs(DepartmentBudget $departmentBudget): View
    {
        $departmentBudget->load(['department']);
        $rfqs = collect();
        
        return view('department-budgets.rfqs.index', compact('departmentBudget', 'rfqs'));
    }

    /**
     * Quote analysis page
     */
    public function quotes(DepartmentBudget $departmentBudget): View
    {
        $departmentBudget->load(['department']);
        $quotes = collect();
        
        return view('department-budgets.quotes.index', compact('departmentBudget', 'quotes'));
    }

    /**
     * Purchase orders listing
     */
    public function purchaseOrders(DepartmentBudget $departmentBudget): View
    {
        $departmentBudget->load(['department']);
        $purchaseOrders = collect();
        
        return view('department-budgets.purchase-orders.index', compact('departmentBudget', 'purchaseOrders'));
    }

    /**
     * Goods receipts listing
     */
    public function receipts(DepartmentBudget $departmentBudget): View
    {
        $departmentBudget->load(['department']);
        $receipts = collect();
        
        return view('department-budgets.receipts.index', compact('departmentBudget', 'receipts'));
    }

    /**
     * Assets & stock listing
     */
    public function assets(DepartmentBudget $departmentBudget): View
    {
        $departmentBudget->load(['department']);
        $assets = collect();
        $stocks = collect();
        
        return view('department-budgets.assets.index', compact('departmentBudget', 'assets', 'stocks'));
    }

    /**
     * Reports dashboard
     */
    public function reports(DepartmentBudget $departmentBudget): View
    {
        $departmentBudget->load(['budgetLines.category', 'department']);
        
        return view('department-budgets.reports.index', compact('departmentBudget'));
    }
}
