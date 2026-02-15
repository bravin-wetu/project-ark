<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoodsReceiptController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of goods receipts for a project.
     */
    public function index(Project $project)
    {
        $receipts = GoodsReceipt::whereHas('purchaseOrder', function ($q) use ($project) {
            $q->where('purchaseable_type', Project::class)
              ->where('purchaseable_id', $project->id);
        })
        ->with(['purchaseOrder.supplier', 'creator', 'items'])
        ->latest('received_at')
        ->paginate(15);

        $stats = [
            'total' => $receipts->total(),
            'draft' => GoodsReceipt::whereHas('purchaseOrder', fn($q) => 
                $q->where('purchaseable_type', Project::class)->where('purchaseable_id', $project->id)
            )->where('status', 'draft')->count(),
            'confirmed' => GoodsReceipt::whereHas('purchaseOrder', fn($q) => 
                $q->where('purchaseable_type', Project::class)->where('purchaseable_id', $project->id)
            )->whereIn('status', ['confirmed', 'complete'])->count(),
        ];

        return view('projects.goods-receipts.index', compact('project', 'receipts', 'stats'));
    }

    /**
     * Show the form for creating a new goods receipt.
     */
    public function create(Project $project, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canReceiveGoods()) {
            return back()->with('error', 'This purchase order cannot receive goods at this time.');
        }

        $purchaseOrder->load(['supplier', 'items']);
        
        // Get items with remaining quantities
        $receivableItems = $purchaseOrder->items->filter(fn($item) => $item->remaining_quantity > 0);

        if ($receivableItems->isEmpty()) {
            return back()->with('error', 'All items have already been received.');
        }

        return view('projects.goods-receipts.create', compact('project', 'purchaseOrder', 'receivableItems'));
    }

    /**
     * Store a newly created goods receipt.
     */
    public function store(Request $request, Project $project, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'delivery_note_number' => 'nullable|string|max:100',
            'invoice_number' => 'nullable|string|max:100',
            'received_by' => 'required|string|max:100',
            'received_at' => 'required|date',
            'receiving_location' => 'nullable|string|max:200',
            'overall_condition' => 'required|in:excellent,good,acceptable,damaged,rejected',
            'notes' => 'nullable|string',
            'discrepancy_notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.received_quantity' => 'required|numeric|min:0',
            'items.*.accepted_quantity' => 'required|numeric|min:0',
            'items.*.rejected_quantity' => 'nullable|numeric|min:0',
            'items.*.condition' => 'required|in:excellent,good,acceptable,damaged,rejected',
            'items.*.rejection_reason' => 'nullable|string',
            'items.*.storage_location' => 'nullable|string|max:100',
            'items.*.batch_number' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $receipt = GoodsReceipt::create([
                'purchase_order_id' => $purchaseOrder->id,
                'delivery_note_number' => $validated['delivery_note_number'] ?? null,
                'invoice_number' => $validated['invoice_number'] ?? null,
                'received_by' => $validated['received_by'],
                'received_at' => $validated['received_at'],
                'receiving_location' => $validated['receiving_location'] ?? null,
                'overall_condition' => $validated['overall_condition'],
                'notes' => $validated['notes'] ?? null,
                'discrepancy_notes' => $validated['discrepancy_notes'] ?? null,
                'status' => GoodsReceipt::STATUS_DRAFT,
            ]);

            foreach ($validated['items'] as $itemData) {
                if ($itemData['received_quantity'] > 0 || $itemData['accepted_quantity'] > 0) {
                    $poItem = $purchaseOrder->items->where('id', $itemData['purchase_order_item_id'])->first();
                    
                    $receipt->items()->create([
                        'purchase_order_item_id' => $itemData['purchase_order_item_id'],
                        'expected_quantity' => $poItem->remaining_quantity,
                        'received_quantity' => $itemData['received_quantity'],
                        'accepted_quantity' => $itemData['accepted_quantity'],
                        'rejected_quantity' => $itemData['rejected_quantity'] ?? 0,
                        'condition' => $itemData['condition'],
                        'rejection_reason' => $itemData['rejection_reason'] ?? null,
                        'storage_location' => $itemData['storage_location'] ?? null,
                        'batch_number' => $itemData['batch_number'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('projects.purchase-orders.receipts.show', [$project, $purchaseOrder, $receipt])
                ->with('success', 'Goods receipt created as draft. Review and confirm to update inventory.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create goods receipt: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified goods receipt.
     */
    public function show(Project $project, PurchaseOrder $purchaseOrder, GoodsReceipt $receipt)
    {
        $receipt->load([
            'purchaseOrder.supplier',
            'items.purchaseOrderItem',
            'creator',
            'confirmer',
        ]);

        return view('projects.goods-receipts.show', compact('project', 'purchaseOrder', 'receipt'));
    }

    /**
     * Confirm the goods receipt.
     */
    public function confirm(Project $project, PurchaseOrder $purchaseOrder, GoodsReceipt $receipt)
    {
        if (!$receipt->isDraft()) {
            return back()->with('error', 'Only draft receipts can be confirmed.');
        }

        if ($receipt->items->isEmpty()) {
            return back()->with('error', 'Receipt must have at least one item.');
        }

        try {
            $receipt->confirm();
            
            // Send notification
            $this->notificationService->notifyGoodsReceived($receipt, $purchaseOrder);
            
            return back()->with('success', 'Goods receipt confirmed and inventory updated.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel the goods receipt.
     */
    public function cancel(Project $project, PurchaseOrder $purchaseOrder, GoodsReceipt $receipt)
    {
        if (!$receipt->isDraft()) {
            return back()->with('error', 'Only draft receipts can be cancelled.');
        }

        try {
            $receipt->cancel();
            return redirect()
                ->route('projects.purchase-orders.show', [$project, $purchaseOrder])
                ->with('success', 'Goods receipt cancelled.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
