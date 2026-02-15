@section('title', 'Budget Tracker - ' . $departmentBudget->name)

<x-workspace-layout :workspace="$departmentBudget" :workspaceType="'department-budgets'">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
            <a href="{{ route('department-budgets.show', $departmentBudget) }}" class="hover:text-ink-900 transition-colors">{{ $departmentBudget->name }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">Budget Tracker</span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ink-900">Budget Tracker</h1>
                <p class="text-smoke-600 mt-1">Track budget allocation, commitments, and spending across all budget lines.</p>
            </div>
            <a href="{{ route('department-budgets.edit', $departmentBudget) }}" 
               class="btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Budget Line
            </a>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <x-ui.stat-card 
            title="Total Allocated"
            :value="'$' . number_format($stats['total_allocated'], 2)"
            subtitle="{{ $stats['lines_count'] }} budget lines"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
        />
        
        <x-ui.stat-card 
            title="Committed"
            :value="'$' . number_format($stats['total_committed'], 2)"
            :subtitle="$stats['total_allocated'] > 0 ? round(($stats['total_committed'] / $stats['total_allocated']) * 100, 1) . '% of budget' : '0%'"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>'
        />
        
        <x-ui.stat-card 
            title="Spent"
            :value="'$' . number_format($stats['total_spent'], 2)"
            :subtitle="$stats['total_allocated'] > 0 ? round(($stats['total_spent'] / $stats['total_allocated']) * 100, 1) . '% utilized' : '0%'"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>'
        />
        
        <x-ui.stat-card 
            title="Remaining"
            :value="'$' . number_format($stats['total_remaining'], 2)"
            :subtitle="$stats['over_budget_count'] > 0 ? $stats['over_budget_count'] . ' lines over budget' : 'All lines within budget'"
            :changeType="$stats['over_budget_count'] > 0 ? 'negative' : 'positive'"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>'
        />
    </div>

    <!-- Overall Progress Bar -->
    <div class="card mb-8">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-ink-900">Overall Budget Utilization</h3>
                <span class="text-sm text-smoke-600">
                    {{ $stats['total_allocated'] > 0 ? round(($stats['total_spent'] / $stats['total_allocated']) * 100, 1) : 0 }}% used
                </span>
            </div>
            <div class="h-4 bg-smoke-100 rounded-full overflow-hidden">
                @php
                    $spentPercent = $stats['total_allocated'] > 0 ? min(($stats['total_spent'] / $stats['total_allocated']) * 100, 100) : 0;
                    $committedPercent = $stats['total_allocated'] > 0 ? min(($stats['total_committed'] / $stats['total_allocated']) * 100, 100) : 0;
                @endphp
                <div class="h-full flex">
                    <div class="bg-ink-900 transition-all duration-500" style="width: {{ $spentPercent }}%"></div>
                    <div class="bg-smoke-400 transition-all duration-500" style="width: {{ max($committedPercent - $spentPercent, 0) }}%"></div>
                </div>
            </div>
            <div class="flex items-center gap-6 mt-3 text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-ink-900 rounded-full"></span>
                    <span class="text-smoke-600">Spent</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-smoke-400 rounded-full"></span>
                    <span class="text-smoke-600">Committed</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-smoke-100 rounded-full"></span>
                    <span class="text-smoke-600">Available</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Budget Lines Table -->
    <div class="card animate-fade-in">
        <div class="p-6 border-b border-smoke-100">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-ink-900">Budget Lines</h3>
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <input type="text" 
                               placeholder="Search budget lines..." 
                               class="input-field pl-10 w-64"
                               id="searchBudgetLines">
                        <svg class="w-4 h-4 text-smoke-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        
        @if($budgetLines->isEmpty())
            <x-ui.empty-state
                title="No budget lines"
                description="Add budget lines to start tracking your department budget."
                actionHref="{{ route('department-budgets.edit', $departmentBudget) }}"
                actionText="Add Budget Lines"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
            />
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-smoke-50">
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Budget Line</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-smoke-500 uppercase tracking-wider">Allocated</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-smoke-500 uppercase tracking-wider">Committed</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-smoke-500 uppercase tracking-wider">Spent</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-smoke-500 uppercase tracking-wider">Remaining</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider w-48">Utilization</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-smoke-100" id="budgetLinesTable">
                        @foreach($budgetLines as $line)
                            @php
                                $remaining = $line->allocated - $line->spent;
                                $utilization = $line->allocated > 0 ? round(($line->spent / $line->allocated) * 100, 1) : 0;
                                $isOverBudget = $remaining < 0;
                                $isWarning = $utilization >= 80 && !$isOverBudget;
                            @endphp
                            <tr class="hover:bg-smoke-50 transition-colors budget-line-row">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-mono text-smoke-600">{{ $line->code }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-ink-900">{{ $line->name }}</div>
                                    @if($line->description)
                                        <div class="text-xs text-smoke-500 truncate max-w-xs">{{ $line->description }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($line->category)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-smoke-100 text-smoke-700">
                                            {{ $line->category->name }}
                                        </span>
                                    @else
                                        <span class="text-sm text-smoke-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="text-sm font-medium text-ink-900">${{ number_format($line->allocated, 2) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="text-sm text-smoke-600">${{ number_format($line->committed, 2) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="text-sm text-smoke-600">${{ number_format($line->spent, 2) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="text-sm font-medium {{ $isOverBudget ? 'text-red-600' : ($isWarning ? 'text-amber-600' : 'text-emerald-600') }}">
                                        {{ $isOverBudget ? '-' : '' }}${{ number_format(abs($remaining), 2) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-1 h-2 bg-smoke-100 rounded-full overflow-hidden">
                                            <div class="h-full transition-all duration-300 rounded-full {{ $isOverBudget ? 'bg-red-500' : ($isWarning ? 'bg-amber-500' : 'bg-emerald-500') }}" 
                                                 style="width: {{ min($utilization, 100) }}%"></div>
                                        </div>
                                        <span class="text-xs font-medium {{ $isOverBudget ? 'text-red-600' : ($isWarning ? 'text-amber-600' : 'text-smoke-600') }} w-12 text-right">
                                            {{ $utilization }}%
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-smoke-50">
                        <tr class="font-medium">
                            <td class="px-6 py-4" colspan="3">
                                <span class="text-sm text-ink-900">Total ({{ $budgetLines->count() }} lines)</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm text-ink-900">${{ number_format($stats['total_allocated'], 2) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm text-smoke-600">${{ number_format($stats['total_committed'], 2) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm text-smoke-600">${{ number_format($stats['total_spent'], 2) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm {{ $stats['total_remaining'] < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                    {{ $stats['total_remaining'] < 0 ? '-' : '' }}${{ number_format(abs($stats['total_remaining']), 2) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-smoke-600">
                                    {{ $stats['total_allocated'] > 0 ? round(($stats['total_spent'] / $stats['total_allocated']) * 100, 1) : 0 }}%
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        document.getElementById('searchBudgetLines')?.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            document.querySelectorAll('.budget-line-row').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    </script>
    @endpush
</x-workspace-layout>
