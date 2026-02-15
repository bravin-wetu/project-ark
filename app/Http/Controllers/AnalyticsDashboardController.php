<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AnalyticsDashboardController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Main analytics dashboard
     */
    public function index(Request $request): View
    {
        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date) 
            : now()->startOfYear();
        $endDate = $request->filled('end_date') 
            ? Carbon::parse($request->end_date) 
            : now();

        // Get KPIs and metrics
        $kpis = $this->analyticsService->getOrganizationKPIs($startDate, $endDate);
        $monthlySpending = $this->analyticsService->getMonthlySpendingTrend($startDate, $endDate);
        $yoyComparison = $this->analyticsService->getYearOverYearComparison();
        $procurementPipeline = $this->analyticsService->getProcurementPipeline();
        $recentActivity = $this->analyticsService->getRecentActivity(10);

        return view('analytics.dashboard', compact(
            'kpis',
            'monthlySpending',
            'yoyComparison',
            'procurementPipeline',
            'recentActivity',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Budget utilization report
     */
    public function budgetUtilization(Request $request): View
    {
        $projectUtilization = $this->analyticsService->getBudgetUtilizationByProject(20);
        $departmentUtilization = $this->analyticsService->getBudgetUtilizationByDepartment(20);

        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date) 
            : now()->startOfYear();
        $endDate = $request->filled('end_date') 
            ? Carbon::parse($request->end_date) 
            : now();

        $budgetVsActual = $this->analyticsService->getBudgetVsActualByMonth($startDate, $endDate);

        return view('analytics.budget-utilization', compact(
            'projectUtilization',
            'departmentUtilization',
            'budgetVsActual',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Spending analysis report
     */
    public function spendingAnalysis(Request $request): View
    {
        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date) 
            : now()->startOfYear();
        $endDate = $request->filled('end_date') 
            ? Carbon::parse($request->end_date) 
            : now();

        $spendingByCategory = $this->analyticsService->getSpendingByCategory($startDate, $endDate);
        $monthlyTrend = $this->analyticsService->getMonthlySpendingTrend($startDate, $endDate);
        $topSuppliers = $this->analyticsService->getTopSuppliersBySpend($startDate, $endDate, 10);
        $yoyComparison = $this->analyticsService->getYearOverYearComparison();

        return view('analytics.spending-analysis', compact(
            'spendingByCategory',
            'monthlyTrend',
            'topSuppliers',
            'yoyComparison',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Procurement analytics
     */
    public function procurementAnalytics(Request $request): View
    {
        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date) 
            : now()->startOfYear();
        $endDate = $request->filled('end_date') 
            ? Carbon::parse($request->end_date) 
            : now();

        $pipeline = $this->analyticsService->getProcurementPipeline();
        $approvalMetrics = $this->analyticsService->getApprovalMetrics($startDate, $endDate);
        $topSuppliers = $this->analyticsService->getTopSuppliersBySpend($startDate, $endDate, 15);

        return view('analytics.procurement', compact(
            'pipeline',
            'approvalMetrics',
            'topSuppliers',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Supplier performance analytics
     */
    public function supplierAnalytics(Request $request): View
    {
        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date) 
            : now()->startOfYear();
        $endDate = $request->filled('end_date') 
            ? Carbon::parse($request->end_date) 
            : now();

        $topSuppliers = $this->analyticsService->getTopSuppliersBySpend($startDate, $endDate, 25);
        $spendingByCategory = $this->analyticsService->getSpendingByCategory($startDate, $endDate);

        return view('analytics.suppliers', compact(
            'topSuppliers',
            'spendingByCategory',
            'startDate',
            'endDate'
        ));
    }

    /**
     * API endpoint: Get chart data for dashboard
     */
    public function chartData(Request $request): \Illuminate\Http\JsonResponse
    {
        $chartType = $request->get('chart', 'monthly_spending');
        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date) 
            : now()->startOfYear();
        $endDate = $request->filled('end_date') 
            ? Carbon::parse($request->end_date) 
            : now();

        $data = match ($chartType) {
            'monthly_spending' => $this->analyticsService->getMonthlySpendingTrend($startDate, $endDate),
            'budget_vs_actual' => $this->analyticsService->getBudgetVsActualByMonth($startDate, $endDate),
            'spending_by_category' => $this->analyticsService->getSpendingByCategory($startDate, $endDate),
            'top_suppliers' => $this->analyticsService->getTopSuppliersBySpend($startDate, $endDate, 10),
            'project_utilization' => $this->analyticsService->getBudgetUtilizationByProject(10),
            'department_utilization' => $this->analyticsService->getBudgetUtilizationByDepartment(10),
            default => collect(),
        };

        return response()->json([
            'success' => true,
            'data' => $data,
            'chart_type' => $chartType,
        ]);
    }

    /**
     * Export report to CSV
     */
    public function export(Request $request): Response
    {
        $reportType = $request->get('type', 'budget_utilization');
        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date) 
            : now()->startOfYear();
        $endDate = $request->filled('end_date') 
            ? Carbon::parse($request->end_date) 
            : now();

        $csv = $this->analyticsService->exportToCsv($reportType, $startDate, $endDate);
        $filename = "{$reportType}_" . now()->format('Y-m-d') . ".csv";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Clear analytics cache
     */
    public function clearCache(): \Illuminate\Http\RedirectResponse
    {
        $this->analyticsService->clearCache();

        return back()->with('success', 'Analytics cache cleared successfully.');
    }

    /**
     * Get KPIs via JSON for AJAX refresh
     */
    public function kpisJson(Request $request): \Illuminate\Http\JsonResponse
    {
        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date) 
            : now()->startOfYear();
        $endDate = $request->filled('end_date') 
            ? Carbon::parse($request->end_date) 
            : now();

        $kpis = $this->analyticsService->getOrganizationKPIs($startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => $kpis,
        ]);
    }
}
