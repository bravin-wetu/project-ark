@section('title', 'Stock Item - ' . $stockItem->name)

<x-workspace-layout :workspace="$project" :workspaceType="'projects'">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
            <a href="{{ route('projects.stock.index', $project) }}" class="hover:text-ink-900 transition-colors">Stock Items</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">{{ $stockItem->sku }}</span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ink-900">{{ $stockItem->name }}</h1>
                <p class="text-smoke-600 mt-1">{{ $stockItem->description }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('projects.stock.create-batch', [$project, $stockItem]) }}" class="btn-primary inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Add Batch
                </a>
            </div>
        </div>
    </div>

    @php
        $currentStock = $stockItem->getCurrentStock();
        $isLow = $stockItem->isLowStock();
        $isOut = $currentStock <= 0;
    @endphp

    <!-- Stock Overview -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="card p-4 {{ $isOut ? 'border-red-200 bg-red-50' : ($isLow ? 'border-amber-200 bg-amber-50' : '') }}">
            <div class="text-sm {{ $isOut ? 'text-red-700' : ($isLow ? 'text-amber-700' : 'text-smoke-500') }}">Current Stock</div>
            <div class="text-3xl font-bold {{ $isOut ? 'text-red-600' : ($isLow ? 'text-amber-600' : 'text-ink-900') }}">
                {{ number_format($currentStock) }}
            </div>
            <div class="text-sm text-smoke-500">{{ $stockItem->unit }}</div>
        </div>
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Reorder Level</div>
            <div class="text-3xl font-bold text-ink-900">{{ number_format($stockItem->reorder_level ?? 0) }}</div>
            <div class="text-sm text-smoke-500">{{ $stockItem->unit }}</div>
        </div>
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Active Batches</div>
            <div class="text-3xl font-bold text-ink-900">{{ $stockItem->batches()->where('available_quantity', '>', 0)->count() }}</div>
            <div class="text-sm text-smoke-500">with stock</div>
        </div>
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Total Value</div>
            <div class="text-3xl font-bold text-ink-900">KES {{ number_format($stockItem->batches->sum(fn($b) => $b->available_quantity * $b->unit_cost), 2) }}</div>
        </div>
    </div>

    <!-- Details & Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Item Details Card -->
            <div class="card">
                <div class="px-6 py-4 border-b border-smoke-200">
                    <h2 class="font-medium text-ink-900">Item Details</h2>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm text-smoke-500">SKU</dt>
                            <dd class="font-mono text-ink-900">{{ $stockItem->sku }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-smoke-500">Category</dt>
                            <dd class="text-ink-900">{{ $stockItem->category ?? 'Uncategorized' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-smoke-500">Unit of Measure</dt>
                            <dd class="text-ink-900">{{ $stockItem->unit }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-smoke-500">Location</dt>
                            <dd class="text-ink-900">{{ $stockItem->location ?? '-' }}</dd>
                        </div>
                        @if($stockItem->minimum_order_quantity)
                        <div>
                            <dt class="text-sm text-smoke-500">Min Order Qty</dt>
                            <dd class="text-ink-900">{{ number_format($stockItem->minimum_order_quantity) }} {{ $stockItem->unit }}</dd>
                        </div>
                        @endif
                        @if($stockItem->preferred_supplier_id)
                        <div>
                            <dt class="text-sm text-smoke-500">Preferred Supplier</dt>
                            <dd class="text-ink-900">{{ $stockItem->preferredSupplier?->name ?? '-' }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Batches -->
            <div class="card">
                <div class="px-6 py-4 border-b border-smoke-200 flex items-center justify-between">
                    <h2 class="font-medium text-ink-900">Stock Batches</h2>
                    <a href="{{ route('projects.stock.batches', [$project, $stockItem]) }}" class="text-sm text-primary-600 hover:text-primary-700">View All</a>
                </div>
                @if($stockItem->batches->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-smoke-200">
                        <thead class="bg-smoke-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase">Batch #</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase">Available</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-smoke-500 uppercase">Unit Cost</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase">Expiry</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase">Received</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-smoke-100">
                            @foreach($stockItem->batches->take(5) as $batch)
                            @php
                                $isExpired = $batch->expiry_date && $batch->expiry_date->isPast();
                                $isExpiringSoon = $batch->expiry_date && $batch->expiry_date->isBetween(now(), now()->addDays(30));
                            @endphp
                            <tr class="{{ $isExpired ? 'bg-red-50' : ($isExpiringSoon ? 'bg-amber-50' : '') }}">
                                <td class="px-4 py-3 font-mono text-sm text-ink-900">{{ $batch->batch_number }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-medium {{ $batch->available_quantity <= 0 ? 'text-smoke-400' : 'text-ink-900' }}">
                                        {{ number_format($batch->available_quantity) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-ink-900">KES {{ number_format($batch->unit_cost, 2) }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($batch->expiry_date)
                                        <span class="{{ $isExpired ? 'text-red-600' : ($isExpiringSoon ? 'text-amber-600' : 'text-smoke-600') }}">
                                            {{ $batch->expiry_date->format('M d, Y') }}
                                        </span>
                                    @else
                                        <span class="text-smoke-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center text-smoke-600">{{ $batch->received_date->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="p-6 text-center text-smoke-500">
                    No batches recorded yet.
                </div>
                @endif
            </div>

            <!-- Recent Adjustments -->
            <div class="card">
                <div class="px-6 py-4 border-b border-smoke-200 flex items-center justify-between">
                    <h2 class="font-medium text-ink-900">Recent Adjustments</h2>
                    <a href="{{ route('projects.stock.adjustments', [$project, $stockItem]) }}" class="text-sm text-primary-600 hover:text-primary-700">View All</a>
                </div>
                @if($stockItem->adjustments->isNotEmpty())
                <div class="divide-y divide-smoke-100">
                    @foreach($stockItem->adjustments->take(5) as $adjustment)
                    <div class="px-6 py-3 flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-ink-900">
                                {{ $adjustment->quantity > 0 ? '+' : '' }}{{ $adjustment->quantity }} {{ $stockItem->unit }}
                            </div>
                            <div class="text-sm text-smoke-500">{{ ucfirst(str_replace('_', ' ', $adjustment->reason)) }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-smoke-600">{{ $adjustment->created_at->format('M d, Y') }}</div>
                            <div class="text-xs text-smoke-400">by {{ $adjustment->adjustedBy?->name ?? 'System' }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="p-6 text-center text-smoke-500">
                    No adjustments recorded.
                </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Actions -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="card">
                <div class="px-6 py-4 border-b border-smoke-200">
                    <h2 class="font-medium text-ink-900">Quick Actions</h2>
                </div>
                <div class="p-4 space-y-2">
                    <a href="{{ route('projects.stock.create-batch', [$project, $stockItem]) }}" 
                       class="w-full flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-smoke-50 transition-colors">
                        <div class="p-2 bg-green-50 rounded">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <div>
                            <div class="font-medium text-ink-900">Add Batch</div>
                            <div class="text-sm text-smoke-500">Record new stock receipt</div>
                        </div>
                    </a>
                    <button onclick="openAdjustmentModal()" 
                       class="w-full flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-smoke-50 transition-colors text-left">
                        <div class="p-2 bg-amber-50 rounded">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                            </svg>
                        </div>
                        <div>
                            <div class="font-medium text-ink-900">Make Adjustment</div>
                            <div class="text-sm text-smoke-500">Add/remove stock manually</div>
                        </div>
                    </button>
                    <a href="{{ route('projects.stock.edit', [$project, $stockItem]) }}" 
                       class="w-full flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-smoke-50 transition-colors">
                        <div class="p-2 bg-blue-50 rounded">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="font-medium text-ink-900">Edit Item</div>
                            <div class="text-sm text-smoke-500">Update item details</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Stock Level Indicator -->
            <div class="card p-6">
                <h3 class="text-sm font-medium text-smoke-500 uppercase tracking-wider mb-4">Stock Level</h3>
                @php
                    $reorderLevel = $stockItem->reorder_level ?? 0;
                    $maxLevel = max($currentStock, $reorderLevel * 2, 100);
                    $currentPercent = $maxLevel > 0 ? ($currentStock / $maxLevel * 100) : 0;
                    $reorderPercent = $maxLevel > 0 ? ($reorderLevel / $maxLevel * 100) : 0;
                @endphp
                <div class="relative">
                    <div class="h-4 bg-smoke-100 rounded-full overflow-hidden">
                        <div class="h-full {{ $isOut ? 'bg-red-500' : ($isLow ? 'bg-amber-500' : 'bg-green-500') }} transition-all duration-300"
                             style="width: {{ min($currentPercent, 100) }}%"></div>
                    </div>
                    @if($reorderLevel > 0)
                    <div class="absolute top-0 h-4 border-l-2 border-amber-500" 
                         style="left: {{ min($reorderPercent, 100) }}%"></div>
                    @endif
                </div>
                <div class="mt-2 flex justify-between text-sm">
                    <span class="text-smoke-500">0</span>
                    @if($reorderLevel > 0)
                    <span class="text-amber-600">Reorder: {{ number_format($reorderLevel) }}</span>
                    @endif
                    <span class="text-smoke-500">{{ number_format($maxLevel) }}</span>
                </div>
            </div>

            <!-- Activity -->
            <div class="card">
                <div class="px-6 py-4 border-b border-smoke-200">
                    <h2 class="font-medium text-ink-900">Recent Activity</h2>
                </div>
                <div class="p-4 space-y-4">
                    @forelse($stockItem->batches->take(3) as $batch)
                    <div class="flex items-start gap-3">
                        <div class="p-1 bg-green-100 rounded-full">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-ink-900">Batch <span class="font-mono">{{ $batch->batch_number }}</span> received</p>
                            <p class="text-xs text-smoke-500">{{ $batch->received_date->diffForHumans() }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-smoke-500 text-center py-4">No recent activity</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Adjustment Modal -->
    <div id="adjustmentModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-ink-900/50" onclick="closeAdjustmentModal()"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md">
            <div class="card p-6">
                <h3 class="text-lg font-medium text-ink-900 mb-4">Make Stock Adjustment</h3>
                <form action="{{ route('projects.stock.store-adjustment', [$project, $stockItem]) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1">Adjustment Type *</label>
                            <div class="flex gap-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="type" value="increase" class="form-radio text-primary-600" checked>
                                    <span class="ml-2 text-sm">Increase</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="type" value="decrease" class="form-radio text-primary-600">
                                    <span class="ml-2 text-sm">Decrease</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1">Quantity *</label>
                            <input type="number" name="quantity" required min="1" class="form-input w-full" placeholder="Enter quantity">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1">Reason *</label>
                            <select name="reason" required class="form-select w-full">
                                <option value="">Select reason...</option>
                                <option value="count_correction">Physical Count Correction</option>
                                <option value="damage">Damaged Goods</option>
                                <option value="expiry">Expired Stock</option>
                                <option value="theft">Theft/Loss</option>
                                <option value="return">Customer Return</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1">Notes</label>
                            <textarea name="notes" rows="2" class="form-textarea w-full" placeholder="Additional details..."></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" onclick="closeAdjustmentModal()" class="btn-secondary">Cancel</button>
                        <button type="submit" class="btn-primary">Submit Adjustment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAdjustmentModal() {
            document.getElementById('adjustmentModal').classList.remove('hidden');
        }
        function closeAdjustmentModal() {
            document.getElementById('adjustmentModal').classList.add('hidden');
        }
    </script>
</x-workspace-layout>
