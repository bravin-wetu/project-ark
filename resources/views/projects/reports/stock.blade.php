@section('title', 'Stock Report - ' . $project->name)

<x-workspace-layout :workspace="$project" :workspaceType="'projects'">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
            <a href="{{ route('projects.reports.index', $project) }}" class="hover:text-ink-900 transition-colors">Reports</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">Stock Report</span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ink-900">Stock Report</h1>
                <p class="text-smoke-600 mt-1">Inventory levels, stock alerts, and movement analysis.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('projects.reports.index', $project) }}?export=excel" class="btn-secondary text-sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export
                </a>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Stock Items</div>
            <div class="text-3xl font-semibold text-ink-900">{{ $totalItems }}</div>
        </div>
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Total Value</div>
            <div class="text-2xl font-semibold text-ink-900">KES {{ number_format($totalValue, 0) }}</div>
        </div>
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Items Received (30d)</div>
            <div class="text-2xl font-semibold text-green-600">{{ $movement['received'] ?? 0 }}</div>
        </div>
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Items Issued (30d)</div>
            <div class="text-2xl font-semibold text-blue-600">{{ $movement['issued'] ?? 0 }}</div>
        </div>
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Alerts</div>
            <div class="text-3xl font-semibold {{ ($lowStockItems->count() + $expiringBatches->count()) > 0 ? 'text-red-600' : 'text-green-600' }}">
                {{ $lowStockItems->count() + $expiringBatches->count() }}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-6 mb-8">
        <!-- Low Stock Alerts -->
        <div class="card {{ $lowStockItems->count() > 0 ? 'border-l-4 border-amber-500' : '' }}">
            <div class="px-6 py-4 border-b border-smoke-200 flex items-center justify-between">
                <h2 class="font-medium text-ink-900">Low Stock Items</h2>
                @if($lowStockItems->count() > 0)
                <span class="inline-flex px-2 py-0.5 text-xs bg-amber-100 text-amber-700 rounded-full">{{ $lowStockItems->count() }} items</span>
                @endif
            </div>
            <div class="p-4">
                @if($lowStockItems->isNotEmpty())
                <div class="space-y-3">
                    @foreach($lowStockItems->take(10) as $item)
                    <div class="flex items-center justify-between p-3 bg-amber-50 rounded-lg">
                        <div>
                            <div class="font-medium text-ink-900">{{ $item->name }}</div>
                            <div class="text-xs text-smoke-500">{{ $item->sku ?? 'No SKU' }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold text-amber-600">{{ $item->current_quantity }} {{ $item->unit }}</div>
                            <div class="text-xs text-smoke-500">Min: {{ $item->reorder_level }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @if($lowStockItems->count() > 10)
                <p class="text-sm text-smoke-500 text-center mt-4">And {{ $lowStockItems->count() - 10 }} more...</p>
                @endif
                @else
                <div class="text-center py-8 text-smoke-500">
                    <svg class="w-12 h-12 mx-auto text-green-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p>All stock levels are healthy</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Expiring Batches -->
        <div class="card {{ $expiringBatches->count() > 0 ? 'border-l-4 border-red-500' : '' }}">
            <div class="px-6 py-4 border-b border-smoke-200 flex items-center justify-between">
                <h2 class="font-medium text-ink-900">Expiring Soon (90 days)</h2>
                @if($expiringBatches->count() > 0)
                <span class="inline-flex px-2 py-0.5 text-xs bg-red-100 text-red-700 rounded-full">{{ $expiringBatches->count() }} batches</span>
                @endif
            </div>
            <div class="p-4">
                @if($expiringBatches->isNotEmpty())
                <div class="space-y-3">
                    @foreach($expiringBatches->take(10) as $batch)
                    @php
                        $daysUntil = \Carbon\Carbon::parse($batch->expiry_date)->diffInDays(now(), false) * -1;
                        $urgent = $daysUntil <= 30;
                    @endphp
                    <div class="flex items-center justify-between p-3 {{ $urgent ? 'bg-red-50' : 'bg-amber-50' }} rounded-lg">
                        <div>
                            <div class="font-medium text-ink-900">{{ $batch->stockItem?->name ?? 'Unknown Item' }}</div>
                            <div class="text-xs text-smoke-500">Batch: {{ $batch->batch_number }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold {{ $urgent ? 'text-red-600' : 'text-amber-600' }}">
                                {{ $daysUntil }} days
                            </div>
                            <div class="text-xs text-smoke-500">{{ \Carbon\Carbon::parse($batch->expiry_date)->format('M d, Y') }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @if($expiringBatches->count() > 10)
                <p class="text-sm text-smoke-500 text-center mt-4">And {{ $expiringBatches->count() - 10 }} more...</p>
                @endif
                @else
                <div class="text-center py-8 text-smoke-500">
                    <svg class="w-12 h-12 mx-auto text-green-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p>No batches expiring soon</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Stock by Category -->
    <div class="card mb-8">
        <div class="px-6 py-4 border-b border-smoke-200">
            <h2 class="font-medium text-ink-900">Stock Value by Category</h2>
        </div>
        @if($byCategory->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-smoke-200">
                <thead class="bg-smoke-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase">Category</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase">Items</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-smoke-500 uppercase">Total Qty</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-smoke-500 uppercase">Value</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase">% of Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-smoke-100">
                    @foreach($byCategory as $cat)
                    @php
                        $percentage = $totalValue > 0 ? ($cat['value'] / $totalValue) * 100 : 0;
                    @endphp
                    <tr class="hover:bg-smoke-50">
                        <td class="px-4 py-3 font-medium text-ink-900">{{ $cat['category'] ?? 'Uncategorized' }}</td>
                        <td class="px-4 py-3 text-center text-smoke-600">{{ $cat['items'] }}</td>
                        <td class="px-4 py-3 text-right text-smoke-600">{{ number_format($cat['quantity']) }}</td>
                        <td class="px-4 py-3 text-right font-medium text-ink-900">KES {{ number_format($cat['value'], 0) }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2 bg-smoke-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-500 rounded-full" style="width: {{ min($percentage, 100) }}%"></div>
                                </div>
                                <span class="text-sm text-smoke-600 w-12 text-right">{{ number_format($percentage, 1) }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-smoke-50">
                    <tr>
                        <td class="px-4 py-3 font-semibold text-ink-900">Total</td>
                        <td class="px-4 py-3 text-center font-semibold text-ink-900">{{ $byCategory->sum('items') }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-ink-900">{{ number_format($byCategory->sum('quantity')) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-ink-900">KES {{ number_format($totalValue, 0) }}</td>
                        <td class="px-4 py-3 font-semibold text-ink-900">100%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="p-12 text-center text-smoke-500">
            <p>No stock data available</p>
        </div>
        @endif
    </div>

    <!-- Stock Movement Chart (Simplified) -->
    <div class="card mb-8">
        <div class="px-6 py-4 border-b border-smoke-200">
            <h2 class="font-medium text-ink-900">Stock Movement (Last 30 Days)</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto rounded-full bg-green-100 flex items-center justify-center mb-3">
                        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18M15 8l4 4m0 0l-4 4"/>
                        </svg>
                    </div>
                    <div class="text-3xl font-semibold text-green-600">{{ number_format($movement['received'] ?? 0) }}</div>
                    <div class="text-sm text-smoke-500">Items Received</div>
                </div>
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto rounded-full bg-blue-100 flex items-center justify-center mb-3">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3M9 16l-4-4m0 0l4-4"/>
                        </svg>
                    </div>
                    <div class="text-3xl font-semibold text-blue-600">{{ number_format($movement['issued'] ?? 0) }}</div>
                    <div class="text-sm text-smoke-500">Items Issued</div>
                </div>
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto rounded-full {{ ($movement['received'] ?? 0) >= ($movement['issued'] ?? 0) ? 'bg-green-100' : 'bg-red-100' }} flex items-center justify-center mb-3">
                        <svg class="w-10 h-10 {{ ($movement['received'] ?? 0) >= ($movement['issued'] ?? 0) ? 'text-green-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if(($movement['received'] ?? 0) >= ($movement['issued'] ?? 0))
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                            @endif
                        </svg>
                    </div>
                    @php
                        $net = ($movement['received'] ?? 0) - ($movement['issued'] ?? 0);
                    @endphp
                    <div class="text-3xl font-semibold {{ $net >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $net >= 0 ? '+' : '' }}{{ number_format($net) }}
                    </div>
                    <div class="text-sm text-smoke-500">Net Change</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="flex items-center gap-4">
        <a href="{{ route('projects.stock.index', $project) }}" class="btn-secondary text-sm">
            View All Stock Items
        </a>
        <a href="{{ route('projects.stock.movements', $project) }}" class="btn-secondary text-sm">
            Stock Movements
        </a>
        <a href="{{ route('projects.hubs.index', $project) }}" class="btn-secondary text-sm">
            Manage Warehouses
        </a>
    </div>
</x-workspace-layout>
