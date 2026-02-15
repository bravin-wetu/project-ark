<?php

namespace App\Services;

use App\Models\Project;
use App\Models\DepartmentBudget;
use App\Models\Requisition;
use App\Models\PurchaseOrder;
use App\Models\GoodsReceipt;
use App\Models\Supplier;
use App\Models\BudgetLine;
use App\Models\BudgetRevision;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AnalyticsService
{
    /**
     * Cache duration in seconds (5 minutes)
     */
    protected int $cacheDuration = 300;

    /**
     * Get organization-wide KPIs
     */
    public function getOrganizationKPIs(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfYear();
        $endDate = $endDate ?? now();

        $cacheKey = "org_kpis_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($startDate, $endDate) {
            // Project metrics
            $activeProjects = Project::where('status', 'active')->get();
            $projectBudget = $activeProjects->sum('allocated');
            $projectSpent = $activeProjects->sum('spent');

            // Department metrics
            $activeDepartments = DepartmentBudget::where('status', 'active')->get();
            $departmentBudget = $activeDepartments->sum('allocated');
            $departmentSpent = $activeDepartments->sum('spent');

            // Procurement metrics
            $totalPOs = PurchaseOrder::whereBetween('created_at', [$startDate, $endDate])->count();
            $totalPOValue = PurchaseOrder::whereBetween('created_at', [$startDate, $endDate])
                ->whereNotIn('status', ['cancelled', 'rejected'])
                ->sum('total_amount');

            $totalRequisitions = Requisition::whereBetween('created_at', [$startDate, $endDate])->count();
            $approvedRequisitions = Requisition::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'approved')
                ->count();

            // Supplier metrics
            $activeSuppliers = Supplier::where('is_active', true)->count();

            // Calculate overall utilization
            $totalBudget = $projectBudget + $departmentBudget;
            $totalSpent = $projectSpent + $departmentSpent;
            $utilization = $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 1) : 0;

            return [
                'total_budget' => $totalBudget,
                'total_spent' => $totalSpent,
                'total_available' => $totalBudget - $totalSpent,
                'utilization_percent' => $utilization,
                'project_count' => $activeProjects->count(),
                'project_budget' => $projectBudget,
                'project_spent' => $projectSpent,
                'department_count' => $activeDepartments->count(),
                'department_budget' => $departmentBudget,
                'department_spent' => $departmentSpent,
                'total_pos' => $totalPOs,
                'total_po_value' => $totalPOValue,
                'total_requisitions' => $totalRequisitions,
                'approved_requisitions' => $approvedRequisitions,
                'requisition_approval_rate' => $totalRequisitions > 0 
                    ? round(($approvedRequisitions / $totalRequisitions) * 100, 1) 
                    : 0,
                'active_suppliers' => $activeSuppliers,
            ];
        });
    }

    /**
     * Get monthly spending trend data
     */
    public function getMonthlySpendingTrend(?Carbon $startDate = null, ?Carbon $endDate = null): Collection
    {
        $startDate = $startDate ?? now()->subMonths(11)->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $cacheKey = "spending_trend_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($startDate, $endDate) {
            // Get PO values by month
            $poSpending = PurchaseOrder::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('SUM(total_amount) as amount')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->groupBy('month')
            ->pluck('amount', 'month');

            // Generate all months in range
            $period = CarbonPeriod::create($startDate, '1 month', $endDate);
            $result = collect();

            foreach ($period as $date) {
                $monthKey = $date->format('Y-m');
                $result->push([
                    'month' => $date->format('M Y'),
                    'month_key' => $monthKey,
                    'amount' => $poSpending->get($monthKey, 0),
                ]);
            }

            return $result;
        });
    }

    /**
     * Get budget utilization by project
     */
    public function getBudgetUtilizationByProject(int $limit = 10): Collection
    {
        return Cache::remember('budget_util_by_project', $this->cacheDuration, function () use ($limit) {
            return Project::where('status', 'active')
                ->where('allocated', '>', 0)
                ->orderByDesc('allocated')
                ->take($limit)
                ->get()
                ->map(function ($project) {
                    $utilization = $project->allocated > 0 
                        ? round(($project->spent / $project->allocated) * 100, 1) 
                        : 0;
                    return [
                        'id' => $project->id,
                        'name' => $project->name,
                        'code' => $project->code,
                        'allocated' => $project->allocated,
                        'spent' => $project->spent,
                        'available' => $project->allocated - $project->spent,
                        'utilization' => $utilization,
                        'status' => $this->getUtilizationStatus($utilization),
                    ];
                });
        });
    }

    /**
     * Get budget utilization by department
     */
    public function getBudgetUtilizationByDepartment(int $limit = 10): Collection
    {
        return Cache::remember('budget_util_by_dept', $this->cacheDuration, function () use ($limit) {
            return DepartmentBudget::where('status', 'active')
                ->where('allocated', '>', 0)
                ->with('department')
                ->orderByDesc('allocated')
                ->take($limit)
                ->get()
                ->map(function ($budget) {
                    $utilization = $budget->allocated > 0 
                        ? round(($budget->spent / $budget->allocated) * 100, 1) 
                        : 0;
                    return [
                        'id' => $budget->id,
                        'name' => $budget->department->name ?? 'Unknown',
                        'fiscal_year' => $budget->fiscal_year,
                        'allocated' => $budget->allocated,
                        'spent' => $budget->spent,
                        'available' => $budget->allocated - $budget->spent,
                        'utilization' => $utilization,
                        'status' => $this->getUtilizationStatus($utilization),
                    ];
                });
        });
    }

    /**
     * Get spending by category
     */
    public function getSpendingByCategory(?Carbon $startDate = null, ?Carbon $endDate = null): Collection
    {
        $startDate = $startDate ?? now()->startOfYear();
        $endDate = $endDate ?? now();

        $cacheKey = "spending_by_cat_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($startDate, $endDate) {
            return DB::table('purchase_order_items')
                ->join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
                ->select(
                    'purchase_order_items.category',
                    DB::raw('SUM(purchase_order_items.quantity * purchase_order_items.unit_price) as total')
                )
                ->whereBetween('purchase_orders.created_at', [$startDate, $endDate])
                ->whereNotIn('purchase_orders.status', ['cancelled', 'rejected'])
                ->whereNotNull('purchase_order_items.category')
                ->groupBy('purchase_order_items.category')
                ->orderByDesc('total')
                ->get()
                ->map(function ($item) {
                    return [
                        'category' => $item->category ?: 'Uncategorized',
                        'total' => $item->total,
                    ];
                });
        });
    }

    /**
     * Get top suppliers by spend
     */
    public function getTopSuppliersBySpend(?Carbon $startDate = null, ?Carbon $endDate = null, int $limit = 10): Collection
    {
        $startDate = $startDate ?? now()->startOfYear();
        $endDate = $endDate ?? now();

        $cacheKey = "top_suppliers_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}_{$limit}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($startDate, $endDate, $limit) {
            return Supplier::select('suppliers.*')
                ->selectRaw('SUM(purchase_orders.total_amount) as total_spend')
                ->selectRaw('COUNT(DISTINCT purchase_orders.id) as po_count')
                ->join('purchase_orders', 'suppliers.id', '=', 'purchase_orders.supplier_id')
                ->whereBetween('purchase_orders.created_at', [$startDate, $endDate])
                ->whereNotIn('purchase_orders.status', ['cancelled', 'rejected'])
                ->groupBy('suppliers.id')
                ->orderByDesc('total_spend')
                ->take($limit)
                ->get()
                ->map(function ($supplier) {
                    return [
                        'id' => $supplier->id,
                        'name' => $supplier->name,
                        'category' => $supplier->category,
                        'total_spend' => $supplier->total_spend,
                        'po_count' => $supplier->po_count,
                    ];
                });
        });
    }

    /**
     * Get procurement pipeline metrics
     */
    public function getProcurementPipeline(): array
    {
        return Cache::remember('procurement_pipeline', $this->cacheDuration, function () {
            // Requisition status breakdown
            $requisitionStats = Requisition::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            // PO status breakdown
            $poStats = PurchaseOrder::select('status', DB::raw('count(*) as count'), DB::raw('SUM(total_amount) as value'))
                ->groupBy('status')
                ->get()
                ->keyBy('status')
                ->map(fn($item) => ['count' => $item->count, 'value' => $item->value])
                ->toArray();

            // Processing times
            $avgRequisitionProcessingDays = $this->calculateAverageProcessingTime(Requisition::class);
            $avgPOProcessingDays = $this->calculateAverageProcessingTime(PurchaseOrder::class);

            return [
                'requisitions' => [
                    'draft' => $requisitionStats['draft'] ?? 0,
                    'submitted' => $requisitionStats['submitted'] ?? 0,
                    'pending' => $requisitionStats['pending'] ?? 0,
                    'approved' => $requisitionStats['approved'] ?? 0,
                    'rejected' => $requisitionStats['rejected'] ?? 0,
                    'cancelled' => $requisitionStats['cancelled'] ?? 0,
                ],
                'purchase_orders' => $poStats,
                'avg_requisition_processing_days' => $avgRequisitionProcessingDays,
                'avg_po_processing_days' => $avgPOProcessingDays,
            ];
        });
    }

    /**
     * Get approval turnaround metrics
     */
    public function getApprovalMetrics(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subMonths(3);
        $endDate = $endDate ?? now();

        $cacheKey = "approval_metrics_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($startDate, $endDate) {
            // Budget revision approvals
            $revisionMetrics = BudgetRevision::whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('COUNT(*) as total'),
                    DB::raw('SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved'),
                    DB::raw('SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected'),
                    DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending')
                )
                ->first();

            // Requisition approvals
            $requisitionMetrics = Requisition::whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('COUNT(*) as total'),
                    DB::raw('SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved'),
                    DB::raw('SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected'),
                    DB::raw('SUM(CASE WHEN status IN ("pending", "submitted") THEN 1 ELSE 0 END) as pending')
                )
                ->first();

            return [
                'budget_revisions' => [
                    'total' => $revisionMetrics->total ?? 0,
                    'approved' => $revisionMetrics->approved ?? 0,
                    'rejected' => $revisionMetrics->rejected ?? 0,
                    'pending' => $revisionMetrics->pending ?? 0,
                    'approval_rate' => $revisionMetrics->total > 0 
                        ? round((($revisionMetrics->approved ?? 0) / $revisionMetrics->total) * 100, 1) 
                        : 0,
                ],
                'requisitions' => [
                    'total' => $requisitionMetrics->total ?? 0,
                    'approved' => $requisitionMetrics->approved ?? 0,
                    'rejected' => $requisitionMetrics->rejected ?? 0,
                    'pending' => $requisitionMetrics->pending ?? 0,
                    'approval_rate' => $requisitionMetrics->total > 0 
                        ? round((($requisitionMetrics->approved ?? 0) / $requisitionMetrics->total) * 100, 1) 
                        : 0,
                ],
            ];
        });
    }

    /**
     * Get comparison data for year-over-year analysis
     */
    public function getYearOverYearComparison(): array
    {
        return Cache::remember('yoy_comparison', $this->cacheDuration, function () {
            $currentYear = now()->year;
            $previousYear = $currentYear - 1;

            // Current year spending
            $currentYearSpend = PurchaseOrder::whereYear('created_at', $currentYear)
                ->whereNotIn('status', ['cancelled', 'rejected'])
                ->sum('total_amount');

            // Previous year spending
            $previousYearSpend = PurchaseOrder::whereYear('created_at', $previousYear)
                ->whereNotIn('status', ['cancelled', 'rejected'])
                ->sum('total_amount');

            // Current year requisitions
            $currentYearReqs = Requisition::whereYear('created_at', $currentYear)->count();
            $previousYearReqs = Requisition::whereYear('created_at', $previousYear)->count();

            // Calculate percentage changes
            $spendChange = $previousYearSpend > 0 
                ? round((($currentYearSpend - $previousYearSpend) / $previousYearSpend) * 100, 1) 
                : 0;
            $reqsChange = $previousYearReqs > 0 
                ? round((($currentYearReqs - $previousYearReqs) / $previousYearReqs) * 100, 1) 
                : 0;

            return [
                'current_year' => $currentYear,
                'previous_year' => $previousYear,
                'spending' => [
                    'current' => $currentYearSpend,
                    'previous' => $previousYearSpend,
                    'change_percent' => $spendChange,
                    'trend' => $spendChange >= 0 ? 'up' : 'down',
                ],
                'requisitions' => [
                    'current' => $currentYearReqs,
                    'previous' => $previousYearReqs,
                    'change_percent' => $reqsChange,
                    'trend' => $reqsChange >= 0 ? 'up' : 'down',
                ],
            ];
        });
    }

    /**
     * Get data for budget vs actual chart
     */
    public function getBudgetVsActualByMonth(?Carbon $startDate = null, ?Carbon $endDate = null): Collection
    {
        $startDate = $startDate ?? now()->startOfYear();
        $endDate = $endDate ?? now()->endOfMonth();

        $cacheKey = "budget_vs_actual_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($startDate, $endDate) {
            // Get monthly budgets (simplified - using total allocated divided by months)
            $totalBudget = Project::where('status', 'active')->sum('allocated') + 
                           DepartmentBudget::where('status', 'active')->sum('allocated');

            $period = CarbonPeriod::create($startDate, '1 month', $endDate);
            $monthCount = iterator_count($period);
            $monthlyBudget = $monthCount > 0 ? $totalBudget / $monthCount : 0;

            // Get monthly spending
            $monthlySpending = PurchaseOrder::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('SUM(total_amount) as spent')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->groupBy('month')
            ->pluck('spent', 'month');

            $result = collect();
            $period = CarbonPeriod::create($startDate, '1 month', $endDate);

            foreach ($period as $date) {
                $monthKey = $date->format('Y-m');
                $result->push([
                    'month' => $date->format('M Y'),
                    'month_key' => $monthKey,
                    'budget' => round($monthlyBudget, 2),
                    'actual' => $monthlySpending->get($monthKey, 0),
                ]);
            }

            return $result;
        });
    }

    /**
     * Get recent activity for dashboard
     */
    public function getRecentActivity(int $limit = 20): Collection
    {
        return Cache::remember("recent_activity_{$limit}", 60, function () use ($limit) {
            $activities = collect();

            // Recent requisitions
            Requisition::with(['user', 'budgetable'])
                ->latest()
                ->take($limit)
                ->get()
                ->each(function ($req) use ($activities) {
                    $activities->push([
                        'type' => 'requisition',
                        'title' => "Requisition {$req->requisition_number}",
                        'description' => "{$req->user->name} created a requisition",
                        'status' => $req->status,
                        'amount' => $req->estimated_total,
                        'date' => $req->created_at,
                        'url' => route('requisitions.show', $req),
                    ]);
                });

            // Recent POs
            PurchaseOrder::with(['user', 'supplier'])
                ->latest()
                ->take($limit)
                ->get()
                ->each(function ($po) use ($activities) {
                    $activities->push([
                        'type' => 'purchase_order',
                        'title' => "PO {$po->po_number}",
                        'description' => "PO to {$po->supplier->name}",
                        'status' => $po->status,
                        'amount' => $po->total_amount,
                        'date' => $po->created_at,
                        'url' => route('purchase-orders.show', $po),
                    ]);
                });

            return $activities->sortByDesc('date')->take($limit)->values();
        });
    }

    /**
     * Export report data to CSV format
     */
    public function exportToCsv(string $reportType, ?Carbon $startDate = null, ?Carbon $endDate = null): string
    {
        $startDate = $startDate ?? now()->startOfYear();
        $endDate = $endDate ?? now();

        switch ($reportType) {
            case 'budget_utilization':
                return $this->exportBudgetUtilization();
            case 'spending_by_category':
                return $this->exportSpendingByCategory($startDate, $endDate);
            case 'supplier_analysis':
                return $this->exportSupplierAnalysis($startDate, $endDate);
            case 'monthly_spending':
                return $this->exportMonthlySpending($startDate, $endDate);
            default:
                return '';
        }
    }

    /**
     * Calculate average processing time for a model type
     */
    protected function calculateAverageProcessingTime(string $modelClass): float
    {
        if ($modelClass === Requisition::class) {
            $avg = Requisition::whereIn('status', ['approved', 'rejected'])
                ->whereNotNull('updated_at')
                ->selectRaw('AVG(DATEDIFF(updated_at, created_at)) as avg_days')
                ->value('avg_days');
        } elseif ($modelClass === PurchaseOrder::class) {
            $avg = PurchaseOrder::whereIn('status', ['completed', 'closed'])
                ->whereNotNull('updated_at')
                ->selectRaw('AVG(DATEDIFF(updated_at, created_at)) as avg_days')
                ->value('avg_days');
        } else {
            $avg = 0;
        }

        return round($avg ?? 0, 1);
    }

    /**
     * Get utilization status based on percentage
     */
    protected function getUtilizationStatus(float $utilization): string
    {
        if ($utilization >= 100) {
            return 'over_budget';
        } elseif ($utilization >= 90) {
            return 'near_limit';
        } elseif ($utilization >= 75) {
            return 'on_track';
        } else {
            return 'under_utilized';
        }
    }

    /**
     * Export budget utilization to CSV
     */
    protected function exportBudgetUtilization(): string
    {
        $projects = $this->getBudgetUtilizationByProject(100);
        $departments = $this->getBudgetUtilizationByDepartment(100);

        $csv = "Type,Name,Allocated,Spent,Available,Utilization %,Status\n";

        foreach ($projects as $project) {
            $csv .= "Project,\"{$project['name']}\",{$project['allocated']},{$project['spent']},{$project['available']},{$project['utilization']},{$project['status']}\n";
        }

        foreach ($departments as $dept) {
            $csv .= "Department,\"{$dept['name']}\",{$dept['allocated']},{$dept['spent']},{$dept['available']},{$dept['utilization']},{$dept['status']}\n";
        }

        return $csv;
    }

    /**
     * Export spending by category to CSV
     */
    protected function exportSpendingByCategory(Carbon $startDate, Carbon $endDate): string
    {
        $data = $this->getSpendingByCategory($startDate, $endDate);

        $csv = "Category,Total Spend\n";
        foreach ($data as $item) {
            $csv .= "\"{$item['category']}\",{$item['total']}\n";
        }

        return $csv;
    }

    /**
     * Export supplier analysis to CSV
     */
    protected function exportSupplierAnalysis(Carbon $startDate, Carbon $endDate): string
    {
        $data = $this->getTopSuppliersBySpend($startDate, $endDate, 100);

        $csv = "Supplier,Category,Total Spend,PO Count\n";
        foreach ($data as $item) {
            $csv .= "\"{$item['name']}\",\"{$item['category']}\",{$item['total_spend']},{$item['po_count']}\n";
        }

        return $csv;
    }

    /**
     * Export monthly spending to CSV
     */
    protected function exportMonthlySpending(Carbon $startDate, Carbon $endDate): string
    {
        $data = $this->getMonthlySpendingTrend($startDate, $endDate);

        $csv = "Month,Spending\n";
        foreach ($data as $item) {
            $csv .= "\"{$item['month']}\",{$item['amount']}\n";
        }

        return $csv;
    }

    /**
     * Clear all analytics cache
     */
    public function clearCache(): void
    {
        $keys = [
            'org_kpis_*',
            'spending_trend_*',
            'budget_util_by_project',
            'budget_util_by_dept',
            'spending_by_cat_*',
            'top_suppliers_*',
            'procurement_pipeline',
            'approval_metrics_*',
            'yoy_comparison',
            'budget_vs_actual_*',
            'recent_activity_*',
        ];

        // Note: For production, use Cache::tags() or pattern-based deletion
        Cache::flush();
    }
}
