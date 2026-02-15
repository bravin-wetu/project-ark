@section('title', 'Budget Utilization')

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
                    <span class="text-ink-900 font-medium">Budget Utilization</span>
                </div>
                <h1 class="text-2xl font-semibold text-ink-900">Budget Utilization Report</h1>
                <p class="text-smoke-600 mt-1">Track budget allocation and spending across projects and departments.</p>
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
               class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg bg-primary-500 text-white">
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

        <!-- Budget vs Actual Chart -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-ink-900 mb-4">Budget vs Actual Spending (Monthly)</h3>
            <div class="h-72">
                <canvas id="budgetVsActualChart"></canvas>
            </div>
        </div>

        <!-- Project Budget Utilization -->
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-smoke-200">
                <h3 class="text-lg font-semibold text-ink-900">Project Budget Utilization</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-smoke-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Project</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-smoke-500 uppercase tracking-wider">Allocated</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-smoke-500 uppercase tracking-wider">Spent</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-smoke-500 uppercase tracking-wider">Available</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">Utilization</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-smoke-100">
                        @forelse($projectUtilization as $project)
                        <tr class="hover:bg-smoke-50 transition-colors">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium text-ink-900">{{ $project['name'] }}</p>
                                    <p class="text-xs text-smoke-500">{{ $project['code'] }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right font-medium text-ink-900">KES {{ number_format($project['allocated'], 0) }}</td>
                            <td class="px-6 py-4 text-right text-green-600">KES {{ number_format($project['spent'], 0) }}</td>
                            <td class="px-6 py-4 text-right text-blue-600">KES {{ number_format($project['available'], 0) }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-24 h-2 bg-smoke-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-300
                                            {{ $project['utilization'] >= 100 ? 'bg-red-500' : '' }}
                                            {{ $project['utilization'] >= 90 && $project['utilization'] < 100 ? 'bg-amber-500' : '' }}
                                            {{ $project['utilization'] >= 75 && $project['utilization'] < 90 ? 'bg-green-500' : '' }}
                                            {{ $project['utilization'] < 75 ? 'bg-blue-500' : '' }}" 
                                            style="width: {{ min($project['utilization'], 100) }}%"></div>
                                    </div>
                                    <span class="text-sm font-medium text-ink-900">{{ $project['utilization'] }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $project['status'] === 'over_budget' ? 'bg-red-100 text-red-700' : '' }}
                                    {{ $project['status'] === 'near_limit' ? 'bg-amber-100 text-amber-700' : '' }}
                                    {{ $project['status'] === 'on_track' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $project['status'] === 'under_utilized' ? 'bg-blue-100 text-blue-700' : '' }}">
                                    {{ str_replace('_', ' ', ucwords($project['status'], '_')) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-smoke-500">No project data available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Department Budget Utilization -->
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-smoke-200">
                <h3 class="text-lg font-semibold text-ink-900">Department Budget Utilization</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-smoke-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Fiscal Year</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-smoke-500 uppercase tracking-wider">Allocated</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-smoke-500 uppercase tracking-wider">Spent</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-smoke-500 uppercase tracking-wider">Available</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">Utilization</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-smoke-100">
                        @forelse($departmentUtilization as $dept)
                        <tr class="hover:bg-smoke-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-ink-900">{{ $dept['name'] }}</td>
                            <td class="px-6 py-4 text-smoke-600">{{ $dept['fiscal_year'] }}</td>
                            <td class="px-6 py-4 text-right font-medium text-ink-900">KES {{ number_format($dept['allocated'], 0) }}</td>
                            <td class="px-6 py-4 text-right text-green-600">KES {{ number_format($dept['spent'], 0) }}</td>
                            <td class="px-6 py-4 text-right text-blue-600">KES {{ number_format($dept['available'], 0) }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-24 h-2 bg-smoke-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-300
                                            {{ $dept['utilization'] >= 100 ? 'bg-red-500' : '' }}
                                            {{ $dept['utilization'] >= 90 && $dept['utilization'] < 100 ? 'bg-amber-500' : '' }}
                                            {{ $dept['utilization'] >= 75 && $dept['utilization'] < 90 ? 'bg-green-500' : '' }}
                                            {{ $dept['utilization'] < 75 ? 'bg-blue-500' : '' }}" 
                                            style="width: {{ min($dept['utilization'], 100) }}%"></div>
                                    </div>
                                    <span class="text-sm font-medium text-ink-900">{{ $dept['utilization'] }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $dept['status'] === 'over_budget' ? 'bg-red-100 text-red-700' : '' }}
                                    {{ $dept['status'] === 'near_limit' ? 'bg-amber-100 text-amber-700' : '' }}
                                    {{ $dept['status'] === 'on_track' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $dept['status'] === 'under_utilized' ? 'bg-blue-100 text-blue-700' : '' }}">
                                    {{ str_replace('_', ' ', ucwords($dept['status'], '_')) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-smoke-500">No department budget data available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Utilization Distribution Chart -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-ink-900 mb-4">Project Utilization Distribution</h3>
                <div class="h-64">
                    <canvas id="projectUtilChart"></canvas>
                </div>
            </div>
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-ink-900 mb-4">Department Utilization Distribution</h3>
                <div class="h-64">
                    <canvas id="deptUtilChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Budget vs Actual Chart
            const budgetVsActualData = @json($budgetVsActual);
            const budgetVsActualCtx = document.getElementById('budgetVsActualChart').getContext('2d');
            new Chart(budgetVsActualCtx, {
                type: 'bar',
                data: {
                    labels: budgetVsActualData.map(d => d.month),
                    datasets: [
                        {
                            label: 'Budget',
                            data: budgetVsActualData.map(d => d.budget),
                            backgroundColor: 'rgba(99, 102, 241, 0.5)',
                            borderColor: '#6366f1',
                            borderWidth: 1,
                        },
                        {
                            label: 'Actual',
                            data: budgetVsActualData.map(d => d.actual),
                            backgroundColor: 'rgba(34, 197, 94, 0.5)',
                            borderColor: '#22c55e',
                            borderWidth: 1,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': KES ' + context.parsed.y.toLocaleString();
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

            // Project Utilization Distribution (Horizontal Bar)
            const projectData = @json($projectUtilization);
            const projectUtilCtx = document.getElementById('projectUtilChart').getContext('2d');
            new Chart(projectUtilCtx, {
                type: 'bar',
                data: {
                    labels: projectData.slice(0, 8).map(d => d.name.substring(0, 20) + (d.name.length > 20 ? '...' : '')),
                    datasets: [{
                        label: 'Utilization %',
                        data: projectData.slice(0, 8).map(d => d.utilization),
                        backgroundColor: projectData.slice(0, 8).map(d => {
                            if (d.utilization >= 100) return '#ef4444';
                            if (d.utilization >= 90) return '#f59e0b';
                            if (d.utilization >= 75) return '#22c55e';
                            return '#3b82f6';
                        }),
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
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            max: 120,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });

            // Department Utilization Distribution (Horizontal Bar)
            const deptData = @json($departmentUtilization);
            const deptUtilCtx = document.getElementById('deptUtilChart').getContext('2d');
            new Chart(deptUtilCtx, {
                type: 'bar',
                data: {
                    labels: deptData.slice(0, 8).map(d => d.name.substring(0, 20) + (d.name.length > 20 ? '...' : '')),
                    datasets: [{
                        label: 'Utilization %',
                        data: deptData.slice(0, 8).map(d => d.utilization),
                        backgroundColor: deptData.slice(0, 8).map(d => {
                            if (d.utilization >= 100) return '#ef4444';
                            if (d.utilization >= 90) return '#f59e0b';
                            if (d.utilization >= 75) return '#22c55e';
                            return '#3b82f6';
                        }),
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
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            max: 120,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
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
