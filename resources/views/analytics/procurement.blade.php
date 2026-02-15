@section('title', 'Procurement Analytics')

<x-app-layout>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
                    <a href="{{ route('analytics.index') }}" class="hover:text-ink-900 transition-colors">Analytics</a>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span class="text-ink-900 font-medium">Procurement Analytics</span>
                </div>
                <h1 class="text-2xl font-semibold text-ink-900">Procurement Analytics</h1>
                <p class="text-smoke-600 mt-1">Track requisitions, purchase orders, and procurement performance.</p>
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
            </form>
        </div>

        <!-- Quick Navigation -->
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('analytics.index') }}" 
               class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg bg-smoke-100 text-smoke-700 hover:bg-smoke-200 transition-colors">
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
               class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg bg-primary-500 text-white">
                Procurement
            </a>
            <a href="{{ route('analytics.suppliers') }}" 
               class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg bg-smoke-100 text-smoke-700 hover:bg-smoke-200 transition-colors">
                Suppliers
            </a>
        </div>

        <!-- Requisition Pipeline -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-ink-900 mb-6">Requisition Pipeline</h3>
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                @php
                    $reqStatuses = [
                        'draft' => ['label' => 'Draft', 'color' => 'smoke', 'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
                        'submitted' => ['label' => 'Submitted', 'color' => 'blue', 'icon' => 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8'],
                        'pending' => ['label' => 'Pending', 'color' => 'amber', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                        'approved' => ['label' => 'Approved', 'color' => 'green', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        'rejected' => ['label' => 'Rejected', 'color' => 'red', 'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        'cancelled' => ['label' => 'Cancelled', 'color' => 'smoke', 'icon' => 'M6 18L18 6M6 6l12 12'],
                    ];
                    $totalReq = array_sum($pipeline['requisitions']);
                @endphp
                @foreach($reqStatuses as $status => $config)
                <div class="text-center p-4 rounded-xl bg-{{ $config['color'] }}-50 border border-{{ $config['color'] }}-200">
                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-{{ $config['color'] }}-100 mb-2">
                        <svg class="w-5 h-5 text-{{ $config['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $config['icon'] }}"/>
                        </svg>
                    </div>
                    <p class="text-2xl font-bold text-ink-900">{{ $pipeline['requisitions'][$status] ?? 0 }}</p>
                    <p class="text-xs text-smoke-600 mt-1">{{ $config['label'] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Requisition Status Chart -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-ink-900 mb-4">Requisition Status Distribution</h3>
                <div class="h-64">
                    <canvas id="requisitionStatusChart"></canvas>
                </div>
            </div>

            <!-- PO Status & Value Chart -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-ink-900 mb-4">Purchase Order Status</h3>
                <div class="h-64">
                    <canvas id="poStatusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Processing Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="card p-6 text-center">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-blue-100 mb-4">
                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-4xl font-bold text-ink-900">{{ $pipeline['avg_requisition_processing_days'] }}</p>
                <p class="text-sm text-smoke-600 mt-1">Days Avg. Requisition Processing</p>
            </div>

            <div class="card p-6 text-center">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-purple-100 mb-4">
                    <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-4xl font-bold text-ink-900">{{ $pipeline['avg_po_processing_days'] }}</p>
                <p class="text-sm text-smoke-600 mt-1">Days Avg. PO Fulfillment</p>
            </div>

            <div class="card p-6 text-center">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-green-100 mb-4">
                    <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-4xl font-bold text-ink-900">{{ $approvalMetrics['requisitions']['approval_rate'] }}%</p>
                <p class="text-sm text-smoke-600 mt-1">Requisition Approval Rate</p>
            </div>
        </div>

        <!-- Approval Metrics -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Requisition Approvals -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-ink-900 mb-4">Requisition Approvals</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-smoke-600">Total Submitted</span>
                        <span class="font-semibold text-ink-900">{{ $approvalMetrics['requisitions']['total'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-smoke-600">Approved</span>
                        <span class="font-semibold text-green-600">{{ $approvalMetrics['requisitions']['approved'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-smoke-600">Rejected</span>
                        <span class="font-semibold text-red-600">{{ $approvalMetrics['requisitions']['rejected'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-smoke-600">Pending</span>
                        <span class="font-semibold text-amber-600">{{ $approvalMetrics['requisitions']['pending'] }}</span>
                    </div>
                    <hr class="border-smoke-200">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-ink-900">Approval Rate</span>
                        <span class="text-xl font-bold text-primary-600">{{ $approvalMetrics['requisitions']['approval_rate'] }}%</span>
                    </div>
                </div>
            </div>

            <!-- Budget Revision Approvals -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-ink-900 mb-4">Budget Revision Approvals</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-smoke-600">Total Requested</span>
                        <span class="font-semibold text-ink-900">{{ $approvalMetrics['budget_revisions']['total'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-smoke-600">Approved</span>
                        <span class="font-semibold text-green-600">{{ $approvalMetrics['budget_revisions']['approved'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-smoke-600">Rejected</span>
                        <span class="font-semibold text-red-600">{{ $approvalMetrics['budget_revisions']['rejected'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-smoke-600">Pending</span>
                        <span class="font-semibold text-amber-600">{{ $approvalMetrics['budget_revisions']['pending'] }}</span>
                    </div>
                    <hr class="border-smoke-200">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-ink-900">Approval Rate</span>
                        <span class="text-xl font-bold text-primary-600">{{ $approvalMetrics['budget_revisions']['approval_rate'] }}%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Suppliers -->
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-smoke-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-ink-900">Procurement by Supplier</h3>
                <a href="{{ route('analytics.suppliers') }}" class="text-sm text-primary-600 hover:text-primary-700">View Details</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-smoke-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">PO Count</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-smoke-500 uppercase tracking-wider">Total Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-smoke-100">
                        @forelse($topSuppliers->take(10) as $index => $supplier)
                        <tr class="hover:bg-smoke-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full 
                                    {{ $index < 3 ? 'bg-amber-100 text-amber-700' : 'bg-smoke-100 text-smoke-600' }}
                                    text-sm font-medium">
                                    {{ $index + 1 }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-medium text-ink-900">{{ $supplier['name'] }}</td>
                            <td class="px-6 py-4 text-smoke-600">{{ $supplier['category'] ?? '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                                    {{ $supplier['po_count'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-green-600">KES {{ number_format($supplier['total_spend'], 0) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-smoke-500">No supplier data available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Requisition Status Chart
            const reqData = @json($pipeline['requisitions']);
            const reqCtx = document.getElementById('requisitionStatusChart').getContext('2d');
            new Chart(reqCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Draft', 'Submitted', 'Pending', 'Approved', 'Rejected', 'Cancelled'],
                    datasets: [{
                        data: [
                            reqData.draft || 0,
                            reqData.submitted || 0,
                            reqData.pending || 0,
                            reqData.approved || 0,
                            reqData.rejected || 0,
                            reqData.cancelled || 0
                        ],
                        backgroundColor: [
                            '#94a3b8',
                            '#60a5fa',
                            '#fbbf24',
                            '#34d399',
                            '#f87171',
                            '#cbd5e1'
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

            // PO Status Chart
            const poData = @json($pipeline['purchase_orders']);
            const poLabels = Object.keys(poData);
            const poValues = poLabels.map(k => poData[k]?.count || 0);
            const poCtx = document.getElementById('poStatusChart').getContext('2d');
            
            const poColorMap = {
                'draft': '#94a3b8',
                'pending': '#fbbf24',
                'approved': '#60a5fa',
                'sent': '#8b5cf6',
                'acknowledged': '#a855f7',
                'partially_received': '#f97316',
                'completed': '#34d399',
                'closed': '#22c55e',
                'cancelled': '#ef4444',
                'rejected': '#f87171'
            };
            
            new Chart(poCtx, {
                type: 'bar',
                data: {
                    labels: poLabels.map(l => l.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase())),
                    datasets: [{
                        label: 'Count',
                        data: poValues,
                        backgroundColor: poLabels.map(l => poColorMap[l] || '#6366f1'),
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
