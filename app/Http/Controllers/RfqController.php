<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Rfq;
use App\Models\Quote;
use App\Models\Supplier;
use App\Models\Requisition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RfqController extends Controller
{
    /**
     * Display a listing of RFQs
     */
    public function index(Project $project)
    {
        $rfqs = $project->rfqs()
            ->with(['requisition', 'awardedSupplier', 'creator'])
            ->withCount(['suppliers', 'quotes'])
            ->latest()
            ->paginate(15);

        $stats = [
            'total' => $project->rfqs()->count(),
            'draft' => $project->rfqs()->where('status', Rfq::STATUS_DRAFT)->count(),
            'open' => $project->rfqs()->whereIn('status', [Rfq::STATUS_SENT, Rfq::STATUS_QUOTES_RECEIVED])->count(),
            'awarded' => $project->rfqs()->where('status', Rfq::STATUS_AWARDED)->count(),
        ];

        return view('projects.rfqs.index', compact('project', 'rfqs', 'stats'));
    }

    /**
     * Show the form for creating a new RFQ
     */
    public function create(Project $project, Request $request)
    {
        $requisitionId = $request->get('requisition_id');
        $requisition = null;

        if ($requisitionId) {
            $requisition = Requisition::with('items')
                ->where('requisitionable_type', Project::class)
                ->where('requisitionable_id', $project->id)
                ->where('status', Requisition::STATUS_APPROVED)
                ->findOrFail($requisitionId);
        }

        // Get approved requisitions without RFQs
        $availableRequisitions = Requisition::where('requisitionable_type', Project::class)
            ->where('requisitionable_id', $project->id)
            ->where('status', Requisition::STATUS_APPROVED)
            ->whereDoesntHave('rfqs')
            ->with('items')
            ->get();

        // Get active suppliers
        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('projects.rfqs.create', compact('project', 'requisition', 'availableRequisitions', 'suppliers'));
    }

    /**
     * Store a newly created RFQ
     */
    public function store(Project $project, Request $request)
    {
        $validated = $request->validate([
            'requisition_id' => 'required|exists:requisitions,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'closing_date' => 'required|date|after:today',
            'delivery_date' => 'nullable|date|after:closing_date',
            'terms_and_conditions' => 'nullable|string',
            'submission_instructions' => 'nullable|string',
            'evaluation_criteria' => 'nullable|string',
            'min_quotes' => 'required|integer|min:1|max:10',
            'supplier_ids' => 'required|array|min:1',
            'supplier_ids.*' => 'exists:suppliers,id',
        ]);

        // Verify requisition belongs to this project and is approved
        $requisition = Requisition::where('requisitionable_type', Project::class)
            ->where('requisitionable_id', $project->id)
            ->where('status', Requisition::STATUS_APPROVED)
            ->findOrFail($validated['requisition_id']);

        DB::transaction(function () use ($project, $validated, $requisition) {
            $rfq = Rfq::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'requisition_id' => $requisition->id,
                'rfqable_type' => Project::class,
                'rfqable_id' => $project->id,
                'closing_date' => $validated['closing_date'],
                'delivery_date' => $validated['delivery_date'],
                'terms_and_conditions' => $validated['terms_and_conditions'],
                'submission_instructions' => $validated['submission_instructions'],
                'evaluation_criteria' => $validated['evaluation_criteria'],
                'min_quotes' => $validated['min_quotes'],
            ]);

            // Attach suppliers
            $rfq->suppliers()->attach($validated['supplier_ids']);

            // Update requisition status
            $requisition->update(['status' => Requisition::STATUS_IN_PROGRESS]);
        });

        return redirect()
            ->route('projects.rfqs.index', $project)
            ->with('success', 'RFQ created successfully.');
    }

    /**
     * Display the specified RFQ
     */
    public function show(Project $project, Rfq $rfq)
    {
        $this->authorizeRfq($project, $rfq);

        $rfq->load([
            'requisition.items',
            'suppliers',
            'quotes.supplier',
            'quotes.items',
            'awardedQuote.supplier',
            'creator',
        ]);

        return view('projects.rfqs.show', compact('project', 'rfq'));
    }

    /**
     * Show the form for editing the RFQ
     */
    public function edit(Project $project, Rfq $rfq)
    {
        $this->authorizeRfq($project, $rfq);

        if (!$rfq->canEdit()) {
            return redirect()
                ->route('projects.rfqs.show', [$project, $rfq])
                ->with('error', 'This RFQ cannot be edited.');
        }

        $rfq->load(['requisition.items', 'suppliers']);
        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('projects.rfqs.edit', compact('project', 'rfq', 'suppliers'));
    }

    /**
     * Update the specified RFQ
     */
    public function update(Project $project, Rfq $rfq, Request $request)
    {
        $this->authorizeRfq($project, $rfq);

        if (!$rfq->canEdit()) {
            return redirect()
                ->route('projects.rfqs.show', [$project, $rfq])
                ->with('error', 'This RFQ cannot be edited.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'closing_date' => 'required|date|after:today',
            'delivery_date' => 'nullable|date|after:closing_date',
            'terms_and_conditions' => 'nullable|string',
            'submission_instructions' => 'nullable|string',
            'evaluation_criteria' => 'nullable|string',
            'min_quotes' => 'required|integer|min:1|max:10',
            'supplier_ids' => 'required|array|min:1',
            'supplier_ids.*' => 'exists:suppliers,id',
        ]);

        DB::transaction(function () use ($rfq, $validated) {
            $rfq->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'closing_date' => $validated['closing_date'],
                'delivery_date' => $validated['delivery_date'],
                'terms_and_conditions' => $validated['terms_and_conditions'],
                'submission_instructions' => $validated['submission_instructions'],
                'evaluation_criteria' => $validated['evaluation_criteria'],
                'min_quotes' => $validated['min_quotes'],
            ]);

            // Sync suppliers
            $rfq->suppliers()->sync($validated['supplier_ids']);
        });

        return redirect()
            ->route('projects.rfqs.show', [$project, $rfq])
            ->with('success', 'RFQ updated successfully.');
    }

    /**
     * Send RFQ to suppliers
     */
    public function send(Project $project, Rfq $rfq)
    {
        $this->authorizeRfq($project, $rfq);

        if (!$rfq->canSend()) {
            return redirect()
                ->route('projects.rfqs.show', [$project, $rfq])
                ->with('error', 'This RFQ cannot be sent. Please add at least one supplier.');
        }

        $rfq->send();

        return redirect()
            ->route('projects.rfqs.show', [$project, $rfq])
            ->with('success', 'RFQ sent to suppliers successfully.');
    }

    /**
     * Show quote analysis/comparison
     */
    public function analyze(Project $project, Rfq $rfq)
    {
        $this->authorizeRfq($project, $rfq);

        if (!$rfq->canEvaluate()) {
            return redirect()
                ->route('projects.rfqs.show', [$project, $rfq])
                ->with('error', 'No quotes available for analysis.');
        }

        $rfq->load([
            'requisition.items',
            'quotes.supplier',
            'quotes.items.requisitionItem',
        ]);

        // Start evaluation if not already
        if ($rfq->status === Rfq::STATUS_QUOTES_RECEIVED) {
            $rfq->startEvaluation();
        }

        return view('projects.rfqs.analyze', compact('project', 'rfq'));
    }

    /**
     * Award the RFQ to a supplier
     */
    public function award(Project $project, Rfq $rfq, Request $request)
    {
        $this->authorizeRfq($project, $rfq);

        $validated = $request->validate([
            'quote_id' => 'required|exists:quotes,id',
            'justification' => 'nullable|string|max:1000',
        ]);

        $quote = Quote::where('rfq_id', $rfq->id)->findOrFail($validated['quote_id']);

        if (!$rfq->canAward()) {
            return redirect()
                ->route('projects.rfqs.analyze', [$project, $rfq])
                ->with('error', 'This RFQ cannot be awarded yet.');
        }

        $rfq->award($quote, $validated['justification']);

        return redirect()
            ->route('projects.rfqs.show', [$project, $rfq])
            ->with('success', 'RFQ awarded to ' . $quote->supplier->name . ' successfully.');
    }

    /**
     * Cancel the RFQ
     */
    public function cancel(Project $project, Rfq $rfq, Request $request)
    {
        $this->authorizeRfq($project, $rfq);

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        if ($rfq->isAwarded()) {
            return redirect()
                ->route('projects.rfqs.show', [$project, $rfq])
                ->with('error', 'Cannot cancel an awarded RFQ.');
        }

        $rfq->cancel($validated['reason'] ?? null);

        // Reset requisition status back to approved
        $rfq->requisition->update(['status' => Requisition::STATUS_APPROVED]);

        return redirect()
            ->route('projects.rfqs.index', $project)
            ->with('success', 'RFQ cancelled successfully.');
    }

    /**
     * Delete the RFQ
     */
    public function destroy(Project $project, Rfq $rfq)
    {
        $this->authorizeRfq($project, $rfq);

        if (!$rfq->isDraft()) {
            return redirect()
                ->route('projects.rfqs.index', $project)
                ->with('error', 'Only draft RFQs can be deleted.');
        }

        $rfq->delete();

        return redirect()
            ->route('projects.rfqs.index', $project)
            ->with('success', 'RFQ deleted successfully.');
    }

    /**
     * Add a quote to the RFQ (manual entry)
     */
    public function addQuote(Project $project, Rfq $rfq, Request $request)
    {
        $this->authorizeRfq($project, $rfq);

        if (!$rfq->canReceiveQuotes()) {
            return redirect()
                ->route('projects.rfqs.show', [$project, $rfq])
                ->with('error', 'This RFQ is not accepting quotes.');
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'supplier_reference' => 'nullable|string|max:100',
            'quote_date' => 'required|date',
            'valid_until' => 'required|date|after:quote_date',
            'delivery_days' => 'nullable|integer|min:1',
            'payment_terms' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.requisition_item_id' => 'nullable|exists:requisition_items,id',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($rfq, $validated) {
            $quote = Quote::create([
                'rfq_id' => $rfq->id,
                'supplier_id' => $validated['supplier_id'],
                'supplier_reference' => $validated['supplier_reference'],
                'quote_date' => $validated['quote_date'],
                'valid_until' => $validated['valid_until'],
                'delivery_days' => $validated['delivery_days'],
                'payment_terms' => $validated['payment_terms'],
                'notes' => $validated['notes'],
                'status' => Quote::STATUS_SUBMITTED,
                'submitted_at' => now(),
            ]);

            foreach ($validated['items'] as $itemData) {
                $quote->items()->create([
                    'requisition_item_id' => $itemData['requisition_item_id'] ?? null,
                    'name' => $itemData['name'],
                    'description' => $itemData['description'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'unit' => $itemData['unit'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $itemData['quantity'] * $itemData['unit_price'],
                ]);
            }

            // Recalculate totals
            $quote->recalculateTotals();

            // Update RFQ status
            if ($rfq->status === Rfq::STATUS_SENT) {
                $rfq->update(['status' => Rfq::STATUS_QUOTES_RECEIVED]);
            }

            // Update pivot
            $rfq->suppliers()->updateExistingPivot($validated['supplier_id'], [
                'status' => 'quoted',
            ]);
        });

        return redirect()
            ->route('projects.rfqs.show', [$project, $rfq])
            ->with('success', 'Quote added successfully.');
    }

    /**
     * Verify RFQ belongs to project
     */
    private function authorizeRfq(Project $project, Rfq $rfq): void
    {
        if ($rfq->rfqable_type !== Project::class || $rfq->rfqable_id !== $project->id) {
            abort(404);
        }
    }
}
