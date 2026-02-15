@section('title', 'Supplier Analytics')

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
                    <span class="text-ink-900 font-medium">Supplier Analytics</span>
                </div>
                <h1 class="text-2xl font-semibold text-ink-900">Supplier Analytics</h1>
                <p class="text-smoke-600 mt-1">Analyze supplier performance, spending distribution, and vendor relationships.</p>
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
                <a href="{{ route('analytics.export', ['type' => 'supplier_analysis', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
                   class="btn-secondary text-sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export CSV
                </a>
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
               class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg bg-smoke-100 text-smoke-700 hover:bg-smoke-200 transition-colors">
                Procurement
            </a>
            <a href="{{ route('analytics.suppliers') }}" 
               class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg bg-primary-500 text-white">
                Suppliers
            </a>
        </div>

        <!-- Summary Cards -->
        @php
            $totalSpend = $topSuppliers->sum('total_spend');
            $totalPOs = $topSuppliers->sum('po_count');
            $topSpender = $topSuppliers->first();
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="card p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-smoke-500">Total Suppliers</p>
                        <p class="text-2xl font-bold text-ink-900 mt-1">{{ $topSuppliers->count() }}</p>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-xl">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-smoke-500 mt-2">with active transactions</p>
            </div>

            <div class="card p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-smoke-500">Total Spend</p>
                        <p class="text-2xl font-bold text-green-600 mt-1">KES {{ number_format($totalSpend, 0) }}</p>
                    </div>
                    <div class="p-3 bg-green-50 rounded-xl">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-smoke-500 mt-2">during selected period</p>
            </div>

            <div class="card p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-smoke-500">Total POs</p>
                        <p class="text-2xl font-bold text-ink-900 mt-1">{{ number_format($totalPOs) }}</p>
                    </div>
                    <div class="p-3 bg-purple-50 rounded-xl">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-smoke-500 mt-2">purchase orders issued</p>
            </div>

            <div class="card p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-smoke-500">Top Supplier</p>
                        <p class="text-lg font-bold text-ink-900 mt-1 truncate" title="{{ $topSpender['name'] ?? '-' }}">
                            {{ Str::limit($topSpender['name'] ?? '-', 18) }}
                        </p>
                    </div>
                    <div class="p-3 bg-amber-50 rounded-xl">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-smoke-500 mt-2">KES {{ number_format($topSpender['total_spend'] ?? 0, 0) }} spent</p>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Suppliers Chart -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-ink-900 mb-4">Top 10 Suppliers by Spend</h3>
                <div class="h-72">
                    <canvas id="topSuppliersChart"></canvas>
                </div>
            </div>

            <!-- Spending by Category Chart -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-ink-900 mb-4">Supplier Spend by Category</h3>
                <div class="h-72">
                    <canvas id="categoryDistributionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Supplier Ranking Table -->
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-smoke-200">
                <h3 class="text-lg font-semibold text-ink-900">Supplier Performance Ranking</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-smoke-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Rank</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">PO Count</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-smoke-500 uppercase tracking-wider">Total Spend</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">% of Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Share</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-smoke-100">
                        @forelse($topSuppliers as $index => $supplier)
                        @php
                            $percentage = $totalSpend > 0 ? round(($supplier['total_spend'] / $totalSpend) * 100, 1) : 0;
                        @endphp
                        <tr class="hover:bg-smoke-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full 
                                    @if($index === 0) bg-yellow-100 text-yellow-700
                                    @elseif($index === 1) bg-gray-200 text-gray-700
                                    @elseif($index === 2) bg-amber-100 text-amber-700
                                    @else bg-smoke-100 text-smoke-600
                                    @endif
                                    text-sm font-semibold">
                                    @if($index < 3)
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 2l2.5 5 5.5.8-4 3.9.9 5.5L10 14.8l-4.9 2.4.9-5.5-4-3.9 5.5-.8L10 2z"/>
                                        </svg>
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-ink-900">{{ $supplier['name'] }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-smoke-100 text-smoke-700">
                                    {{ $supplier['category'] ?? 'General' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">
                                    {{ $supplier['po_count'] }} POs
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-green-600">
                                KES {{ number_format($supplier['total_spend'], 0) }}
                            </td>
                            <td class="px-6 py-4 text-center font-medium text-ink-900">
                                {{ $percentage }}%
                            </td>
                            <td class="px-6 py-4">
                                <div class="w-32 h-2 bg-smoke-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-primary-500 rounded-full transition-all duration-300" 
                                         style="width: {{ min($percentage, 100) }}%"></div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-smoke-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    <p class="text-smoke-500">No supplier data available for the selected period</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($topSuppliers->count() > 0)
                    <tfoot class="bg-smoke-50">
                        <tr>
                            <td colspan="3" class="px-6 py-3 font-semibold text-ink-900">Total</td>
                            <td class="px-6 py-3 text-center font-semibold text-ink-900">{{ $totalPOs }}</td>
                            <td class="px-6 py-3 text-right font-bold text-ink-900">KES {{ number_format($totalSpend, 0) }}</td>
                            <td class="px-6 py-3 text-center font-semibold text-ink-900">100%</td>
                            <td class="px-6 py-3"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Top Suppliers Chart (Horizontal Bar)
            const supplierData = @json($topSuppliers->take(10));
            const suppliersCtx = document.getElementById('topSuppliersChart').getContext('2d');
            
            new Chart(suppliersCtx, {
                type: 'bar',
                data: {
                    labels: supplierData.map(d => d.name.length > 20 ? d.name.substring(0, 20) + '...' : d.name),
                    datasets: [{
                        label: 'Spend',
                        data: supplierData.map(d => d.total_spend),
                        backgroundColor: [
                            '#6366f1', '#8b5cf6', '#a855f7', '#d946ef', '#ec4899',
                            '#f43f5e', '#f97316', '#fbbf24', '#84cc16', '#22c55e'
                        ],
                        borderWidth: 0,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'KES ' + context.parsed.x.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
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

            // Category Distribution Chart
            const categoryData = @json($spendingByCategory);
            const categoryCtx = document.getElementById('categoryDistributionChart').getContext('2d');
            const colors = ['#6366f1', '#8b5cf6', '#a855f7', '#d946ef', '#ec4899', '#f43f5e', '#f97316', '#fbbf24', '#84cc16', '#22c55e'];
            
            new Chart(categoryCtx, {
                type: 'pie',
                data: {
                    labels: categoryData.map(d => d.category),
                    datasets: [{
                        data: categoryData.map(d => d.total),
                        backgroundColor: categoryData.map((d, i) => colors[i % colors.length]),
                        borderWidth: 2,
                        borderColor: '#ffffff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                boxWidth: 12,
                                padding: 10,
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return context.label + ': KES ' + context.parsed.toLocaleString() + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
