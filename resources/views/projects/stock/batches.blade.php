@section('title', 'Batches - ' . $stockItem->name)

<x-workspace-layout :workspace="$project" :workspaceType="'projects'">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
            <a href="{{ route('projects.stock.index', $project) }}" class="hover:text-ink-900 transition-colors">Stock Items</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('projects.stock.show', [$project, $stockItem]) }}" class="hover:text-ink-900 transition-colors">{{ $stockItem->sku }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">Batches</span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ink-900">Stock Batches</h1>
                <p class="text-smoke-600 mt-1">{{ $stockItem->name }}</p>
            </div>
            <a href="{{ route('projects.stock.create-batch', [$project, $stockItem]) }}" class="btn-primary inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Batch
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Total Batches</div>
            <div class="text-2xl font-semibold text-ink-900">{{ $batches->count() }}</div>
        </div>
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Active Batches</div>
            <div class="text-2xl font-semibold text-ink-900">{{ $batches->where('available_quantity', '>', 0)->count() }}</div>
        </div>
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Total Quantity</div>
            <div class="text-2xl font-semibold text-ink-900">{{ number_format($batches->sum('available_quantity')) }} {{ $stockItem->unit }}</div>
        </div>
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Total Value</div>
            <div class="text-2xl font-semibold text-ink-900">KES {{ number_format($batches->sum(fn($b) => $b->available_quantity * $b->unit_cost), 2) }}</div>
        </div>
    </div>

    <!-- Expiry Alerts -->
    @php
        $expiringSoon = $batches->filter(fn($b) => $b->expiry_date && $b->expiry_date->isBetween(now(), now()->addDays(30)) && $b->available_quantity > 0);
        $expired = $batches->filter(fn($b) => $b->expiry_date && $b->expiry_date->isPast() && $b->available_quantity > 0);
    @endphp

    @if($expired->isNotEmpty())
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <div>
                <h3 class="text-red-800 font-medium">Expired Stock Alert</h3>
                <p class="text-sm text-red-700">{{ $expired->count() }} batch(es) have expired. Consider making a stock adjustment to remove these items.</p>
            </div>
        </div>
    </div>
    @endif

    @if($expiringSoon->isNotEmpty())
    <div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-lg">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div>
                <h3 class="text-amber-800 font-medium">Expiring Soon</h3>
                <p class="text-sm text-amber-700">{{ $expiringSoon->count() }} batch(es) will expire within 30 days. Use FIFO to issue these first.</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Batches Table -->
    @if($batches->isNotEmpty())
    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-smoke-200">
            <thead class="bg-smoke-50 border-b border-smoke-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Batch #</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">Original Qty</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">Available</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-smoke-500 uppercase tracking-wider">Unit Cost</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-smoke-500 uppercase tracking-wider">Value</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">Received</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">Expiry</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">Source</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-smoke-100">
                @foreach($batches as $batch)
                @php
                    $isExpired = $batch->expiry_date && $batch->expiry_date->isPast();
                    $isExpiringSoon = $batch->expiry_date && $batch->expiry_date->isBetween(now(), now()->addDays(30));
                    $isEmpty = $batch->available_quantity <= 0;
                @endphp
                <tr class="{{ $isExpired ? 'bg-red-50' : ($isExpiringSoon ? 'bg-amber-50' : ($isEmpty ? 'bg-smoke-50' : '')) }}">
                    <td class="px-4 py-3">
                        <span class="font-mono text-ink-900">{{ $batch->batch_number }}</span>
                    </td>
                    <td class="px-4 py-3 text-center text-smoke-600">
                        {{ number_format($batch->quantity_received) }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="font-medium {{ $isEmpty ? 'text-smoke-400' : 'text-ink-900' }}">
                            {{ number_format($batch->available_quantity) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right text-ink-900">
                        KES {{ number_format($batch->unit_cost, 2) }}
                    </td>
                    <td class="px-4 py-3 text-right font-medium text-ink-900">
                        KES {{ number_format($batch->available_quantity * $batch->unit_cost, 2) }}
                    </td>
                    <td class="px-4 py-3 text-center text-smoke-600">
                        {{ $batch->received_date->format('M d, Y') }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($batch->expiry_date)
                            <span class="{{ $isExpired ? 'text-red-600 font-medium' : ($isExpiringSoon ? 'text-amber-600' : 'text-smoke-600') }}">
                                {{ $batch->expiry_date->format('M d, Y') }}
                            </span>
                        @else
                            <span class="text-smoke-400">N/A</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($isEmpty)
                            <span class="inline-flex px-2 py-0.5 text-xs bg-smoke-100 text-smoke-600 rounded-full">Depleted</span>
                        @elseif($isExpired)
                            <span class="inline-flex px-2 py-0.5 text-xs bg-red-100 text-red-700 rounded-full">Expired</span>
                        @elseif($isExpiringSoon)
                            <span class="inline-flex px-2 py-0.5 text-xs bg-amber-100 text-amber-700 rounded-full">Expiring</span>
                        @else
                            <span class="inline-flex px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded-full">Active</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($batch->goods_receipt_item_id)
                            <a href="#" class="text-primary-600 hover:text-primary-700 text-sm">GRN</a>
                        @elseif($batch->reference)
                            <span class="text-sm text-smoke-600">{{ $batch->reference }}</span>
                        @else
                            <span class="text-smoke-400">Manual</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-smoke-50 border-t border-smoke-200">
                <tr>
                    <td class="px-4 py-3 font-medium text-ink-900">Total</td>
                    <td class="px-4 py-3 text-center font-medium text-ink-900">{{ number_format($batches->sum('quantity_received')) }}</td>
                    <td class="px-4 py-3 text-center font-medium text-ink-900">{{ number_format($batches->sum('available_quantity')) }}</td>
                    <td class="px-4 py-3 text-right text-smoke-500">-</td>
                    <td class="px-4 py-3 text-right font-medium text-ink-900">KES {{ number_format($batches->sum(fn($b) => $b->available_quantity * $b->unit_cost), 2) }}</td>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if($batches instanceof \Illuminate\Pagination\LengthAwarePaginator)
    <div class="mt-4">
        {{ $batches->links() }}
    </div>
    @endif
    @else
    <!-- Empty State -->
    <div class="card p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-smoke-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
        <h3 class="text-lg font-medium text-ink-900 mb-1">No batches yet</h3>
        <p class="text-smoke-500 mb-4">Add your first batch to start tracking stock for this item.</p>
        <a href="{{ route('projects.stock.create-batch', [$project, $stockItem]) }}" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add First Batch
        </a>
    </div>
    @endif

    <!-- FIFO Explanation -->
    <div class="mt-8 card p-6 bg-blue-50 border-blue-200">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <h3 class="font-medium text-blue-900">FIFO (First-In, First-Out)</h3>
                <p class="text-sm text-blue-800 mt-1">
                    Stock is automatically issued using FIFO method. The oldest batches (by received date) are consumed first.
                    This ensures proper stock rotation and minimizes expiry wastage.
                </p>
            </div>
        </div>
    </div>
</x-workspace-layout>
