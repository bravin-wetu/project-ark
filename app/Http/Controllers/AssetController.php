<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetTransfer;
use App\Models\AssetMaintenance;
use App\Models\Project;
use App\Models\Hub;
use App\Models\User;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    /**
     * Display assets for a project
     */
    public function index(Project $project)
    {
        $assets = Asset::forProject($project->id)
            ->with(['hub', 'assignedUser', 'supplier'])
            ->latest()
            ->paginate(20);

        $stats = [
            'total' => Asset::forProject($project->id)->count(),
            'active' => Asset::forProject($project->id)->active()->count(),
            'in_maintenance' => Asset::forProject($project->id)->where('status', Asset::STATUS_IN_MAINTENANCE)->count(),
            'disposed' => Asset::forProject($project->id)->where('status', Asset::STATUS_DISPOSED)->count(),
        ];

        return view('projects.assets.index', compact('project', 'assets', 'stats'));
    }

    /**
     * Show form to create asset from receipt item
     */
    public function createFromReceipt(Project $project, GoodsReceipt $receipt, GoodsReceiptItem $item)
    {
        // Verify the receipt belongs to this project
        if ($receipt->purchaseOrder->purchaseable_id !== $project->id) {
            abort(403, 'Receipt does not belong to this project');
        }

        $hubs = Hub::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $categories = Asset::getCategories();
        $conditions = Asset::getConditions();

        return view('projects.assets.create-from-receipt', compact(
            'project', 'receipt', 'item', 'hubs', 'users', 'categories', 'conditions'
        ));
    }

    /**
     * Store asset from receipt item
     */
    public function storeFromReceipt(Request $request, Project $project, GoodsReceipt $receipt, GoodsReceiptItem $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'subcategory' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'hub_id' => 'required|exists:hubs,id',
            'assigned_to' => 'nullable|exists:users,id',
            'location' => 'nullable|string|max:255',
            'condition' => 'required|string',
            'warranty_expiry' => 'nullable|date',
            'warranty_notes' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $asset = Asset::createFromReceiptItem($item, $validated);

        return redirect()
            ->route('projects.assets.show', [$project, $asset])
            ->with('success', "Asset {$asset->asset_tag} registered successfully.");
    }

    /**
     * Show form to create asset manually
     */
    public function create(Project $project)
    {
        $hubs = Hub::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $categories = Asset::getCategories();
        $conditions = Asset::getConditions();

        return view('projects.assets.create', compact(
            'project', 'hubs', 'users', 'categories', 'conditions'
        ));
    }

    /**
     * Store manually created asset
     */
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'subcategory' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'acquisition_cost' => 'nullable|numeric|min:0',
            'acquisition_date' => 'nullable|date',
            'hub_id' => 'required|exists:hubs,id',
            'assigned_to' => 'nullable|exists:users,id',
            'location' => 'nullable|string|max:255',
            'condition' => 'required|string',
            'warranty_expiry' => 'nullable|date',
            'warranty_notes' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['assetable_type'] = Project::class;
        $validated['assetable_id'] = $project->id;
        $validated['status'] = Asset::STATUS_PENDING;

        $asset = Asset::create($validated);

        return redirect()
            ->route('projects.assets.show', [$project, $asset])
            ->with('success', "Asset {$asset->asset_tag} created successfully.");
    }

    /**
     * Display asset details
     */
    public function show(Project $project, Asset $asset)
    {
        $asset->load(['hub', 'assignedUser', 'supplier', 'purchaseOrder', 'goodsReceipt', 'creator']);
        
        $transfers = $asset->transfers()->with(['fromHub', 'toHub', 'initiator'])->latest()->get();
        $maintenances = $asset->maintenances()->with(['creator'])->latest()->get();

        return view('projects.assets.show', compact('project', 'asset', 'transfers', 'maintenances'));
    }

    /**
     * Activate asset
     */
    public function activate(Project $project, Asset $asset)
    {
        if (!$asset->activate()) {
            return back()->with('error', 'Asset cannot be activated.');
        }

        return back()->with('success', 'Asset activated successfully.');
    }

    /**
     * Show transfer form
     */
    public function transferForm(Project $project, Asset $asset)
    {
        if (!$asset->canBeTransferred()) {
            return back()->with('error', 'Asset cannot be transferred in its current status.');
        }

        $hubs = Hub::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $conditions = ['excellent', 'good', 'fair', 'poor', 'damaged'];

        return view('projects.assets.transfer', compact('project', 'asset', 'hubs', 'users', 'conditions'));
    }

    /**
     * Create transfer
     */
    public function transfer(Request $request, Project $project, Asset $asset)
    {
        $validated = $request->validate([
            'to_hub_id' => 'nullable|exists:hubs,id',
            'to_user_id' => 'nullable|exists:users,id',
            'to_location' => 'nullable|string|max:255',
            'transfer_date' => 'required|date',
            'expected_arrival' => 'nullable|date|after_or_equal:transfer_date',
            'reason' => 'nullable|string',
            'condition_on_transfer' => 'required|string',
        ]);

        $validated['asset_id'] = $asset->id;
        $validated['from_hub_id'] = $asset->hub_id;
        $validated['from_user_id'] = $asset->assigned_to;
        $validated['from_location'] = $asset->location;
        $validated['status'] = AssetTransfer::STATUS_PENDING;

        $transfer = AssetTransfer::create($validated);

        return redirect()
            ->route('projects.assets.show', [$project, $asset])
            ->with('success', "Transfer {$transfer->transfer_number} initiated.");
    }

    /**
     * Show maintenance form
     */
    public function maintenanceForm(Project $project, Asset $asset)
    {
        $types = AssetMaintenance::getTypes();
        $conditions = Asset::getConditions();

        return view('projects.assets.maintenance', compact('project', 'asset', 'types', 'conditions'));
    }

    /**
     * Schedule maintenance
     */
    public function scheduleMaintenance(Request $request, Project $project, Asset $asset)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_date' => 'required|date',
            'service_provider' => 'nullable|string|max:255',
            'technician' => 'nullable|string|max:255',
            'estimated_cost' => 'nullable|numeric|min:0',
            'condition_before' => 'nullable|string',
        ]);

        $validated['asset_id'] = $asset->id;
        $validated['status'] = AssetMaintenance::STATUS_SCHEDULED;

        $maintenance = AssetMaintenance::create($validated);

        return redirect()
            ->route('projects.assets.show', [$project, $asset])
            ->with('success', "Maintenance {$maintenance->maintenance_number} scheduled.");
    }

    /**
     * Show disposal form
     */
    public function disposeForm(Project $project, Asset $asset)
    {
        if (!$asset->canBeDisposed()) {
            return back()->with('error', 'Asset cannot be disposed in its current status.');
        }

        $methods = ['sold', 'donated', 'scrapped', 'returned', 'other'];

        return view('projects.assets.dispose', compact('project', 'asset', 'methods'));
    }

    /**
     * Dispose asset
     */
    public function dispose(Request $request, Project $project, Asset $asset)
    {
        $validated = $request->validate([
            'disposal_method' => 'required|string',
            'disposal_value' => 'nullable|numeric|min:0',
            'disposal_notes' => 'nullable|string',
        ]);

        if (!$asset->dispose($validated['disposal_method'], $validated['disposal_value'] ?? null, $validated['disposal_notes'] ?? null)) {
            return back()->with('error', 'Failed to dispose asset.');
        }

        return redirect()
            ->route('projects.assets.index', $project)
            ->with('success', "Asset {$asset->asset_tag} has been disposed.");
    }
}
