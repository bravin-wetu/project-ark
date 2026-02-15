@section('title', 'Reports - ' . $departmentBudget->name)

<x-workspace-layout :workspace="$departmentBudget" :workspaceType="'department-budgets'">
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
            <a href="{{ route('department-budgets.show', $departmentBudget) }}" class="hover:text-ink-900 transition-colors">{{ $departmentBudget->name }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">Reports</span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ink-900">Reports</h1>
                <p class="text-smoke-600 mt-1">Generate and export department budget financial and procurement reports.</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="card p-6 hover:shadow-soft transition-shadow cursor-pointer opacity-50">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-smoke-100 rounded-xl">
                    <svg class="w-6 h-6 text-smoke-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-medium text-ink-900">Budget Utilization</h3>
                    <p class="text-sm text-smoke-600 mt-1">Detailed breakdown of budget allocation vs spending by line item.</p>
                    <span class="inline-block mt-3 text-xs font-medium text-smoke-500 bg-smoke-100 px-2 py-1 rounded">Coming in Sprint 8</span>
                </div>
            </div>
        </div>

        <div class="card p-6 hover:shadow-soft transition-shadow cursor-pointer opacity-50">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-smoke-100 rounded-xl">
                    <svg class="w-6 h-6 text-smoke-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-medium text-ink-900">Procurement Summary</h3>
                    <p class="text-sm text-smoke-600 mt-1">Overview of all procurement activity including requisitions, POs, and receipts.</p>
                    <span class="inline-block mt-3 text-xs font-medium text-smoke-500 bg-smoke-100 px-2 py-1 rounded">Coming in Sprint 8</span>
                </div>
            </div>
        </div>

        <div class="card p-6 hover:shadow-soft transition-shadow cursor-pointer opacity-50">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-smoke-100 rounded-xl">
                    <svg class="w-6 h-6 text-smoke-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-medium text-ink-900">Vendor Analysis</h3>
                    <p class="text-sm text-smoke-600 mt-1">Performance metrics and spending by vendor/supplier.</p>
                    <span class="inline-block mt-3 text-xs font-medium text-smoke-500 bg-smoke-100 px-2 py-1 rounded">Coming in Sprint 8</span>
                </div>
            </div>
        </div>

        <div class="card p-6 hover:shadow-soft transition-shadow cursor-pointer opacity-50">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-smoke-100 rounded-xl">
                    <svg class="w-6 h-6 text-smoke-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-medium text-ink-900">Asset Register</h3>
                    <p class="text-sm text-smoke-600 mt-1">Complete listing of all department assets with depreciation and location.</p>
                    <span class="inline-block mt-3 text-xs font-medium text-smoke-500 bg-smoke-100 px-2 py-1 rounded">Coming in Sprint 8</span>
                </div>
            </div>
        </div>

        <div class="card p-6 hover:shadow-soft transition-shadow cursor-pointer opacity-50">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-smoke-100 rounded-xl">
                    <svg class="w-6 h-6 text-smoke-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-medium text-ink-900">Audit Trail</h3>
                    <p class="text-sm text-smoke-600 mt-1">Complete audit log of all actions and changes for compliance.</p>
                    <span class="inline-block mt-3 text-xs font-medium text-smoke-500 bg-smoke-100 px-2 py-1 rounded">Coming in Sprint 8</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-6">
        <h3 class="text-lg font-medium text-ink-900 mb-4">Budget Summary</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div>
                <p class="text-sm text-smoke-500">Budget Name</p>
                <p class="text-lg font-medium text-ink-900">{{ $departmentBudget->name }}</p>
            </div>
            <div>
                <p class="text-sm text-smoke-500">Department</p>
                <p class="text-lg font-medium text-ink-900">{{ $departmentBudget->department->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-smoke-500">Fiscal Year</p>
                <p class="text-lg font-medium text-ink-900">{{ $departmentBudget->fiscal_year ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-smoke-500">Status</p>
                <p class="text-lg font-medium text-ink-900 capitalize">{{ $departmentBudget->status }}</p>
            </div>
            <div>
                <p class="text-sm text-smoke-500">Start Date</p>
                <p class="text-lg font-medium text-ink-900">{{ $departmentBudget->start_date?->format('M d, Y') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-smoke-500">End Date</p>
                <p class="text-lg font-medium text-ink-900">{{ $departmentBudget->end_date?->format('M d, Y') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-smoke-500">Total Budget</p>
                <p class="text-lg font-medium text-ink-900">${{ number_format($departmentBudget->total_budget ?? 0, 2) }}</p>
            </div>
            <div>
                <p class="text-sm text-smoke-500">Currency</p>
                <p class="text-lg font-medium text-ink-900">{{ $departmentBudget->currency ?? 'USD' }}</p>
            </div>
        </div>
    </div>
</x-workspace-layout>
