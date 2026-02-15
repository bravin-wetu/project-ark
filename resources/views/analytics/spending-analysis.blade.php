@section('title', 'Spending Analysis')

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
                    <span class="text-ink-900 font-medium">Spending Analysis</span>
                </div>
                <h1 class="text-2xl font-semibold text-ink-900">Spending Analysis</h1>
                <p class="text-smoke-600 mt-1">Analyze spending patterns by category, supplier, and time period.</p>
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
                <div class="relative" x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="btn-secondary text-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Export
                    </button>
                    <div x-show="open" @click.away="open = false" 
                         class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-smoke-200 py-1 z-10">
                        <a href="{{ route('analytics.export', ['type' => 'spending_by_category', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
                           class="block px-4 py-2 text-sm text-ink-700 hover:bg-smoke-50">By Category</a>
                        <a href="{{ route('analytics.export', ['type' => 'supplier_analysis', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
                           class="block px-4 py-2 text-sm text-ink-700 hover:bg-smoke-50">Supplier Analysis</a>
                        <a href="{{ route('analytics.export', ['type' => 'monthly_spending', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
                           class="block px-4 py-2 text-sm text-ink-700 hover:bg-smoke-50">Monthly Trend</a>
                    </div>
                </div>
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
               class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg bg-primary-500 text-white">
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

        <!-- Year-over-Year Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="card p-6 border-l-4 border-l-primary-500">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-smoke-600">Total Spending ({{ $yoyComparison['current_year'] }})</p>
                        <p class="text-3xl font-bold text-ink-900 mt-2">KES {{ number_format($yoyComparison['spending']['current'], 0) }}</p>
                    </div>
                    <div class="flex items-center {{ $yoyComparison['spending']['trend'] === 'up' ? 'text-red-600' : 'text-green-600' }}">
                        @if($yoyComparison['spending']['trend'] === 'up')
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        @else
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                        </svg>
                        @endif
                        <span class="ml-1 text-xl font-bold">{{ abs($yoyComparison['spending']['change_percent']) }}%</span>
                    </div>
                </div>
                <p class="text-sm text-smoke-500 mt-2">
                    {{ $yoyComparison['spending']['trend'] === 'up' ? 'Increased' : 'Decreased' }} from 
                    KES {{ number_format($yoyComparison['spending']['previous'], 0) }} ({{ $yoyComparison['previous_year'] }})
                </p>
            </div>

            <div class="card p-6">
                <p class="text-sm font-medium text-smoke-600">Spending Distribution</p>
                <div class="mt-4 h-48">
                    <canvas id="spendingByPeriodChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Monthly Spending Trend -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-ink-900 mb-4">Monthly Spending Trend</h3>
                <div class="h-72">
                    <canvas id="monthlyTrendChart"></canvas>
                </div>
            </div>

            <!-- Spending by Category -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-ink-900 mb-4">Spending by Category</h3>
                <div class="h-72">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Spending by Category Table -->
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-smoke-200">
                <h3 class="text-lg font-semibold text-ink-900">Category Breakdown</h3>
            </div>
            @php
                $totalSpend = $spendingByCategory->sum('total');
            @endphp
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-smoke-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-smoke-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">% of Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Distribution</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-smoke-100">
                        @forelse($spendingByCategory as $category)
                        @php
                            $percentage = $totalSpend > 0 ? round(($category['total'] / $totalSpend) * 100, 1) : 0;
                        @endphp
                        <tr class="hover:bg-smoke-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-3 h-3 rounded-full bg-primary-500"></div>
                                    <span class="font-medium text-ink-900">{{ $category['category'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-ink-900">KES {{ number_format($category['total'], 0) }}</td>
                            <td class="px-6 py-4 text-center text-smoke-600">{{ $percentage }}%</td>
                            <td class="px-6 py-4">
                                <div class="w-full h-2 bg-smoke-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-primary-500 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-smoke-500">No category data available</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($spendingByCategory->count() > 0)
                    <tfoot class="bg-smoke-50">
                        <tr>
                            <td class="px-6 py-3 font-semibold text-ink-900">Total</td>
                            <td class="px-6 py-3 text-right font-bold text-ink-900">KES {{ number_format($totalSpend, 0) }}</td>
                            <td class="px-6 py-3 text-center font-semibold text-ink-900">100%</td>
                            <td class="px-6 py-3"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <!-- Top Suppliers -->
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-smoke-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-ink-900">Top Suppliers by Spend</h3>
                <a href="{{ route('analytics.suppliers') }}" class="text-sm text-primary-600 hover:text-primary-700">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-smoke-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">PO Count</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-smoke-500 uppercase tracking-wider">Total Spend</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-smoke-100">
                        @forelse($topSuppliers as $index => $supplier)
                        <tr class="hover:bg-smoke-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-smoke-100 text-sm font-medium text-smoke-600">
                                    {{ $index + 1 }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-medium text-ink-900">{{ $supplier['name'] }}</td>
                            <td class="px-6 py-4 text-smoke-600">{{ $supplier['category'] ?? '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                                    {{ $supplier['po_count'] }} POs
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
            // Monthly Trend Chart
            const monthlyData = @json($monthlyTrend);
            const monthlyCtx = document.getElementById('monthlyTrendChart').getContext('2d');
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
                        legend: { display: false },
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

            // Category Chart (Pie)
            const categoryData = @json($spendingByCategory);
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            const colors = ['#6366f1', '#8b5cf6', '#a855f7', '#d946ef', '#ec4899', '#f43f5e', '#f97316', '#fbbf24', '#84cc16', '#22c55e'];
            
            new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: categoryData.map(d => d.category),
                    datasets: [{
                        data: categoryData.map(d => d.total),
                        backgroundColor: categoryData.map((d, i) => colors[i % colors.length]),
                        borderWidth: 0,
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
                    },
                    cutout: '50%'
                }
            });

            // Spending by Period (small pie for summary card)
            const periodCtx = document.getElementById('spendingByPeriodChart').getContext('2d');
            const firstHalf = monthlyData.slice(0, 6).reduce((sum, d) => sum + d.amount, 0);
            const secondHalf = monthlyData.slice(6).reduce((sum, d) => sum + d.amount, 0);
            
            new Chart(periodCtx, {
                type: 'pie',
                data: {
                    labels: ['H1', 'H2'],
                    datasets: [{
                        data: [firstHalf, secondHalf],
                        backgroundColor: ['#6366f1', '#22c55e'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': KES ' + context.parsed.toLocaleString();
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
