@section('title', 'Procurement Report - ' . $project->name)

<x-workspace-layout :workspace="$project" :workspaceType="'projects'">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
            <a href="{{ route('projects.reports.index', $project) }}" class="hover:text-ink-900 transition-colors">Reports</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">Procurement Analysis</span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ink-900">Procurement Analysis</h1>
                <p class="text-smoke-600 mt-1">Spending patterns, supplier distribution, and PO metrics.</p>
            </div>
            <form method="GET" class="flex items-center gap-2">
                <input type="date" name="start_date" value="{{ $startDate instanceof \Carbon\Carbon ? $startDate->format('Y-m-d') : $startDate }}" 
                       class="form-input text-sm">
                <span class="text-smoke-400">to</span>
                <input type="date" name="end_date" value="{{ $endDate instanceof \Carbon\Carbon ? $endDate->format('Y-m-d') : $endDate }}" 
                       class="form-input text-sm">
                <button type="submit" class="btn-secondary text-sm">Apply</button>
            </form>
        </div>
    </div>

    <!-- PO Status Overview -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-8">
        @php
            $statuses = ['draft' => 'Draft', 'pending_approval' => 'Pending', 'approved' => 'Approved', 'sent' => 'Sent', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
            $statusColors = ['draft' => 'smoke', 'pending_approval' => 'amber', 'approved' => 'blue', 'sent' => 'purple', 'completed' => 'green', 'cancelled' => 'red'];
        @endphp
        @foreach($statuses as $status => $label)
        @php
            $data = $poStatusBreakdown->get($status);
        @endphp
        <div class="card p-4 border-l-4 border-l-{{ $statusColors[$status] }}-500">
            <div class="text-sm text-smoke-500">{{ $label }}</div>
            <div class="text-2xl font-semibold text-ink-900">{{ $data?->count ?? 0 }}</div>
            <div class="text-xs text-smoke-400">KES {{ number_format($data?->total ?? 0, 0) }}</div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Top Suppliers by Spend -->
        <div class="card">
            <div class="px-6 py-4 border-b border-smoke-200">
                <h2 class="font-medium text-ink-900">Top 10 Suppliers by Spend</h2>
            </div>
            @if($spendBySupplier->isNotEmpty())
            <div class="p-4">
                @php $maxSpend = $spendBySupplier->max('total_value') ?: 1; @endphp
                @foreach($spendBySupplier as $supplier)
                <div class="mb-4 last:mb-0">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-ink-900 truncate">{{ $supplier['supplier_name'] }}</span>
                        <span class="text-smoke-600">KES {{ number_format($supplier['total_value'], 0) }}</span>
                    </div>
                    <div class="h-2 bg-smoke-100 rounded-full overflow-hidden">
                        <div class="h-full bg-primary-500 rounded-full" style="width: {{ ($supplier['total_value'] / $maxSpend) * 100 }}%"></div>
                    </div>
                    <div class="text-xs text-smoke-400 mt-1">{{ $supplier['po_count'] }} POs</div>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-6 text-center text-smoke-500">No supplier data available.</div>
            @endif
        </div>

        <!-- Spend by Category -->
        <div class="card">
            <div class="px-6 py-4 border-b border-smoke-200">
                <h2 class="font-medium text-ink-900">Spending by Category</h2>
            </div>
            @if($spendByCategory->isNotEmpty() && $spendByCategory->sum('total') > 0)
            <div class="p-4">
                @php $totalCategorySpend = $spendByCategory->sum('total') ?: 1; @endphp
                @foreach($spendByCategory as $category)
                <div class="mb-4 last:mb-0">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-ink-900">{{ $category['category'] }}</span>
                        <span class="text-smoke-600">KES {{ number_format($category['total'], 0) }}</span>
                    </div>
                    <div class="h-2 bg-smoke-100 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-500 rounded-full" style="width: {{ ($category['total'] / $totalCategorySpend) * 100 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-6 text-center text-smoke-500">
                <p>Category tracking not yet available.</p>
                <p class="text-sm mt-1">Categories are derived from requisition items.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Processing Times -->
    <div class="card mb-8">
        <div class="px-6 py-4 border-b border-smoke-200">
            <h2 class="font-medium text-ink-900">Processing Metrics</h2>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="text-4xl font-bold text-ink-900">
                    {{ $processingTimes['avg_req_to_po_days'] ?? '—' }}
                </div>
                <div class="text-sm text-smoke-500 mt-1">Avg. Days from Requisition to PO</div>
            </div>
            <div class="text-center">
                <div class="text-4xl font-bold text-ink-900">
                    {{ $poStatusBreakdown->sum('count') }}
                </div>
                <div class="text-sm text-smoke-500 mt-1">Total Purchase Orders</div>
            </div>
            <div class="text-center">
                <div class="text-4xl font-bold text-ink-900">
                    KES {{ number_format($poStatusBreakdown->sum('total'), 0) }}
                </div>
                <div class="text-sm text-smoke-500 mt-1">Total PO Value</div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('projects.requisitions.index', $project) }}" class="card p-4 hover:shadow-soft transition-shadow">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-50 rounded-lg">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <span class="font-medium text-ink-900">View Requisitions</span>
                    <p class="text-sm text-smoke-500">All requisition requests</p>
                </div>
            </div>
        </a>
        <a href="{{ route('projects.purchase-orders.index', $project) }}" class="card p-4 hover:shadow-soft transition-shadow">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-purple-50 rounded-lg">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <span class="font-medium text-ink-900">View Purchase Orders</span>
                    <p class="text-sm text-smoke-500">All POs for this project</p>
                </div>
            </div>
        </a>
        <a href="{{ route('projects.receipts.index', $project) }}" class="card p-4 hover:shadow-soft transition-shadow">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-green-50 rounded-lg">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                </div>
                <div>
                    <span class="font-medium text-ink-900">View Goods Receipts</span>
                    <p class="text-sm text-smoke-500">All received goods</p>
                </div>
            </div>
        </a>
    </div>
</x-workspace-layout>
