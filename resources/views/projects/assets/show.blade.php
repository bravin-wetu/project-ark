@section('title', $asset->asset_tag . ' - ' . $project->name)

<x-workspace-layout :workspace="$project" :workspaceType="'projects'">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
            <a href="{{ route('projects.show', $project) }}" class="hover:text-ink-900 transition-colors">{{ $project->name }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('projects.assets.index', $project) }}" class="hover:text-ink-900 transition-colors">Assets</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">{{ $asset->asset_tag }}</span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-semibold text-ink-900">{{ $asset->name }}</h1>
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $asset->getStatusBadgeClass() }}">
                        {{ ucfirst(str_replace('_', ' ', $asset->status)) }}
                    </span>
                </div>
                <p class="text-smoke-600 mt-1 font-mono">{{ $asset->asset_tag }}</p>
            </div>
            <div class="flex gap-2">
                @if($asset->isPending())
                    <form action="{{ route('projects.assets.activate', [$project, $asset]) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-primary">Activate Asset</button>
                    </form>
                @endif
                @if($asset->canBeTransferred())
                    <a href="{{ route('projects.assets.transfer-form', [$project, $asset]) }}" class="btn-secondary">Transfer</a>
                @endif
                <a href="{{ route('projects.assets.maintenance-form', [$project, $asset]) }}" class="btn-secondary">Schedule Maintenance</a>
                @if($asset->canBeDisposed())
                    <a href="{{ route('projects.assets.dispose-form', [$project, $asset]) }}" class="text-red-600 hover:text-red-800 px-3 py-2">Dispose</a>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <!-- Main Details -->
        <div class="col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-ink-900 mb-4">Asset Details</h3>
                
                <dl class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm text-smoke-500">Category</dt>
                        <dd class="text-ink-900">{{ $asset->category }}</dd>
                    </div>
                    @if($asset->subcategory)
                    <div>
                        <dt class="text-sm text-smoke-500">Subcategory</dt>
                        <dd class="text-ink-900">{{ $asset->subcategory }}</dd>
                    </div>
                    @endif
                    @if($asset->serial_number)
                    <div>
                        <dt class="text-sm text-smoke-500">Serial Number</dt>
                        <dd class="text-ink-900 font-mono">{{ $asset->serial_number }}</dd>
                    </div>
                    @endif
                    @if($asset->model)
                    <div>
                        <dt class="text-sm text-smoke-500">Model</dt>
                        <dd class="text-ink-900">{{ $asset->model }}</dd>
                    </div>
                    @endif
                    @if($asset->manufacturer)
                    <div>
                        <dt class="text-sm text-smoke-500">Manufacturer</dt>
                        <dd class="text-ink-900">{{ $asset->manufacturer }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-sm text-smoke-500">Condition</dt>
                        <dd>
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $asset->getConditionBadgeClass() }}">
                                {{ ucfirst(str_replace('_', ' ', $asset->condition)) }}
                            </span>
                        </dd>
                    </div>
                </dl>

                @if($asset->description)
                <div class="mt-4 pt-4 border-t border-smoke-200">
                    <dt class="text-sm text-smoke-500 mb-1">Description</dt>
                    <dd class="text-ink-900">{{ $asset->description }}</dd>
                </div>
                @endif
            </div>

            <!-- Source Information -->
            @if($asset->purchaseOrder || $asset->goodsReceipt)
            <div class="card p-6">
                <h3 class="text-lg font-medium text-ink-900 mb-4">Procurement Source</h3>
                
                <dl class="grid grid-cols-2 gap-4">
                    @if($asset->purchaseOrder)
                    <div>
                        <dt class="text-sm text-smoke-500">Purchase Order</dt>
                        <dd>
                            <a href="{{ route('projects.purchase-orders.show', [$project, $asset->purchaseOrder]) }}" 
                               class="text-ink-900 hover:underline font-mono">
                                {{ $asset->purchaseOrder->po_number }}
                            </a>
                        </dd>
                    </div>
                    @endif
                    @if($asset->goodsReceipt)
                    <div>
                        <dt class="text-sm text-smoke-500">Goods Receipt</dt>
                        <dd>
                            <a href="{{ route('projects.goods-receipts.show', [$project, $asset->goodsReceipt]) }}" 
                               class="text-ink-900 hover:underline font-mono">
                                {{ $asset->goodsReceipt->receipt_number }}
                            </a>
                        </dd>
                    </div>
                    @endif
                    @if($asset->supplier)
                    <div>
                        <dt class="text-sm text-smoke-500">Supplier</dt>
                        <dd class="text-ink-900">{{ $asset->supplier->name }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
            @endif

            <!-- Transfer History -->
            <div class="card">
                <div class="p-4 border-b border-smoke-200">
                    <h3 class="font-medium text-ink-900">Transfer History</h3>
                </div>
                @if($transfers->isEmpty())
                    <div class="p-6 text-center text-smoke-500">
                        No transfers recorded.
                    </div>
                @else
                    <div class="divide-y divide-smoke-100">
                        @foreach($transfers as $transfer)
                        <div class="p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="font-mono text-sm text-ink-900">{{ $transfer->transfer_number }}</span>
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $transfer->getStatusBadgeClass() }} ml-2">
                                        {{ ucfirst(str_replace('_', ' ', $transfer->status)) }}
                                    </span>
                                </div>
                                <span class="text-sm text-smoke-500">{{ $transfer->transfer_date->format('M d, Y') }}</span>
                            </div>
                            <p class="text-sm text-smoke-600 mt-1">
                                {{ $transfer->fromHub?->name ?? 'Unknown' }} → {{ $transfer->toHub?->name ?? 'Unknown' }}
                            </p>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Maintenance History -->
            <div class="card">
                <div class="p-4 border-b border-smoke-200">
                    <h3 class="font-medium text-ink-900">Maintenance History</h3>
                </div>
                @if($maintenances->isEmpty())
                    <div class="p-6 text-center text-smoke-500">
                        No maintenance records.
                    </div>
                @else
                    <div class="divide-y divide-smoke-100">
                        @foreach($maintenances as $maint)
                        <div class="p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="font-mono text-sm text-ink-900">{{ $maint->maintenance_number }}</span>
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $maint->getTypeBadgeClass() }} ml-2">
                                        {{ ucfirst($maint->type) }}
                                    </span>
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $maint->getStatusBadgeClass() }} ml-1">
                                        {{ ucfirst(str_replace('_', ' ', $maint->status)) }}
                                    </span>
                                </div>
                                <span class="text-sm text-smoke-500">{{ $maint->scheduled_date->format('M d, Y') }}</span>
                            </div>
                            <p class="text-sm text-ink-900 mt-1">{{ $maint->title }}</p>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Location & Assignment -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-ink-900 mb-4">Location</h3>
                
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm text-smoke-500">Hub</dt>
                        <dd class="text-ink-900">{{ $asset->hub?->name ?? 'Not assigned' }}</dd>
                    </div>
                    @if($asset->location)
                    <div>
                        <dt class="text-sm text-smoke-500">Physical Location</dt>
                        <dd class="text-ink-900">{{ $asset->location }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-sm text-smoke-500">Assigned To</dt>
                        <dd class="text-ink-900">{{ $asset->assignedUser?->name ?? 'Not assigned' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Financial -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-ink-900 mb-4">Financial</h3>
                
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm text-smoke-500">Acquisition Cost</dt>
                        <dd class="text-ink-900 font-mono text-lg">{{ number_format($asset->acquisition_cost, 2) }}</dd>
                    </div>
                    @if($asset->current_value && $asset->current_value != $asset->acquisition_cost)
                    <div>
                        <dt class="text-sm text-smoke-500">Current Value</dt>
                        <dd class="text-ink-900 font-mono">{{ number_format($asset->current_value, 2) }}</dd>
                    </div>
                    @endif
                    @if($asset->acquisition_date)
                    <div>
                        <dt class="text-sm text-smoke-500">Acquisition Date</dt>
                        <dd class="text-ink-900">{{ $asset->acquisition_date->format('M d, Y') }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Warranty -->
            @if($asset->warranty_expiry)
            <div class="card p-6">
                <h3 class="text-lg font-medium text-ink-900 mb-4">Warranty</h3>
                
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm text-smoke-500">Expiry Date</dt>
                        <dd class="text-ink-900 {{ $asset->warranty_expiry->isPast() ? 'text-red-600' : '' }}">
                            {{ $asset->warranty_expiry->format('M d, Y') }}
                            @if($asset->warranty_expiry->isPast())
                                <span class="text-xs">(Expired)</span>
                            @endif
                        </dd>
                    </div>
                    @if($asset->warranty_notes)
                    <div>
                        <dt class="text-sm text-smoke-500">Notes</dt>
                        <dd class="text-ink-900 text-sm">{{ $asset->warranty_notes }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
            @endif

            <!-- Audit Info -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-ink-900 mb-4">Audit Trail</h3>
                
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm text-smoke-500">Registered By</dt>
                        <dd class="text-ink-900">{{ $asset->creator?->name ?? 'System' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-smoke-500">Created</dt>
                        <dd class="text-ink-900">{{ $asset->created_at->format('M d, Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-smoke-500">Last Updated</dt>
                        <dd class="text-ink-900">{{ $asset->updated_at->format('M d, Y H:i') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-workspace-layout>
