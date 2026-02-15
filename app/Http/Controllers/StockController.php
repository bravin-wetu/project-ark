<?php

namespace App\Http\Controllers;

use App\Models\StockItem;
use App\Models\StockBatch;
use App\Models\StockAdjustment;
use App\Models\StockIssue;
use App\Models\StockIssueItem;
use App\Models\Project;
use App\Models\Hub;
use App\Models\User;
use App\Models\Department;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    /**
     * Display stock for a project
     */
    public function index(Project $project)
    {
        $batches = StockBatch::forProject($project->id)
            ->with(['stockItem', 'hub', 'supplier'])
            ->latest()
            ->paginate(20);

        $stats = [
            'total_batches' => StockBatch::forProject($project->id)->count(),
            'active_batches' => StockBatch::forProject($project->id)->active()->count(),
            'low_stock_items' => 0, // Will calculate if we have stock items
            'expiring_soon' => StockBatch::forProject($project->id)->expiringSoon()->count(),
        ];

        return view('projects.stock.index', compact('project', 'batches', 'stats'));
    }

    /**
     * Show form to create stock batch from receipt item
     */
    public function createFromReceipt(Project $project, GoodsReceipt $receipt, GoodsReceiptItem $item)
    {
        // Verify the receipt belongs to this project
        if ($receipt->purchaseOrder->purchaseable_id !== $project->id) {
            abort(403, 'Receipt does not belong to this project');
        }

        $hubs = Hub::orderBy('name')->get();
        $stockItems = StockItem::active()->orderBy('name')->get();
        $categories = StockItem::getCategories();

        return view('projects.stock.create-from-receipt', compact(
            'project', 'receipt', 'item', 'hubs', 'stockItems', 'categories'
        ));
    }

    /**
     * Store stock batch from receipt item
     */
    public function storeFromReceipt(Request $request, Project $project, GoodsReceipt $receipt, GoodsReceiptItem $item)
    {
        $validated = $request->validate([
            'stock_item_id' => 'nullable|exists:stock_items,id',
            'new_stock_item_name' => 'required_without:stock_item_id|nullable|string|max:255',
            'new_stock_item_category' => 'required_without:stock_item_id|nullable|string',
            'new_stock_item_unit' => 'required_without:stock_item_id|nullable|string|max:50',
            'hub_id' => 'required|exists:hubs,id',
            'storage_location' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
            'lot_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Create new stock item if needed
            if (empty($validated['stock_item_id'])) {
                $stockItem = StockItem::create([
                    'name' => $validated['new_stock_item_name'],
                    'category' => $validated['new_stock_item_category'],
                    'unit' => $validated['new_stock_item_unit'],
                ]);
            } else {
                $stockItem = StockItem::find($validated['stock_item_id']);
            }

            // Create batch
            $batch = StockBatch::createFromReceiptItem($item, $stockItem, [
                'hub_id' => $validated['hub_id'],
                'storage_location' => $validated['storage_location'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'lot_number' => $validated['lot_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            DB::commit();

            return redirect()
                ->route('projects.stock.show', [$project, $batch])
                ->with('success', "Stock batch {$batch->batch_number} created successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create stock batch: ' . $e->getMessage());
        }
    }

    /**
     * Display stock batch details
     */
    public function show(Project $project, StockBatch $batch)
    {
        $batch->load(['stockItem', 'hub', 'supplier', 'purchaseOrder', 'goodsReceipt', 'creator']);
        $adjustments = $batch->adjustments()->with('creator')->latest()->get();

        return view('projects.stock.show', compact('project', 'batch', 'adjustments'));
    }

    /**
     * Show stock issues list
     */
    public function issues(Project $project)
    {
        $issues = StockIssue::forProject($project->id)
            ->with(['hub', 'issuedToUser', 'creator'])
            ->latest()
            ->paginate(20);

        return view('projects.stock.issues', compact('project', 'issues'));
    }

    /**
     * Show form to create stock issue
     */
    public function createIssue(Project $project)
    {
        $hubs = Hub::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        
        // Get available batches for this project
        $batches = StockBatch::forProject($project->id)
            ->active()
            ->where('quantity_available', '>', 0)
            ->with('stockItem')
            ->get();

        return view('projects.stock.create-issue', compact('project', 'hubs', 'users', 'departments', 'batches'));
    }

    /**
     * Store stock issue
     */
    public function storeIssue(Request $request, Project $project)
    {
        $validated = $request->validate([
            'hub_id' => 'required|exists:hubs,id',
            'issued_to' => 'required|exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'issue_date' => 'required|date',
            'purpose' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.stock_batch_id' => 'required|exists:stock_batches,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            $issue = StockIssue::create([
                'issueable_type' => Project::class,
                'issueable_id' => $project->id,
                'hub_id' => $validated['hub_id'],
                'issued_to' => $validated['issued_to'],
                'department_id' => $validated['department_id'] ?? null,
                'issue_date' => $validated['issue_date'],
                'purpose' => $validated['purpose'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => StockIssue::STATUS_DRAFT,
            ]);

            foreach ($validated['items'] as $itemData) {
                $batch = StockBatch::find($itemData['stock_batch_id']);
                
                StockIssueItem::create([
                    'stock_issue_id' => $issue->id,
                    'stock_item_id' => $batch->stock_item_id,
                    'stock_batch_id' => $batch->id,
                    'quantity_requested' => $itemData['quantity'],
                    'unit' => $batch->stockItem->unit,
                    'unit_cost' => $batch->unit_cost,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('projects.stock.issue-show', [$project, $issue])
                ->with('success', "Stock issue {$issue->issue_number} created.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create stock issue: ' . $e->getMessage());
        }
    }

    /**
     * Show stock issue details
     */
    public function showIssue(Project $project, StockIssue $issue)
    {
        $issue->load(['hub', 'issuedToUser', 'department', 'creator', 'items.stockItem', 'items.stockBatch']);

        return view('projects.stock.issue-show', compact('project', 'issue'));
    }

    /**
     * Submit issue for approval
     */
    public function submitIssue(Project $project, StockIssue $issue)
    {
        if (!$issue->submit()) {
            return back()->with('error', 'Issue cannot be submitted.');
        }

        return back()->with('success', 'Issue submitted for approval.');
    }

    /**
     * Approve issue
     */
    public function approveIssue(Project $project, StockIssue $issue)
    {
        if (!$issue->approve()) {
            return back()->with('error', 'Issue cannot be approved.');
        }

        return back()->with('success', 'Issue approved.');
    }

    /**
     * Process issue (deduct from stock)
     */
    public function processIssue(Project $project, StockIssue $issue)
    {
        if (!$issue->issue()) {
            return back()->with('error', 'Failed to process issue. Check stock availability.');
        }

        return back()->with('success', 'Stock issued successfully.');
    }

    /**
     * Cancel issue
     */
    public function cancelIssue(Project $project, StockIssue $issue)
    {
        if (!$issue->cancel()) {
            return back()->with('error', 'Issue cannot be cancelled.');
        }

        return back()->with('success', 'Issue cancelled.');
    }

    /**
     * Show stock movements history
     */
    public function movements(Project $project)
    {
        // Get all stock movements (adjustments + issues)
        $adjustments = StockAdjustment::with(['stockItem', 'hub', 'user'])
            ->whereHas('stockItem', function ($q) use ($project) {
                $q->where('stockable_type', Project::class)
                  ->where('stockable_id', $project->id);
            })
            ->latest('adjustment_date')
            ->get()
            ->map(function ($adj) {
                return [
                    'date' => $adj->adjustment_date,
                    'type' => 'adjustment',
                    'subtype' => $adj->type,
                    'reference' => $adj->reference ?? '—',
                    'item' => $adj->stockItem->name,
                    'quantity' => $adj->quantity,
                    'hub' => $adj->hub?->name ?? '—',
                    'user' => $adj->user?->name ?? '—',
                    'notes' => $adj->reason,
                ];
            });

        $issues = StockIssue::with(['items.stockItem', 'hub', 'issuedToUser'])
            ->where('issueable_type', Project::class)
            ->where('issueable_id', $project->id)
            ->whereIn('status', [StockIssue::STATUS_APPROVED, StockIssue::STATUS_ISSUED])
            ->latest('issue_date')
            ->get()
            ->flatMap(function ($issue) {
                return $issue->items->map(function ($item) use ($issue) {
                    return [
                        'date' => $issue->issue_date,
                        'type' => 'issue',
                        'subtype' => 'issued',
                        'reference' => $issue->issue_number,
                        'item' => $item->stockItem->name,
                        'quantity' => -$item->quantity_issued,
                        'hub' => $issue->hub?->name ?? '—',
                        'user' => $issue->issuedToUser?->name ?? '—',
                        'notes' => $issue->purpose,
                    ];
                });
            });

        $movements = $adjustments->concat($issues)
            ->sortByDesc('date')
            ->values();

        return view('projects.stock.movements', compact('project', 'movements'));
    }
}
