@section('title', 'Assets & Stock - ' . $departmentBudget->name)

<x-workspace-layout :workspace="$departmentBudget" :workspaceType="'department-budgets'">
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
            <a href="{{ route('department-budgets.show', $departmentBudget) }}" class="hover:text-ink-900 transition-colors">{{ $departmentBudget->name }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">Assets & Stock</span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ink-900">Assets & Stock</h1>
                <p class="text-smoke-600 mt-1">Track fixed assets and inventory stock acquired for this department.</p>
            </div>
        </div>
    </div>

    <div class="border-b border-smoke-200 mb-6">
        <nav class="flex gap-8">
            <button class="py-3 px-1 text-sm font-medium text-ink-900 border-b-2 border-ink-900">Assets</button>
            <button class="py-3 px-1 text-sm font-medium text-smoke-500 hover:text-smoke-700 border-b-2 border-transparent">Stock / Inventory</button>
        </nav>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <x-ui.stat-card title="Total Assets" value="0" subtitle="Fixed assets" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>'/>
        <x-ui.stat-card title="Asset Value" value="$0.00" subtitle="Total book value" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'/>
        <x-ui.stat-card title="Stock Items" value="0" subtitle="Inventory SKUs" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>'/>
        <x-ui.stat-card title="Low Stock" value="0" subtitle="Below reorder level" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>'/>
    </div>

    <div class="card animate-fade-in">
        <x-ui.empty-state
            title="No assets registered"
            description="Assets and inventory are created automatically when goods are received. This feature will be available in Sprint 7."
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>'
        />
    </div>
</x-workspace-layout>
