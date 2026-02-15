<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Quote;
use App\Models\Supplier;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of purchase orders for a project.
     */
    public function index(Project $project)
    {
        $purchaseOrders = $project->purchaseOrders()
            ->with(['supplier', 'items', 'creator'])
            ->latest()
            ->paginate(15);

        $stats = [
            'total' => $project->purchaseOrders()->count(),
            'pending_approval' => $project->purchaseOrders()->where('status', PurchaseOrder::STATUS_PENDING_APPROVAL)->count(),
            'approved' => $project->purchaseOrders()->where('status', PurchaseOrder::STATUS_APPROVED)->count(),
            'sent' => $project->purchaseOrders()->where('status', PurchaseOrder::STATUS_SENT)->count(),
            'received' => $project->purchaseOrders()->whereIn('status', [
                PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
                PurchaseOrder::STATUS_RECEIVED
            ])->count(),
            'total_value' => $project->purchaseOrders()
                ->whereNotIn('status', [PurchaseOrder::STATUS_DRAFT, PurchaseOrder::STATUS_CANCELLED])
                ->sum('total_amount'),
        ];

        return view('projects.purchase-orders.index', compact('project', 'purchaseOrders', 'stats'));
    }

    /**
     * Show the form for creating a new purchase order from a quote.
     */
    public function createFromQuote(Project $project, Quote $quote)
    {
        // Ensure quote is awarded
        if (!$quote->isAwarded()) {
            return back()->with('error', 'Only awarded quotes can be converted to purchase orders.');
        }

        // Check if PO already exists for this quote
        if (PurchaseOrder::where('quote_id', $quote->id)->exists()) {
            return back()->with('error', 'A purchase order already exists for this quote.');
        }

        $quote->load(['supplier', 'items', 'rfq.requisition.items']);
        
        $budgetLines = $project->budgetLines()
            ->where('status', 'active')
            ->get();

        return view('projects.purchase-orders.create', compact('project', 'quote', 'budgetLines'));
    }

    /**
     * Store a newly created purchase order.
     */
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'quote_id' => 'required|exists:quotes,id',
            'budget_line_id' => 'nullable|exists:budget_lines,id',
            'delivery_address' => 'required|string',
            'delivery_hub_id' => 'nullable|exists:hubs,id',
            'expected_delivery_date' => 'required|date|after:today',
            'payment_terms' => 'nullable|string|max:500',
            'shipping_method' => 'nullable|string|max:100',
            'shipping_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.quote_item_id' => 'required|exists:quote_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        $quote = Quote::with(['rfq', 'supplier', 'items'])->findOrFail($validated['quote_id']);

        if (!$quote->isAwarded()) {
            return back()->with('error', 'Only awarded quotes can be converted to purchase orders.');
        }

        DB::beginTransaction();
        try {
            $purchaseOrder = PurchaseOrder::create([
                'purchaseable_type' => Project::class,
                'purchaseable_id' => $project->id,
                'rfq_id' => $quote->rfq_id,
                'quote_id' => $quote->id,
                'requisition_id' => $quote->rfq->requisition_id,
                'supplier_id' => $quote->supplier_id,
                'budget_line_id' => $validated['budget_line_id'] ?? null,
                'delivery_address' => $validated['delivery_address'],
                'delivery_hub_id' => $validated['delivery_hub_id'] ?? null,
                'expected_delivery_date' => $validated['expected_delivery_date'],
                'payment_terms' => $validated['payment_terms'] ?? null,
                'shipping_method' => $validated['shipping_method'] ?? null,
                'shipping_amount' => $validated['shipping_amount'] ?? 0,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'tax_amount' => $quote->tax_total ?? 0,
                'notes' => $validated['notes'] ?? null,
                'terms_conditions' => $validated['terms_conditions'] ?? null,
                'status' => PurchaseOrder::STATUS_DRAFT,
            ]);

            // Create PO items from quote items
            foreach ($validated['items'] as $itemData) {
                $quoteItem = $quote->items->where('id', $itemData['quote_item_id'])->first();
                
                if ($quoteItem) {
                    $purchaseOrder->items()->create([
                        'quote_item_id' => $quoteItem->id,
                        'requisition_item_id' => $quoteItem->requisition_item_id,
                        'name' => $quoteItem->name,
                        'description' => $quoteItem->description,
                        'specifications' => $quoteItem->specifications,
                        'quantity' => $itemData['quantity'],
                        'unit' => $quoteItem->unit,
                        'unit_price' => $quoteItem->unit_price,
                        'status' => PurchaseOrderItem::STATUS_PENDING,
                    ]);
                }
            }

            $purchaseOrder->recalculateTotals();

            DB::commit();

            return redirect()
                ->route('projects.purchase-orders.show', [$project, $purchaseOrder])
                ->with('success', 'Purchase order created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create purchase order: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified purchase order.
     */
    public function show(Project $project, PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'items.quoteItem',
            'quote',
            'rfq',
            'requisition',
            'budgetLine',
            'receipts.items',
            'creator',
            'approver',
        ]);

        $receiptStats = [
            'total_receipts' => $purchaseOrder->receipts->count(),
            'items_received' => $purchaseOrder->items->sum('received_quantity'),
            'items_ordered' => $purchaseOrder->items->sum('quantity'),
            'receipt_percentage' => $purchaseOrder->items->sum('quantity') > 0
                ? round(($purchaseOrder->items->sum('received_quantity') / $purchaseOrder->items->sum('quantity')) * 100, 1)
                : 0,
        ];

        return view('projects.purchase-orders.show', compact('project', 'purchaseOrder', 'receiptStats'));
    }

    /**
     * Submit PO for approval.
     */
    public function submit(Project $project, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->isDraft()) {
            return back()->with('error', 'Only draft purchase orders can be submitted for approval.');
        }

        if ($purchaseOrder->items->isEmpty()) {
            return back()->with('error', 'Purchase order must have at least one item.');
        }

        try {
            $purchaseOrder->submitForApproval();
            return back()->with('success', 'Purchase order submitted for approval.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Approve the purchase order.
     */
    public function approve(Request $request, Project $project, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->isPendingApproval()) {
            return back()->with('error', 'This purchase order is not pending approval.');
        }

        $validated = $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $purchaseOrder->approve($validated['approval_notes'] ?? null);
            
            // Send notifications
            $this->notificationService->notifyPurchaseOrderApproved($purchaseOrder, Auth::user());
            
            return back()->with('success', 'Purchase order approved successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject the purchase order.
     */
    public function reject(Request $request, Project $project, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->isPendingApproval()) {
            return back()->with('error', 'This purchase order is not pending approval.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        try {
            $purchaseOrder->reject($validated['rejection_reason']);
            return back()->with('success', 'Purchase order rejected.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Send the purchase order to supplier.
     */
    public function send(Project $project, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->isApproved()) {
            return back()->with('error', 'Only approved purchase orders can be sent.');
        }

        try {
            $purchaseOrder->send();
            
            // Send notifications
            $this->notificationService->notifyPurchaseOrderSent($purchaseOrder);
            
            return back()->with('success', 'Purchase order marked as sent to supplier.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Record supplier acknowledgment.
     */
    public function acknowledge(Request $request, Project $project, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->isSent()) {
            return back()->with('error', 'Only sent purchase orders can be acknowledged.');
        }

        $validated = $request->validate([
            'supplier_reference' => 'nullable|string|max:100',
        ]);

        try {
            $purchaseOrder->acknowledge($validated['supplier_reference'] ?? null);
            return back()->with('success', 'Supplier acknowledgment recorded.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel the purchase order.
     */
    public function cancel(Request $request, Project $project, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:1000',
        ]);

        try {
            $purchaseOrder->cancel($validated['cancellation_reason']);
            return back()->with('success', 'Purchase order cancelled.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Close a completed purchase order.
     */
    public function close(Project $project, PurchaseOrder $purchaseOrder)
    {
        try {
            $purchaseOrder->close();
            return back()->with('success', 'Purchase order closed.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Print/export purchase order.
     */
    public function print(Project $project, PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'items',
            'quote',
            'requisition',
        ]);

        return view('projects.purchase-orders.print', compact('project', 'purchaseOrder'));
    }
}
