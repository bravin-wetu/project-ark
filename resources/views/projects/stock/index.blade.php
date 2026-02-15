@section('title', 'Stock Items - ' . $project->name)

<x-workspace-layout :workspace="$project" :workspaceType="'projects'">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-semibold text-ink-900">Stock Items</h1>
            <p class="text-smoke-600 mt-1">Manage inventory and track stock levels</p>
        </div>
        <a href="{{ route('projects.stock.create', $project) }}" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add Stock Item
        </a>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Total Items</div>
            <div class="text-2xl font-semibold text-ink-900">{{ $stockItems->count() }}</div>
        </div>
        <div class="card p-4 {{ $lowStockCount > 0 ? 'border-amber-200 bg-amber-50' : '' }}">
            <div class="text-sm {{ $lowStockCount > 0 ? 'text-amber-700' : 'text-smoke-500' }}">Low Stock</div>
            <div class="text-2xl font-semibold {{ $lowStockCount > 0 ? 'text-amber-600' : 'text-ink-900' }}">{{ $lowStockCount }}</div>
        </div>
        <div class="card p-4 {{ $outOfStockCount > 0 ? 'border-red-200 bg-red-50' : '' }}">
            <div class="text-sm {{ $outOfStockCount > 0 ? 'text-red-700' : 'text-smoke-500' }}">Out of Stock</div>
            <div class="text-2xl font-semibold {{ $outOfStockCount > 0 ? 'text-red-600' : 'text-ink-900' }}">{{ $outOfStockCount }}</div>
        </div>
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Total Value</div>
            <div class="text-2xl font-semibold text-ink-900">KES {{ number_format($totalValue, 2) }}</div>
        </div>
    </div>

    <!-- Alerts -->
    @if($lowStockItems->isNotEmpty())
    <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div>
                <h3 class="text-amber-800 font-medium">Low Stock Alert</h3>
                <p class="text-sm text-amber-700 mt-1">
                    {{ $lowStockItems->count() }} item(s) are running low:
                    {{ $lowStockItems->take(3)->pluck('name')->join(', ') }}{{ $lowStockItems->count() > 3 ? '...' : '' }}
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Filter -->
    <div class="card p-4 mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm text-smoke-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, SKU, description..." 
                       class="form-input w-full">
            </div>
            <div>
                <label class="block text-sm text-smoke-600 mb-1">Category</label>
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>{{ $category }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-smoke-600 mb-1">Stock Level</label>
                <select name="stock_level" class="form-select">
                    <option value="">All Levels</option>
                    <option value="low" {{ request('stock_level') == 'low' ? 'selected' : '' }}>Low Stock</option>
                    <option value="out" {{ request('stock_level') == 'out' ? 'selected' : '' }}>Out of Stock</option>
                    <option value="ok" {{ request('stock_level') == 'ok' ? 'selected' : '' }}>Sufficient</option>
                </select>
            </div>
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
    </div>

    <!-- Stock Items Table -->
    @if($stockItems->isNotEmpty())
    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-smoke-200">
            <thead class="bg-smoke-50 border-b border-smoke-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">SKU</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Item</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Category</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">In Stock</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">Reorder Level</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-smoke-500 uppercase tracking-wider">Value</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-smoke-100">
                @foreach($stockItems as $item)
                @php
                    $currentStock = $item->getCurrentStock();
                    $isLow = $item->isLowStock();
                    $isOut = $currentStock <= 0;
                @endphp
                <tr class="hover:bg-smoke-50 {{ $isOut ? 'bg-red-50' : ($isLow ? 'bg-amber-50' : '') }}">
                    <td class="px-4 py-3">
                        <span class="font-mono text-sm text-smoke-600">{{ $item->sku }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('projects.stock.show', [$project, $item]) }}" class="font-medium text-ink-900 hover:text-primary-600">
                            {{ $item->name }}
                        </a>
                        @if($item->description)
                        <p class="text-sm text-smoke-500 truncate max-w-xs">{{ $item->description }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-0.5 text-xs font-medium bg-smoke-100 text-smoke-700 rounded">
                            {{ $item->category ?? 'Uncategorized' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="font-medium {{ $isOut ? 'text-red-600' : ($isLow ? 'text-amber-600' : 'text-ink-900') }}">
                            {{ number_format($currentStock) }}
                        </span>
                        <span class="text-smoke-500 text-sm">{{ $item->unit }}</span>
                    </td>
                    <td class="px-4 py-3 text-center text-smoke-600">
                        {{ number_format($item->reorder_level ?? 0) }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($isOut)
                            <span class="inline-flex px-2 py-0.5 text-xs font-medium bg-red-100 text-red-700 rounded-full">Out of Stock</span>
                        @elseif($isLow)
                            <span class="inline-flex px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 rounded-full">Low</span>
                        @else
                            <span class="inline-flex px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 rounded-full">OK</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right font-medium text-ink-900">
                        KES {{ number_format($item->batches->sum(fn($b) => $b->available_quantity * $b->unit_cost), 2) }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('projects.stock.batches', [$project, $item]) }}" class="text-smoke-400 hover:text-primary-600" title="View Batches">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </a>
                            <a href="{{ route('projects.stock.create-batch', [$project, $item]) }}" class="text-smoke-400 hover:text-green-600" title="Add Batch">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </a>
                            <a href="{{ route('projects.stock.adjustments', [$project, $item]) }}" class="text-smoke-400 hover:text-amber-600" title="Adjustments">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $stockItems->links() }}
    </div>
    @else
    <!-- Empty State -->
    <div class="card p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-smoke-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
        <h3 class="text-lg font-medium text-ink-900 mb-1">No stock items yet</h3>
        <p class="text-smoke-500 mb-4">Add your first stock item to start tracking inventory.</p>
        <a href="{{ route('projects.stock.create', $project) }}" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add Stock Item
        </a>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('projects.stock.issues', $project) }}" class="card p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-50 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </div>
                <div>
                    <span class="font-medium text-ink-900">Stock Issues</span>
                    <p class="text-sm text-smoke-500">View and create stock issue requests</p>
                </div>
            </div>
        </a>
        <a href="{{ route('projects.stock.create-issue', $project) }}" class="card p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-green-50 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div>
                    <span class="font-medium text-ink-900">New Issue Request</span>
                    <p class="text-sm text-smoke-500">Request stock items from inventory</p>
                </div>
            </div>
        </a>
        <a href="#" class="card p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-purple-50 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <span class="font-medium text-ink-900">Inventory Report</span>
                    <p class="text-sm text-smoke-500">Generate stock valuation report</p>
                </div>
            </div>
        </a>
    </div>
</x-workspace-layout>
