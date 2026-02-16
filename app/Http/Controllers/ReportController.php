<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\DepartmentBudget;
use App\Models\Requisition;
use App\Models\PurchaseOrder;
use App\Models\GoodsReceipt;
use App\Models\Asset;
use App\Models\StockItem;
use App\Models\StockBatch;
use App\Models\Supplier;
use App\Models\BudgetLine;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Reports dashboard/index for a project
     */
    public function index(Project $project)
    {
        // Date range defaults
        $startDate = request('start_date', now()->startOfYear());
        $endDate = request('end_date', now());
        
        // Budget summary
        $budgetSummary = $this->getBudgetSummary($project, $startDate, $endDate);
        
        // Procurement metrics
        $procurementMetrics = $this->getProcurementMetrics($project, $startDate, $endDate);
        
        // Recent activity
        $recentActivity = $this->getRecentActivity($project);
        
        return view('projects.reports.index', compact(
            'project',
            'budgetSummary',
            'procurementMetrics',
            'recentActivity',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Budget utilization report
     */
    public function budget(Project $project)
    {
        $startDate = request('start_date', $project->start_date ?? now()->startOfYear());
        $endDate = request('end_date', $project->end_date ?? now());
        
        // Get all budget lines with spending
        $budgetLines = BudgetLine::where('budgetable_type', Project::class)
            ->where('budgetable_id', $project->id)
            ->with(['purchaseOrderItems.purchaseOrder'])
            ->get()
            ->map(function ($line) {
                $committed = $line->purchaseOrderItems()
                    ->whereHas('purchaseOrder', fn($q) => $q->whereNotIn('status', ['cancelled', 'rejected']))
                    ->sum(DB::raw('quantity * unit_price'));
                
                $spent = $line->purchaseOrderItems()
                    ->whereHas('purchaseOrder', fn($q) => $q->whereIn('status', ['completed', 'closed']))
                    ->sum(DB::raw('quantity * unit_price'));
                
                return [
                    'id' => $line->id,
                    'code' => $line->code,
                    'name' => $line->name,
                    'allocated' => $line->allocated_amount,
                    'committed' => $committed,
                    'spent' => $spent,
                    'available' => $line->allocated_amount - $committed,
                    'utilization' => $line->allocated_amount > 0 
                        ? round(($spent / $line->allocated_amount) * 100, 1) 
                        : 0,
                ];
            });
        
        // Summary totals
        $summary = [
            'total_budget' => $budgetLines->sum('allocated'),
            'total_committed' => $budgetLines->sum('committed'),
            'total_spent' => $budgetLines->sum('spent'),
            'total_available' => $budgetLines->sum('available'),
        ];
        $summary['overall_utilization'] = $summary['total_budget'] > 0 
            ? round(($summary['total_spent'] / $summary['total_budget']) * 100, 1) 
            : 0;
        
        // Monthly spending trend
        $monthlySpending = $this->getMonthlySpending($project, $startDate, $endDate);
        
        return view('projects.reports.budget', compact(
            'project',
            'budgetLines',
            'summary',
            'monthlySpending',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Procurement spend analysis
     */
    public function procurement(Project $project)
    {
        $startDate = request('start_date', now()->startOfYear());
        $endDate = request('end_date', now());
        
        // Spending by supplier
        $spendBySupplier = PurchaseOrder::where('orderable_type', Project::class)
            ->where('orderable_id', $project->id)
            ->whereIn('status', ['sent', 'acknowledged', 'partially_received', 'completed', 'closed'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('supplier')
            ->get()
            ->groupBy('supplier_id')
            ->map(function ($orders, $supplierId) {
                $supplier = $orders->first()->supplier;
                return [
                    'supplier_id' => $supplierId,
                    'supplier_name' => $supplier?->name ?? 'Unknown',
                    'po_count' => $orders->count(),
                    'total_value' => $orders->sum('total_amount'),
                ];
            })
            ->sortByDesc('total_value')
            ->values()
            ->take(10);
        
        // Spending by category (from requisition items)
        $spendByCategory = $this->getSpendByCategory($project, $startDate, $endDate);
        
        // PO status breakdown
        $poStatusBreakdown = PurchaseOrder::where('orderable_type', Project::class)
            ->where('orderable_id', $project->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('count(*) as count'), DB::raw('sum(total_amount) as total'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');
        
        // Average processing times
        $processingTimes = $this->getProcessingTimes($project, $startDate, $endDate);
        
        return view('projects.reports.procurement', compact(
            'project',
            'spendBySupplier',
            'spendByCategory',
            'poStatusBreakdown',
            'processingTimes',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Supplier performance report
     */
    public function suppliers(Project $project)
    {
        $startDate = request('start_date', now()->subYear());
        $endDate = request('end_date', now());
        
        // Get suppliers with performance metrics
        $suppliers = Supplier::whereHas('purchaseOrders', function ($q) use ($project) {
            $q->where('orderable_type', Project::class)
              ->where('orderable_id', $project->id);
        })
        ->with(['purchaseOrders' => function ($q) use ($project, $startDate, $endDate) {
            $q->where('orderable_type', Project::class)
              ->where('orderable_id', $project->id)
              ->whereBetween('created_at', [$startDate, $endDate]);
        }, 'purchaseOrders.goodsReceipts'])
        ->get()
        ->map(function ($supplier) {
            $pos = $supplier->purchaseOrders;
            $completedPOs = $pos->filter(fn($po) => in_array($po->status, ['completed', 'closed']));
            
            // Calculate on-time delivery rate
            $onTimeDeliveries = $completedPOs->filter(function ($po) {
                if (!$po->expected_delivery_date) return true;
                $lastReceipt = $po->goodsReceipts->sortByDesc('received_date')->first();
                return $lastReceipt && $lastReceipt->received_date <= $po->expected_delivery_date;
            })->count();
            
            // Average delivery time
            $deliveryTimes = $completedPOs->map(function ($po) {
                $lastReceipt = $po->goodsReceipts->sortByDesc('received_date')->first();
                if ($lastReceipt && $po->sent_at) {
                    return Carbon::parse($po->sent_at)->diffInDays($lastReceipt->received_date);
                }
                return null;
            })->filter();
            
            return [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'category' => $supplier->category,
                'total_pos' => $pos->count(),
                'completed_pos' => $completedPOs->count(),
                'total_value' => $pos->sum('total_amount'),
                'on_time_rate' => $completedPOs->count() > 0 
                    ? round(($onTimeDeliveries / $completedPOs->count()) * 100, 1) 
                    : null,
                'avg_delivery_days' => $deliveryTimes->count() > 0 
                    ? round($deliveryTimes->avg(), 1) 
                    : null,
            ];
        })
        ->sortByDesc('total_value')
        ->values();
        
        return view('projects.reports.suppliers', compact(
            'project',
            'suppliers',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Asset report
     */
    public function assets(Project $project)
    {
        // Asset summary by status
        $assetsByStatus = Asset::where('assetable_type', Project::class)
            ->where('assetable_id', $project->id)
            ->select('status', DB::raw('count(*) as count'), DB::raw('sum(acquisition_cost) as total_value'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');
        
        // Assets by category
        $assetsByCategory = Asset::where('assetable_type', Project::class)
            ->where('assetable_id', $project->id)
            ->select('category', DB::raw('count(*) as count'), DB::raw('sum(acquisition_cost) as total_value'))
            ->groupBy('category')
            ->orderByDesc('total_value')
            ->get();
        
        // Assets by hub/location
        $assetsByHub = Asset::where('assetable_type', Project::class)
            ->where('assetable_id', $project->id)
            ->with('hub')
            ->select('hub_id', DB::raw('count(*) as count'), DB::raw('sum(acquisition_cost) as total_value'))
            ->groupBy('hub_id')
            ->get()
            ->map(fn($row) => [
                'hub_name' => $row->hub?->name ?? 'Unassigned',
                'count' => $row->count,
                'total_value' => $row->total_value,
            ]);
        
        // Depreciation summary
        $depreciationData = $this->getDepreciationSummary($project);
        
        // Assets needing attention (maintenance due, warranty expiring)
        $attentionNeeded = Asset::where('assetable_type', Project::class)
            ->where('assetable_id', $project->id)
            ->where(function ($q) {
                $q->where('status', 'in_maintenance')
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('warranty_expiry')
                         ->where('warranty_expiry', '<=', now()->addDays(30))
                         ->where('warranty_expiry', '>', now());
                  });
            })
            ->get();
        
        return view('projects.reports.assets', compact(
            'project',
            'assetsByStatus',
            'assetsByCategory',
            'assetsByHub',
            'depreciationData',
            'attentionNeeded'
        ));
    }

    /**
     * Stock/Inventory report
     */
    public function stock(Project $project)
    {
        // Stock value summary
        $stockValue = StockBatch::whereHas('stockItem', function ($q) use ($project) {
            // Stock items linked to project via receipts
        })
        ->where('batchable_type', Project::class)
        ->where('batchable_id', $project->id)
        ->select(
            DB::raw('sum(quantity_available * unit_cost) as total_value'),
            DB::raw('sum(quantity_available) as total_quantity'),
            DB::raw('count(distinct stock_item_id) as item_count')
        )
        ->first();
        
        // Low stock items
        $lowStockItems = StockItem::whereHas('batches', function ($q) use ($project) {
            $q->where('batchable_type', Project::class)
              ->where('batchable_id', $project->id);
        })
        ->get()
        ->filter(fn($item) => $item->isLowStock())
        ->values();
        
        // Expiring items (next 30 days)
        $expiringBatches = StockBatch::where('batchable_type', Project::class)
            ->where('batchable_id', $project->id)
            ->where('quantity_available', '>', 0)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>', now())
            ->with('stockItem')
            ->orderBy('expiry_date')
            ->get();
        
        // Stock movement (last 30 days)
        $stockMovement = $this->getStockMovement($project);
        
        // Stock by category
        $stockByCategory = StockBatch::where('batchable_type', Project::class)
            ->where('batchable_id', $project->id)
            ->join('stock_items', 'stock_batches.stock_item_id', '=', 'stock_items.id')
            ->select(
                'stock_items.category',
                DB::raw('sum(stock_batches.quantity_available * stock_batches.unit_cost) as total_value'),
                DB::raw('count(distinct stock_batches.stock_item_id) as item_count')
            )
            ->groupBy('stock_items.category')
            ->orderByDesc('total_value')
            ->get();
        
        return view('projects.reports.stock', compact(
            'project',
            'stockValue',
            'lowStockItems',
            'expiringBatches',
            'stockMovement',
            'stockByCategory'
        ));
    }

    /**
     * Export report to PDF
     */
    public function exportPdf(Project $project, Request $request)
    {
        $reportType = $request->query('report', 'budget');
        $startDate = $request->query('start_date', $project->start_date ?? now()->startOfYear());
        $endDate = $request->query('end_date', $project->end_date ?? now());
        
        $filename = str_replace(' ', '_', $project->name) . "_{$reportType}_report_" . now()->format('Y-m-d') . ".pdf";
        
        switch ($reportType) {
            case 'budget':
                $pdf = $this->generateBudgetPdf($project, $startDate, $endDate);
                break;
            case 'procurement':
                $pdf = $this->generateProcurementPdf($project, $startDate, $endDate);
                break;
            case 'assets':
                $pdf = $this->generateAssetsPdf($project);
                break;
            default:
                return redirect()->back()->with('error', 'Invalid report type');
        }
        
        return $pdf->download($filename);
    }

    /**
     * Generate Budget PDF
     */
    protected function generateBudgetPdf(Project $project, $startDate, $endDate)
    {
        $budgetLines = BudgetLine::where('budgetable_type', Project::class)
            ->where('budgetable_id', $project->id)
            ->get()
            ->map(function ($line) {
                $line->committed = $line->purchaseOrderItems()
                    ->whereHas('purchaseOrder', fn($q) => $q->whereNotIn('status', ['cancelled', 'rejected']))
                    ->sum(DB::raw('quantity * unit_price'));
                
                $line->spent = $line->purchaseOrderItems()
                    ->whereHas('purchaseOrder', fn($q) => $q->whereIn('status', ['completed', 'closed']))
                    ->sum(DB::raw('quantity * unit_price'));
                
                return $line;
            });
        
        $totals = [
            'allocated' => $budgetLines->sum('allocated_amount'),
            'committed' => $budgetLines->sum('committed'),
            'spent' => $budgetLines->sum('spent'),
            'available' => $budgetLines->sum('allocated_amount') - $budgetLines->sum('committed'),
        ];
        
        return Pdf::loadView('projects.reports.pdf.budget', compact(
            'project', 'budgetLines', 'totals', 'startDate', 'endDate'
        ))->setPaper('a4', 'portrait');
    }

    /**
     * Generate Procurement PDF
     */
    protected function generateProcurementPdf(Project $project, $startDate, $endDate)
    {
        $purchaseOrders = PurchaseOrder::where('purchaseable_type', Project::class)
            ->where('purchaseable_id', $project->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('supplier')
            ->latest()
            ->get();
        
        $stats = [
            'total_requisitions' => Requisition::where('requisitionable_type', Project::class)
                ->where('requisitionable_id', $project->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
            'total_rfqs' => \App\Models\Rfq::where('rfqable_type', Project::class)
                ->where('rfqable_id', $project->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
            'total_pos' => $purchaseOrders->count(),
            'total_spend' => $purchaseOrders->whereIn('status', ['completed', 'closed'])->sum('total_amount'),
        ];
        
        $spendByCategory = PurchaseOrder::where('purchaseable_type', Project::class)
            ->where('purchaseable_id', $project->id)
            ->whereIn('status', ['completed', 'closed'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->join('purchase_order_items', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->join('budget_lines', 'purchase_order_items.budget_line_id', '=', 'budget_lines.id')
            ->select('budget_lines.category', DB::raw('SUM(purchase_order_items.quantity * purchase_order_items.unit_price) as total'))
            ->groupBy('budget_lines.category')
            ->get();
        
        return Pdf::loadView('projects.reports.pdf.procurement', compact(
            'project', 'purchaseOrders', 'stats', 'spendByCategory', 'startDate', 'endDate'
        ))->setPaper('a4', 'portrait');
    }

    /**
     * Generate Assets PDF
     */
    protected function generateAssetsPdf(Project $project)
    {
        $assets = Asset::forProject($project->id)
            ->with(['hub'])
            ->orderBy('asset_tag')
            ->get();
        
        $stats = [
            'total' => $assets->count(),
            'active' => $assets->where('status', 'active')->count(),
            'in_maintenance' => $assets->where('status', 'in_maintenance')->count(),
            'disposed' => $assets->where('status', 'disposed')->count(),
            'total_value' => $assets->sum('acquisition_cost'),
        ];
        
        return Pdf::loadView('projects.reports.pdf.assets', compact(
            'project', 'assets', 'stats'
        ))->setPaper('a4', 'landscape');
    }

    /**
     * Export report to Excel
     */
    public function exportExcel(Project $project, Request $request)
    {
        $reportType = $request->query('report', 'budget');
        $startDate = $request->query('start_date', $project->start_date ?? now()->startOfYear());
        $endDate = $request->query('end_date', $project->end_date ?? now());
        
        $filename = str_replace(' ', '_', $project->name) . "_{$reportType}_report_" . now()->format('Y-m-d') . ".xlsx";
        
        switch ($reportType) {
            case 'budget':
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\BudgetReportExport($project, $startDate, $endDate),
                    $filename
                );
            case 'procurement':
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\ProcurementReportExport($project, $startDate, $endDate),
                    $filename
                );
            case 'assets':
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\AssetsReportExport($project),
                    $filename
                );
            default:
                // Fallback to CSV for other types
                return $this->exportCsv($project, $request);
        }
    }

    /**
     * Export report to CSV (fallback)
     */
    public function exportCsv(Project $project, Request $request)
    {
        $reportType = $request->query('report', 'budget');
        $startDate = $request->query('start_date', now()->startOfYear());
        $endDate = $request->query('end_date', now());
        
        $filename = str_replace(' ', '_', $project->name) . "_{$reportType}_report_" . now()->format('Y-m-d') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function () use ($project, $reportType, $startDate, $endDate) {
            $handle = fopen('php://output', 'w');
            
            switch ($reportType) {
                case 'budget':
                    $this->exportBudgetCsv($handle, $project, $startDate, $endDate);
                    break;
                case 'procurement':
                    $this->exportProcurementCsv($handle, $project, $startDate, $endDate);
                    break;
                case 'suppliers':
                    $this->exportSuppliersCsv($handle, $project, $startDate, $endDate);
                    break;
                case 'assets':
                    $this->exportAssetsCsv($handle, $project);
                    break;
                case 'stock':
                    $this->exportStockCsv($handle, $project);
                    break;
                default:
                    fputcsv($handle, ['Report type not found']);
            }
            
            fclose($handle);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    protected function exportBudgetCsv($handle, Project $project, $startDate, $endDate)
    {
        fputcsv($handle, ['Budget Utilization Report']);
        fputcsv($handle, ['Project: ' . $project->name]);
        fputcsv($handle, ['Period: ' . Carbon::parse($startDate)->format('M d, Y') . ' - ' . Carbon::parse($endDate)->format('M d, Y')]);
        fputcsv($handle, []);
        fputcsv($handle, ['Code', 'Budget Line', 'Allocated', 'Committed', 'Spent', 'Available', 'Utilization %']);
        
        $budgetLines = BudgetLine::where('budgetable_type', Project::class)
            ->where('budgetable_id', $project->id)
            ->get();
        
        foreach ($budgetLines as $line) {
            $committed = $line->purchaseOrderItems()
                ->whereHas('purchaseOrder', fn($q) => $q->whereNotIn('status', ['cancelled', 'rejected']))
                ->sum(DB::raw('quantity * unit_price'));
            
            $spent = $line->purchaseOrderItems()
                ->whereHas('purchaseOrder', fn($q) => $q->whereIn('status', ['completed', 'closed']))
                ->sum(DB::raw('quantity * unit_price'));
            
            fputcsv($handle, [
                $line->code,
                $line->name,
                $line->allocated_amount,
                $committed,
                $spent,
                $line->allocated_amount - $committed,
                $line->allocated_amount > 0 ? round(($spent / $line->allocated_amount) * 100, 1) . '%' : '0%',
            ]);
        }
    }

    protected function exportProcurementCsv($handle, Project $project, $startDate, $endDate)
    {
        fputcsv($handle, ['Procurement Spend Report']);
        fputcsv($handle, ['Project: ' . $project->name]);
        fputcsv($handle, ['Period: ' . Carbon::parse($startDate)->format('M d, Y') . ' - ' . Carbon::parse($endDate)->format('M d, Y')]);
        fputcsv($handle, []);
        fputcsv($handle, ['Supplier', 'PO Count', 'Total Value']);
        
        $spendBySupplier = PurchaseOrder::where('orderable_type', Project::class)
            ->where('orderable_id', $project->id)
            ->whereIn('status', ['sent', 'acknowledged', 'partially_received', 'completed', 'closed'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('supplier')
            ->get()
            ->groupBy('supplier_id');
        
        foreach ($spendBySupplier as $supplierId => $orders) {
            $supplier = $orders->first()->supplier;
            fputcsv($handle, [
                $supplier?->name ?? 'Unknown',
                $orders->count(),
                $orders->sum('total_amount'),
            ]);
        }
    }

    protected function exportSuppliersCsv($handle, Project $project, $startDate, $endDate)
    {
        fputcsv($handle, ['Supplier Performance Report']);
        fputcsv($handle, ['Project: ' . $project->name]);
        fputcsv($handle, []);
        fputcsv($handle, ['Supplier', 'Category', 'Total POs', 'Completed', 'Total Value', 'On-Time Rate %']);
        
        $suppliers = Supplier::whereHas('purchaseOrders', function ($q) use ($project) {
            $q->where('orderable_type', Project::class)
              ->where('orderable_id', $project->id);
        })->with(['purchaseOrders' => function ($q) use ($project) {
            $q->where('orderable_type', Project::class)
              ->where('orderable_id', $project->id);
        }])->get();
        
        foreach ($suppliers as $supplier) {
            $pos = $supplier->purchaseOrders;
            $completedPOs = $pos->filter(fn($po) => in_array($po->status, ['completed', 'closed']));
            
            fputcsv($handle, [
                $supplier->name,
                $supplier->category ?? '-',
                $pos->count(),
                $completedPOs->count(),
                $pos->sum('total_amount'),
                $completedPOs->count() > 0 ? 'N/A' : '-',
            ]);
        }
    }

    protected function exportAssetsCsv($handle, Project $project)
    {
        fputcsv($handle, ['Asset Register Report']);
        fputcsv($handle, ['Project: ' . $project->name]);
        fputcsv($handle, []);
        fputcsv($handle, ['Asset Tag', 'Name', 'Category', 'Status', 'Location', 'Acquisition Cost', 'Acquisition Date']);
        
        $assets = Asset::where('assetable_type', Project::class)
            ->where('assetable_id', $project->id)
            ->with('hub')
            ->get();
        
        foreach ($assets as $asset) {
            fputcsv($handle, [
                $asset->asset_tag,
                $asset->name,
                $asset->category ?? '-',
                ucfirst(str_replace('_', ' ', $asset->status)),
                $asset->hub?->name ?? '-',
                $asset->acquisition_cost,
                $asset->acquisition_date ? Carbon::parse($asset->acquisition_date)->format('Y-m-d') : '-',
            ]);
        }
    }

    protected function exportStockCsv($handle, Project $project)
    {
        fputcsv($handle, ['Stock Inventory Report']);
        fputcsv($handle, ['Project: ' . $project->name]);
        fputcsv($handle, []);
        fputcsv($handle, ['SKU', 'Name', 'Category', 'Unit', 'Current Qty', 'Reorder Level', 'Unit Cost', 'Total Value']);
        
        $stockItems = StockItem::where('stockable_type', Project::class)
            ->where('stockable_id', $project->id)
            ->get();
        
        foreach ($stockItems as $item) {
            fputcsv($handle, [
                $item->sku ?? '-',
                $item->name,
                $item->category ?? '-',
                $item->unit,
                $item->current_quantity,
                $item->reorder_level ?? '-',
                $item->unit_cost ?? '-',
                $item->current_quantity * ($item->unit_cost ?? 0),
            ]);
        }
    }

    // ==================== HELPER METHODS ====================

    protected function getBudgetSummary(Project $project, $startDate, $endDate): array
    {
        $budgetLines = BudgetLine::where('budgetable_type', Project::class)
            ->where('budgetable_id', $project->id)
            ->get();
        
        $totalBudget = $budgetLines->sum('allocated_amount');
        
        $totalCommitted = PurchaseOrder::where('orderable_type', Project::class)
            ->where('orderable_id', $project->id)
            ->whereNotIn('status', ['cancelled', 'rejected', 'draft'])
            ->sum('total_amount');
        
        $totalSpent = PurchaseOrder::where('orderable_type', Project::class)
            ->where('orderable_id', $project->id)
            ->whereIn('status', ['completed', 'closed'])
            ->sum('total_amount');
        
        return [
            'total_budget' => $totalBudget,
            'committed' => $totalCommitted,
            'spent' => $totalSpent,
            'available' => $totalBudget - $totalCommitted,
            'utilization_percent' => $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 1) : 0,
        ];
    }

    protected function getProcurementMetrics(Project $project, $startDate, $endDate): array
    {
        $requisitions = Requisition::where('requisitionable_type', Project::class)
            ->where('requisitionable_id', $project->id)
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        $purchaseOrders = PurchaseOrder::where('orderable_type', Project::class)
            ->where('orderable_id', $project->id)
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        $receipts = GoodsReceipt::whereHas('purchaseOrder', function ($q) use ($project) {
            $q->where('orderable_type', Project::class)
              ->where('orderable_id', $project->id);
        })->whereBetween('created_at', [$startDate, $endDate]);
        
        return [
            'requisitions_count' => $requisitions->count(),
            'requisitions_pending' => (clone $requisitions)->where('status', 'pending_approval')->count(),
            'pos_count' => $purchaseOrders->count(),
            'pos_value' => $purchaseOrders->sum('total_amount'),
            'receipts_count' => $receipts->count(),
            'suppliers_used' => (clone $purchaseOrders)->distinct('supplier_id')->count('supplier_id'),
        ];
    }

    protected function getRecentActivity(Project $project, int $limit = 10): array
    {
        $activities = collect();
        
        // Recent requisitions
        Requisition::where('requisitionable_type', Project::class)
            ->where('requisitionable_id', $project->id)
            ->latest()
            ->take(5)
            ->get()
            ->each(function ($req) use ($activities) {
                $activities->push([
                    'type' => 'requisition',
                    'title' => "Requisition {$req->requisition_number}",
                    'description' => $req->title,
                    'status' => $req->status,
                    'date' => $req->created_at,
                ]);
            });
        
        // Recent POs
        PurchaseOrder::where('orderable_type', Project::class)
            ->where('orderable_id', $project->id)
            ->latest()
            ->take(5)
            ->get()
            ->each(function ($po) use ($activities) {
                $activities->push([
                    'type' => 'purchase_order',
                    'title' => "PO {$po->po_number}",
                    'description' => $po->supplier?->name ?? 'Unknown supplier',
                    'status' => $po->status,
                    'date' => $po->created_at,
                ]);
            });
        
        return $activities->sortByDesc('date')->take($limit)->values()->toArray();
    }

    protected function getMonthlySpending(Project $project, $startDate, $endDate): array
    {
        return PurchaseOrder::where('orderable_type', Project::class)
            ->where('orderable_id', $project->id)
            ->whereIn('status', ['sent', 'acknowledged', 'partially_received', 'completed', 'closed'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('sum(total_amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->mapWithKeys(fn($row) => [$row->month => $row->total])
            ->toArray();
    }

    protected function getSpendByCategory(Project $project, $startDate, $endDate)
    {
        // This would typically come from requisition items or a category field
        // Simplified version using PO data
        return collect([
            ['category' => 'Office Supplies', 'total' => 0],
            ['category' => 'IT Equipment', 'total' => 0],
            ['category' => 'Services', 'total' => 0],
        ]);
    }

    protected function getProcessingTimes(Project $project, $startDate, $endDate): array
    {
        // Average time from requisition to PO
        $reqToPo = Requisition::where('requisitionable_type', Project::class)
            ->where('requisitionable_id', $project->id)
            ->whereNotNull('approved_at')
            ->whereHas('rfqs.quotes.purchaseOrder')
            ->with(['rfqs.quotes.purchaseOrder'])
            ->get()
            ->map(function ($req) {
                $po = $req->rfqs->flatMap->quotes->first()?->purchaseOrder;
                if ($po && $req->approved_at) {
                    return Carbon::parse($req->approved_at)->diffInDays($po->created_at);
                }
                return null;
            })
            ->filter();
        
        return [
            'avg_req_to_po_days' => $reqToPo->count() > 0 ? round($reqToPo->avg(), 1) : null,
        ];
    }

    protected function getDepreciationSummary(Project $project): array
    {
        $assets = Asset::where('assetable_type', Project::class)
            ->where('assetable_id', $project->id)
            ->whereNotNull('useful_life_months')
            ->get();
        
        $totalAcquisition = $assets->sum('acquisition_cost');
        $totalCurrentValue = $assets->sum('current_value');
        $totalDepreciation = $totalAcquisition - $totalCurrentValue;
        
        return [
            'total_acquisition' => $totalAcquisition,
            'total_current_value' => $totalCurrentValue,
            'total_depreciation' => $totalDepreciation,
            'asset_count' => $assets->count(),
        ];
    }

    protected function getStockMovement(Project $project): array
    {
        // Stock received in last 30 days
        $received = StockBatch::where('batchable_type', Project::class)
            ->where('batchable_id', $project->id)
            ->where('received_date', '>=', now()->subDays(30))
            ->sum(DB::raw('quantity_received * unit_cost'));
        
        // Stock issued in last 30 days (from adjustments)
        $issued = 0; // Would come from stock issues
        
        return [
            'received_value' => $received,
            'issued_value' => $issued,
            'net_change' => $received - $issued,
        ];
    }
}
