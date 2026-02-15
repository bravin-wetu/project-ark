@section('title', 'Reports - ' . $project->name)

<x-workspace-layout :workspace="$project" :workspaceType="'projects'">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
            <a href="{{ route('projects.show', $project) }}" class="hover:text-ink-900 transition-colors">{{ $project->name }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">Reports & Analytics</span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ink-900">Reports & Analytics</h1>
                <p class="text-smoke-600 mt-1">Financial insights, procurement analytics, and project performance metrics.</p>
            </div>
            <!-- Date Range Filter -->
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

    <!-- Budget Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Total Budget</div>
            <div class="text-2xl font-semibold text-ink-900">KES {{ number_format($budgetSummary['total_budget'], 0) }}</div>
        </div>
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Committed</div>
            <div class="text-2xl font-semibold text-amber-600">KES {{ number_format($budgetSummary['committed'], 0) }}</div>
        </div>
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Spent</div>
            <div class="text-2xl font-semibold text-green-600">KES {{ number_format($budgetSummary['spent'], 0) }}</div>
        </div>
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Available</div>
            <div class="text-2xl font-semibold text-blue-600">KES {{ number_format($budgetSummary['available'], 0) }}</div>
        </div>
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Utilization</div>
            <div class="text-2xl font-semibold text-ink-900">{{ $budgetSummary['utilization_percent'] }}%</div>
            <div class="mt-2 h-2 bg-smoke-100 rounded-full overflow-hidden">
                <div class="h-full bg-primary-500 rounded-full" style="width: {{ min($budgetSummary['utilization_percent'], 100) }}%"></div>
            </div>
        </div>
    </div>

    <!-- Procurement Metrics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="card p-4 border-l-4 border-l-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-smoke-500">Requisitions</div>
                    <div class="text-2xl font-semibold text-ink-900">{{ $procurementMetrics['requisitions_count'] }}</div>
                </div>
                <div class="p-2 bg-blue-50 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            @if($procurementMetrics['requisitions_pending'] > 0)
            <div class="mt-2 text-xs text-amber-600">{{ $procurementMetrics['requisitions_pending'] }} pending approval</div>
            @endif
        </div>
        <div class="card p-4 border-l-4 border-l-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-smoke-500">Purchase Orders</div>
                    <div class="text-2xl font-semibold text-ink-900">{{ $procurementMetrics['pos_count'] }}</div>
                </div>
                <div class="p-2 bg-purple-50 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-2 text-xs text-smoke-500">KES {{ number_format($procurementMetrics['pos_value'], 0) }} total value</div>
        </div>
        <div class="card p-4 border-l-4 border-l-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-smoke-500">Goods Receipts</div>
                    <div class="text-2xl font-semibold text-ink-900">{{ $procurementMetrics['receipts_count'] }}</div>
                </div>
                <div class="p-2 bg-green-50 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="card p-4 border-l-4 border-l-amber-500">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-smoke-500">Active Suppliers</div>
                    <div class="text-2xl font-semibold text-ink-900">{{ $procurementMetrics['suppliers_used'] }}</div>
                </div>
                <div class="p-2 bg-amber-50 rounded-lg">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Cards Grid -->
    <h2 class="text-lg font-medium text-ink-900 mb-4">Available Reports</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- Budget Utilization Report -->
        <a href="{{ route('projects.reports.budget', $project) }}" class="card p-6 hover:shadow-soft transition-shadow group">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-blue-50 rounded-xl group-hover:bg-blue-100 transition-colors">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-medium text-ink-900 group-hover:text-primary-600 transition-colors">Budget Utilization</h3>
                    <p class="text-sm text-smoke-600 mt-1">Budget allocation vs spending by line item with trends.</p>
                    <span class="inline-flex items-center gap-1 mt-3 text-sm text-primary-600">
                        View Report
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                </div>
            </div>
        </a>

        <!-- Procurement Summary -->
        <a href="{{ route('projects.reports.procurement', $project) }}" class="card p-6 hover:shadow-soft transition-shadow group">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-purple-50 rounded-xl group-hover:bg-purple-100 transition-colors">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-medium text-ink-900 group-hover:text-primary-600 transition-colors">Procurement Analysis</h3>
                    <p class="text-sm text-smoke-600 mt-1">Spend by supplier, category analysis, and PO metrics.</p>
                    <span class="inline-flex items-center gap-1 mt-3 text-sm text-primary-600">
                        View Report
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                </div>
            </div>
        </a>

        <!-- Supplier Performance -->
        <a href="{{ route('projects.reports.suppliers', $project) }}" class="card p-6 hover:shadow-soft transition-shadow group">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-amber-50 rounded-xl group-hover:bg-amber-100 transition-colors">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-medium text-ink-900 group-hover:text-primary-600 transition-colors">Supplier Performance</h3>
                    <p class="text-sm text-smoke-600 mt-1">Delivery metrics, spending, and supplier rankings.</p>
                    <span class="inline-flex items-center gap-1 mt-3 text-sm text-primary-600">
                        View Report
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                </div>
            </div>
        </a>

        <!-- Asset Report -->
        <a href="{{ route('projects.reports.assets', $project) }}" class="card p-6 hover:shadow-soft transition-shadow group">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-green-50 rounded-xl group-hover:bg-green-100 transition-colors">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-medium text-ink-900 group-hover:text-primary-600 transition-colors">Asset Register</h3>
                    <p class="text-sm text-smoke-600 mt-1">Asset valuation, depreciation, and location tracking.</p>
                    <span class="inline-flex items-center gap-1 mt-3 text-sm text-primary-600">
                        View Report
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                </div>
            </div>
        </a>

        <!-- Stock Report -->
        <a href="{{ route('projects.reports.stock', $project) }}" class="card p-6 hover:shadow-soft transition-shadow group">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-red-50 rounded-xl group-hover:bg-red-100 transition-colors">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-medium text-ink-900 group-hover:text-primary-600 transition-colors">Stock/Inventory</h3>
                    <p class="text-sm text-smoke-600 mt-1">Stock levels, valuation, movement, and alerts.</p>
                    <span class="inline-flex items-center gap-1 mt-3 text-sm text-primary-600">
                        View Report
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                </div>
            </div>
        </a>

        <!-- Export Options Card -->
        <div class="card p-6 bg-smoke-50 border-dashed">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-smoke-100 rounded-xl">
                    <svg class="w-6 h-6 text-smoke-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-medium text-ink-900">Export Reports</h3>
                    <p class="text-sm text-smoke-600 mt-1">Download reports in PDF or Excel format for sharing.</p>
                    <div class="flex gap-2 mt-3">
                        <span class="inline-flex px-2 py-1 text-xs bg-smoke-200 text-smoke-600 rounded">PDF</span>
                        <span class="inline-flex px-2 py-1 text-xs bg-smoke-200 text-smoke-600 rounded">Excel</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    @if(count($recentActivity) > 0)
    <div class="card">
        <div class="px-6 py-4 border-b border-smoke-200">
            <h2 class="font-medium text-ink-900">Recent Activity</h2>
        </div>
        <div class="divide-y divide-smoke-100">
            @foreach($recentActivity as $activity)
            <div class="px-6 py-3 flex items-center gap-4">
                <div class="p-2 rounded-full 
                    @if($activity['type'] == 'requisition') bg-blue-50 text-blue-600
                    @elseif($activity['type'] == 'purchase_order') bg-purple-50 text-purple-600
                    @else bg-smoke-100 text-smoke-600
                    @endif">
                    @if($activity['type'] == 'requisition')
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    @else
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    @endif
                </div>
                <div class="flex-1">
                    <div class="font-medium text-ink-900">{{ $activity['title'] }}</div>
                    <div class="text-sm text-smoke-500">{{ $activity['description'] }}</div>
                </div>
                <div class="text-right">
                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full
                        @if(in_array($activity['status'], ['approved', 'completed', 'closed'])) bg-green-100 text-green-700
                        @elseif(in_array($activity['status'], ['pending', 'pending_approval'])) bg-amber-100 text-amber-700
                        @elseif(in_array($activity['status'], ['rejected', 'cancelled'])) bg-red-100 text-red-700
                        @else bg-smoke-100 text-smoke-700
                        @endif">
                        {{ str_replace('_', ' ', ucfirst($activity['status'])) }}
                    </span>
                    <div class="text-xs text-smoke-400 mt-1">{{ $activity['date']->diffForHumans() }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</x-workspace-layout>
