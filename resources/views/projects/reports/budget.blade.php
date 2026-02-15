@section('title', 'Budget Report - ' . $project->name)

<x-workspace-layout :workspace="$project" :workspaceType="'projects'">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
            <a href="{{ route('projects.reports.index', $project) }}" class="hover:text-ink-900 transition-colors">Reports</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">Budget Utilization</span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ink-900">Budget Utilization Report</h1>
                <p class="text-smoke-600 mt-1">Detailed breakdown of budget allocation vs spending.</p>
            </div>
            <div class="flex items-center gap-2">
                <form method="GET" class="flex items-center gap-2">
                    <input type="date" name="start_date" value="{{ $startDate instanceof \Carbon\Carbon ? $startDate->format('Y-m-d') : $startDate }}" 
                           class="form-input text-sm">
                    <span class="text-smoke-400">to</span>
                    <input type="date" name="end_date" value="{{ $endDate instanceof \Carbon\Carbon ? $endDate->format('Y-m-d') : $endDate }}" 
                           class="form-input text-sm">
                    <button type="submit" class="btn-secondary text-sm">Apply</button>
                </form>
                <div class="flex gap-1 border-l border-smoke-200 pl-2 ml-2">
                    <a href="{{ route('projects.reports.export.pdf', $project) }}?report=budget" class="btn-secondary text-sm" title="Print/PDF">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                    </a>
                    <a href="{{ route('projects.reports.export.excel', $project) }}?report=budget" class="btn-secondary text-sm" title="Export CSV">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Total Budget</div>
            <div class="text-2xl font-semibold text-ink-900">KES {{ number_format($summary['total_budget'], 0) }}</div>
        </div>
        <div class="card p-4 border-l-4 border-l-amber-500">
            <div class="text-sm text-smoke-500">Committed</div>
            <div class="text-2xl font-semibold text-amber-600">KES {{ number_format($summary['total_committed'], 0) }}</div>
            <div class="text-xs text-smoke-400 mt-1">{{ $summary['total_budget'] > 0 ? round(($summary['total_committed'] / $summary['total_budget']) * 100, 1) : 0 }}% of budget</div>
        </div>
        <div class="card p-4 border-l-4 border-l-green-500">
            <div class="text-sm text-smoke-500">Spent</div>
            <div class="text-2xl font-semibold text-green-600">KES {{ number_format($summary['total_spent'], 0) }}</div>
            <div class="text-xs text-smoke-400 mt-1">{{ $summary['overall_utilization'] }}% utilization</div>
        </div>
        <div class="card p-4 border-l-4 border-l-blue-500">
            <div class="text-sm text-smoke-500">Available</div>
            <div class="text-2xl font-semibold text-blue-600">KES {{ number_format($summary['total_available'], 0) }}</div>
        </div>
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Utilization</div>
            <div class="text-2xl font-semibold text-ink-900">{{ $summary['overall_utilization'] }}%</div>
            <div class="mt-2 h-2 bg-smoke-100 rounded-full overflow-hidden">
                <div class="h-full bg-primary-500 rounded-full transition-all" style="width: {{ min($summary['overall_utilization'], 100) }}%"></div>
            </div>
        </div>
    </div>

    <!-- Monthly Spending Chart -->
    @if(!empty($monthlySpending))
    <div class="card p-6 mb-8">
        <h2 class="font-medium text-ink-900 mb-4">Monthly Spending Trend</h2>
        <div class="h-64 flex items-end gap-2">
            @php
                $maxSpend = max($monthlySpending) ?: 1;
            @endphp
            @foreach($monthlySpending as $month => $amount)
            <div class="flex-1 flex flex-col items-center">
                <div class="w-full bg-primary-500 rounded-t transition-all hover:bg-primary-600" 
                     style="height: {{ ($amount / $maxSpend) * 200 }}px"
                     title="KES {{ number_format($amount, 0) }}"></div>
                <div class="text-xs text-smoke-500 mt-2 transform -rotate-45 origin-top-left">{{ $month }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Budget Lines Table -->
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-smoke-200 flex items-center justify-between">
            <h2 class="font-medium text-ink-900">Budget Lines Breakdown</h2>
            <span class="text-sm text-smoke-500">{{ $budgetLines->count() }} lines</span>
        </div>
        @if($budgetLines->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-smoke-200">
                <thead class="bg-smoke-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Code</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Budget Line</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-smoke-500 uppercase tracking-wider">Allocated</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-smoke-500 uppercase tracking-wider">Committed</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-smoke-500 uppercase tracking-wider">Spent</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-smoke-500 uppercase tracking-wider">Available</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">Utilization</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-smoke-100">
                    @foreach($budgetLines as $line)
                    @php
                        $isOverBudget = $line['committed'] > $line['allocated'];
                        $nearLimit = $line['utilization'] >= 80 && $line['utilization'] < 100;
                    @endphp
                    <tr class="hover:bg-smoke-50 {{ $isOverBudget ? 'bg-red-50' : '' }}">
                        <td class="px-4 py-3 font-mono text-sm text-smoke-600">{{ $line['code'] }}</td>
                        <td class="px-4 py-3 font-medium text-ink-900">{{ $line['name'] }}</td>
                        <td class="px-4 py-3 text-right text-ink-900">KES {{ number_format($line['allocated'], 0) }}</td>
                        <td class="px-4 py-3 text-right text-amber-600">KES {{ number_format($line['committed'], 0) }}</td>
                        <td class="px-4 py-3 text-right text-green-600">KES {{ number_format($line['spent'], 0) }}</td>
                        <td class="px-4 py-3 text-right {{ $line['available'] < 0 ? 'text-red-600 font-medium' : 'text-blue-600' }}">
                            KES {{ number_format($line['available'], 0) }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2 bg-smoke-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all
                                        {{ $isOverBudget ? 'bg-red-500' : ($nearLimit ? 'bg-amber-500' : 'bg-green-500') }}" 
                                         style="width: {{ min($line['utilization'], 100) }}%"></div>
                                </div>
                                <span class="text-sm {{ $isOverBudget ? 'text-red-600 font-medium' : ($nearLimit ? 'text-amber-600' : 'text-smoke-600') }}">
                                    {{ $line['utilization'] }}%
                                </span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-smoke-50 border-t-2 border-smoke-200">
                    <tr class="font-medium">
                        <td class="px-4 py-3"></td>
                        <td class="px-4 py-3 text-ink-900">Total</td>
                        <td class="px-4 py-3 text-right text-ink-900">KES {{ number_format($summary['total_budget'], 0) }}</td>
                        <td class="px-4 py-3 text-right text-amber-600">KES {{ number_format($summary['total_committed'], 0) }}</td>
                        <td class="px-4 py-3 text-right text-green-600">KES {{ number_format($summary['total_spent'], 0) }}</td>
                        <td class="px-4 py-3 text-right text-blue-600">KES {{ number_format($summary['total_available'], 0) }}</td>
                        <td class="px-4 py-3 text-center text-ink-900">{{ $summary['overall_utilization'] }}%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="p-12 text-center text-smoke-500">
            <svg class="w-12 h-12 mx-auto text-smoke-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p>No budget lines configured for this project.</p>
            <a href="{{ route('projects.budget.index', $project) }}" class="text-primary-600 hover:text-primary-700 mt-2 inline-block">
                Configure Budget →
            </a>
        </div>
        @endif
    </div>

    <!-- Legend -->
    <div class="mt-6 flex items-center gap-6 text-sm text-smoke-600">
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
            <span>Under 80%</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 bg-amber-500 rounded-full"></div>
            <span>80-100%</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
            <span>Over Budget</span>
        </div>
    </div>
</x-workspace-layout>
