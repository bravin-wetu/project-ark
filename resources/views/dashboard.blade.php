@section('title', 'Dashboard')

<x-app-layout>
    <!-- Welcome Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-ink-900">Welcome back, {{ Auth::user()->name }}</h1>
        <p class="text-smoke-600 mt-1">Here's an overview of your procurement and budget activities.</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- Donor Funds -->
        <x-ui.stat-card
            title="Donor Funds"
            :value="'$' . number_format($stats['donor_funds'] ?? 0)"
            :subtitle="($stats['active_projects'] ?? 0) . ' active projects'"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
        />

        <!-- Internal Budgets -->
        <x-ui.stat-card
            title="Internal Budgets"
            :value="'$' . number_format($stats['internal_budgets'] ?? 0)"
            :subtitle="($stats['active_departments'] ?? 0) . ' active departments'"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>'
        />

        <!-- Total Commitments -->
        <x-ui.stat-card
            title="Total Commitments"
            :value="'$' . number_format($stats['total_commitments'] ?? 0)"
            :subtitle="($stats['utilization'] ?? 0) . '% utilized'"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>'
        />

        <!-- Total Remaining -->
        <x-ui.stat-card
            title="Total Remaining"
            :value="'$' . number_format($stats['total_remaining'] ?? 0)"
            subtitle="Available balance"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>'
        />
    </div>

    <!-- Budget Control Quick Actions (Sprint 10) -->
    @if(($stats['pending_revisions'] ?? 0) > 0 || ($stats['active_locks'] ?? 0) > 0)
    <section class="mb-10">
        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg p-6 border border-indigo-100">
            <div class="flex items-start justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-ink-900">Budget Control Center</h3>
                        <p class="text-sm text-smoke-600">Manage budget approvals and controls</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    @if(($stats['pending_revisions'] ?? 0) > 0)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                            {{ $stats['pending_revisions'] }} pending revision{{ $stats['pending_revisions'] > 1 ? 's' : '' }}
                        </span>
                    @endif
                    @if(($stats['active_locks'] ?? 0) > 0)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            {{ $stats['active_locks'] }} locked budget{{ $stats['active_locks'] > 1 ? 's' : '' }}
                        </span>
                    @endif
                </div>
            </div>
            
            @if(count($pendingRevisions ?? []) > 0)
            <div class="mt-4 space-y-2">
                @foreach($pendingRevisions as $revision)
                <div class="flex items-center justify-between bg-white rounded-lg p-3 shadow-sm">
                    <div class="flex items-center space-x-3">
                        <span class="flex-shrink-0 w-2 h-2 bg-yellow-400 rounded-full"></span>
                        <div>
                            <p class="text-sm font-medium text-ink-900">{{ $revision->budgetLine->name }}</p>
                            <p class="text-xs text-smoke-500">
                                {{ $revision->reference_number }} &middot; 
                                {{ $revision->formatted_change }} &middot;
                                by {{ $revision->user->name }}
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('budget-control.show-revision', $revision) }}" 
                       class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                        Review
                    </a>
                </div>
                @endforeach
            </div>
            @endif

            <div class="mt-4 flex items-center space-x-4">
                <a href="{{ route('budget-control.pending-revisions') }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Review All Revisions
                </a>
            </div>
        </div>
    </section>
    @endif

    <!-- Donor Projects Section -->
    <section class="mb-10">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-lg font-semibold text-ink-900">Donor Projects</h2>
                <p class="text-sm text-smoke-600">Donor-funded project budgets</p>
            </div>
            <x-ui.button href="{{ route('projects.create') }}" variant="primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create Project
            </x-ui.button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($projects ?? [] as $index => $project)
            <a href="{{ route('projects.show', $project) }}" class="card card-hover p-6 animate-fade-in-up" style="animation-delay: {{ $index * 75 }}ms">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="font-semibold text-ink-900">{{ $project->name }}</h3>
                        <p class="text-sm text-smoke-500">{{ $project->donor->name ?? 'No Donor' }}</p>
                    </div>
                    <x-ui.badge :variant="$project->status === 'active' ? 'dark' : ($project->status === 'draft' ? 'default' : 'success')">
                        {{ ucfirst($project->status) }}
                    </x-ui.badge>
                </div>

                <div class="mb-4">
                    <div class="flex items-center justify-between text-sm mb-2">
                        <span class="text-smoke-600">Budget Utilization</span>
                        <span class="font-medium text-ink-900">{{ $project->utilization }}%</span>
                    </div>
                    <x-ui.progress :value="$project->utilization" />
                </div>

                <div class="flex items-center justify-between text-sm">
                    <span class="text-smoke-600">
                        ${{ number_format($project->spent) }} / ${{ number_format($project->allocated) }}
                    </span>
                    <svg class="w-4 h-4 text-smoke-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
            @empty
            <div class="col-span-3">
                <x-ui.empty-state
                    title="No projects yet"
                    description="Get started by creating a new donor-funded project."
                    actionHref="{{ route('projects.create') }}"
                    actionText="Create Project"
                    icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'
                />
            </div>
            @endforelse
        </div>
        
        @if(count($projects ?? []) > 0)
        <div class="mt-6 text-center">
            <a href="{{ route('projects.index') }}" class="text-sm font-medium text-ink-600 hover:text-ink-900 transition-colors">
                View all projects →
            </a>
        </div>
        @endif
    </section>

    <!-- Department Budgets Section -->
    <section>
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-lg font-semibold text-ink-900">Department Budgets</h2>
                <p class="text-sm text-smoke-600">Internal operational budgets</p>
            </div>
            <x-ui.button href="{{ route('department-budgets.create') }}" variant="secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Create Budget
            </x-ui.button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($departmentBudgets ?? [] as $index => $budget)
            <a href="{{ route('department-budgets.show', $budget) }}" class="card card-hover p-6 animate-fade-in-up" style="animation-delay: {{ $index * 75 }}ms">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="font-semibold text-ink-900">{{ $budget->department->name ?? 'Unknown' }}</h3>
                        <p class="text-sm text-smoke-500">{{ $budget->fiscal_year }}</p>
                    </div>
                    <x-ui.badge :variant="$budget->status === 'active' ? 'dark' : ($budget->status === 'draft' ? 'default' : 'success')">
                        {{ ucfirst($budget->status) }}
                    </x-ui.badge>
                </div>

                <div class="mb-4">
                    <div class="flex items-center justify-between text-sm mb-2">
                        <span class="text-smoke-600">Budget Utilization</span>
                        <span class="font-medium text-ink-900">{{ $budget->utilization }}%</span>
                    </div>
                    <x-ui.progress :value="$budget->utilization" />
                </div>

                <div class="flex items-center justify-between text-sm">
                    <span class="text-smoke-600">
                        ${{ number_format($budget->spent) }} / ${{ number_format($budget->allocated) }}
                    </span>
                    <svg class="w-4 h-4 text-smoke-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
            @empty
            <div class="col-span-3">
                <x-ui.empty-state
                    title="No department budgets yet"
                    description="Create a department budget to manage internal operations."
                    actionHref="{{ route('department-budgets.create') }}"
                    actionText="Create Budget"
                    icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>'
                />
            </div>
            @endforelse
        </div>
        
        @if(count($departmentBudgets ?? []) > 0)
        <div class="mt-6 text-center">
            <a href="{{ route('department-budgets.index') }}" class="text-sm font-medium text-ink-600 hover:text-ink-900 transition-colors">
                View all budgets →
            </a>
        </div>
        @endif
    </section>
</x-app-layout>
