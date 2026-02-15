@section('title', 'Analytics Dashboard')

<x-app-layout>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-ink-900">Analytics Dashboard</h1>
                <p class="text-smoke-600 mt-1">Organization-wide financial insights and performance metrics.</p>
            </div>
            
            <!-- Date Range Filter -->
            <form method="GET" class="flex flex-wrap items-center gap-2">
                <input type="date" name="start_date" 
                       value="{{ $startDate instanceof \Carbon\Carbon ? $startDate->format('Y-m-d') : $startDate }}" 
                       class="form-input text-sm">
                <span class="text-smoke-400">to</span>
                <input type="date" name="end_date" 
                       value="{{ $endDate instanceof \Carbon\Carbon ? $endDate->format('Y-m-d') : $endDate }}" 
                       class="form-input text-sm">
                <button type="submit" class="btn-secondary text-sm">Apply</button>
                <a href="{{ route('analytics.export', ['type' => 'budget_utilization']) }}" class="btn-secondary text-sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export
                </a>
            </form>
        </div>

        <!-- Quick Navigation -->
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('analytics.index') }}" 
               class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg bg-primary-500 text-white">
                Overview
            </a>
            <a href="{{ route('analytics.budget') }}" 
               class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg bg-smoke-100 text-smoke-700 hover:bg-smoke-200 transition-colors">
                Budget Utilization
            </a>
            <a href="{{ route('analytics.spending') }}" 
               class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg bg-smoke-100 text-smoke-700 hover:bg-smoke-200 transition-colors">
                Spending Analysis
            </a>
            <a href="{{ route('analytics.procurement') }}" 
               class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg bg-smoke-100 text-smoke-700 hover:bg-smoke-200 transition-colors">
                Procurement
            </a>
            <a href="{{ route('analytics.suppliers') }}" 
               class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg bg-smoke-100 text-smoke-700 hover:bg-smoke-200 transition-colors">
                Suppliers
            </a>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Total Budget -->
            <div class="card p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-smoke-500">Total Budget</p>
                        <p class="text-2xl font-bold text-ink-900 mt-1">KES {{ number_format($kpis['total_budget'], 0) }}</p>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-xl">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-center text-sm">
                    <span class="text-smoke-500">{{ $kpis['project_count'] }} projects, {{ $kpis['department_count'] }} departments</span>
                </div>
            </div>

            <!-- Total Spent -->
            <div class="card p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-smoke-500">Total Spent</p>
                        <p class="text-2xl font-bold text-green-600 mt-1">KES {{ number_format($kpis['total_spent'], 0) }}</p>
                    </div>
                    <div class="p-3 bg-green-50 rounded-xl">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-smoke-500">Utilization</span>
                        <span class="font-medium {{ $kpis['utilization_percent'] >= 90 ? 'text-amber-600' : 'text-ink-900' }}">{{ $kpis['utilization_percent'] }}%</span>
                    </div>
                    <div class="mt-1.5 h-2 bg-smoke-100 rounded-full overflow-hidden">
                        <div class="h-full bg-green-500 rounded-full transition-all duration-500" 
                             style="width: {{ min($kpis['utilization_percent'], 100) }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Purchase Orders -->
            <div class="card p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-smoke-500">Purchase Orders</p>
                        <p class="text-2xl font-bold text-ink-900 mt-1">{{ number_format($kpis['total_pos']) }}</p>
                    </div>
                    <div class="p-3 bg-purple-50 rounded-xl">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-center text-sm">
                    <span class="text-smoke-500">KES {{ number_format($kpis['total_po_value'], 0) }} total value</span>
                </div>
            </div>

            <!-- Requisitions -->
            <div class="card p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-smoke-500">Requisitions</p>
                        <p class="text-2xl font-bold text-ink-900 mt-1">{{ number_format($kpis['total_requisitions']) }}</p>
                    </div>
                    <div class="p-3 bg-amber-50 rounded-xl">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-center text-sm">
                    <span class="text-green-600 font-medium">{{ $kpis['requisition_approval_rate'] }}%</span>
                    <span class="text-smoke-500 ml-1">approval rate</span>
                </div>
            </div>
        </div>

        <!-- Year-over-Year Comparison -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="card p-5 border-l-4 border-l-blue-500">
                <h3 class="text-sm font-medium text-smoke-600 mb-3">Spending Comparison</h3>
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-3xl font-bold text-ink-900">KES {{ number_format($yoyComparison['spending']['current'], 0) }}</p>
                        <p class="text-sm text-smoke-500 mt-1">{{ $yoyComparison['current_year'] }} YTD</p>
                    </div>
                    <div class="text-right">
                        <div class="flex items-center {{ $yoyComparison['spending']['trend'] === 'up' ? 'text-red-600' : 'text-green-600' }}">
                            @if($yoyComparison['spending']['trend'] === 'up')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                            @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                            </svg>
                            @endif
                            <span class="ml-1 font-semibold">{{ abs($yoyComparison['spending']['change_percent']) }}%</span>
                        </div>
                        <p class="text-sm text-smoke-500">vs {{ $yoyComparison['previous_year'] }}</p>
                    </div>
                </div>
            </div>

            <div class="card p-5 border-l-4 border-l-purple-500">
                <h3 class="text-sm font-medium text-smoke-600 mb-3">Requisition Volume</h3>
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-3xl font-bold text-ink-900">{{ number_format($yoyComparison['requisitions']['current']) }}</p>
                        <p class="text-sm text-smoke-500 mt-1">{{ $yoyComparison['current_year'] }} YTD</p>
                    </div>
                    <div class="text-right">
                        <div class="flex items-center {{ $yoyComparison['requisitions']['trend'] === 'up' ? 'text-blue-600' : 'text-amber-600' }}">
                            @if($yoyComparison['requisitions']['trend'] === 'up')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                            @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                            </svg>
                            @endif
                            <span class="ml-1 font-semibold">{{ abs($yoyComparison['requisitions']['change_percent']) }}%</span>
                        </div>
                        <p class="text-sm text-smoke-500">vs {{ $yoyComparison['previous_year'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Monthly Spending Trend Chart -->
            <div class="card p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-ink-900">Monthly Spending Trend</h3>
                    <a href="{{ route('analytics.spending') }}" class="text-sm text-primary-600 hover:text-primary-700">View Details</a>
                </div>
                <div class="h-64">
                    <canvas id="monthlySpendingChart"></canvas>
                </div>
            </div>

            <!-- Procurement Pipeline Chart -->
            <div class="card p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-ink-900">Procurement Pipeline</h3>
                    <a href="{{ route('analytics.procurement') }}" class="text-sm text-primary-600 hover:text-primary-700">View Details</a>
                </div>
                <div class="h-64">
                    <canvas id="pipelineChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Processing Metrics & Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Processing Metrics -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-ink-900 mb-4">Processing Metrics</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-smoke-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-ink-900">Requisition Processing</p>
                                <p class="text-xs text-smoke-500">Avg. approval time</p>
                            </div>
                        </div>
                        <span class="text-lg font-bold text-ink-900">{{ $procurementPipeline['avg_requisition_processing_days'] }} days</span>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-smoke-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-ink-900">PO Fulfillment</p>
                                <p class="text-xs text-smoke-500">Avg. completion time</p>
                            </div>
                        </div>
                        <span class="text-lg font-bold text-ink-900">{{ $procurementPipeline['avg_po_processing_days'] }} days</span>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-smoke-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-ink-900">Active Suppliers</p>
                                <p class="text-xs text-smoke-500">Registered vendors</p>
                            </div>
                        </div>
                        <span class="text-lg font-bold text-ink-900">{{ $kpis['active_suppliers'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="lg:col-span-2 card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-ink-900">Recent Activity</h3>
                </div>
                <div class="space-y-3 max-h-80 overflow-y-auto">
                    @forelse($recentActivity as $activity)
                    <a href="{{ $activity['url'] }}" class="flex items-center gap-4 p-3 rounded-lg hover:bg-smoke-50 transition-colors">
                        <div class="p-2 rounded-lg {{ $activity['type'] === 'requisition' ? 'bg-blue-100' : 'bg-purple-100' }}">
                            @if($activity['type'] === 'requisition')
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            @else
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-ink-900 truncate">{{ $activity['title'] }}</p>
                            <p class="text-xs text-smoke-500">{{ $activity['description'] }}</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full
                                {{ $activity['status'] === 'approved' || $activity['status'] === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $activity['status'] === 'pending' || $activity['status'] === 'submitted' ? 'bg-amber-100 text-amber-700' : '' }}
                                {{ $activity['status'] === 'draft' ? 'bg-smoke-100 text-smoke-700' : '' }}
                                {{ $activity['status'] === 'rejected' || $activity['status'] === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                            ">{{ ucfirst($activity['status']) }}</span>
                            <p class="text-xs text-smoke-400 mt-1">{{ $activity['date']->diffForHumans() }}</p>
                        </div>
                    </a>
                    @empty
                    <p class="text-sm text-smoke-500 text-center py-4">No recent activity</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Monthly Spending Chart
            const monthlyData = @json($monthlySpending);
            const monthlyCtx = document.getElementById('monthlySpendingChart').getContext('2d');
            new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: monthlyData.map(d => d.month),
                    datasets: [{
                        label: 'Spending',
                        data: monthlyData.map(d => d.amount),
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'KES ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'KES ' + (value / 1000000).toFixed(1) + 'M';
                                }
                            }
                        }
                    }
                }
            });

            // Pipeline Chart (Doughnut)
            const requisitionData = @json($procurementPipeline['requisitions']);
            const pipelineCtx = document.getElementById('pipelineChart').getContext('2d');
            new Chart(pipelineCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Draft', 'Submitted', 'Pending', 'Approved', 'Rejected'],
                    datasets: [{
                        data: [
                            requisitionData.draft || 0,
                            requisitionData.submitted || 0,
                            requisitionData.pending || 0,
                            requisitionData.approved || 0,
                            requisitionData.rejected || 0
                        ],
                        backgroundColor: [
                            '#94a3b8',
                            '#60a5fa',
                            '#fbbf24',
                            '#34d399',
                            '#f87171'
                        ],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    },
                    cutout: '60%'
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
