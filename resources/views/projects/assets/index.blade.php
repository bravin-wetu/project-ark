@section('title', 'Assets - ' . $project->name)

<x-workspace-layout :workspace="$project" :workspaceType="'projects'">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
            <a href="{{ route('projects.show', $project) }}" class="hover:text-ink-900 transition-colors">{{ $project->name }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">Assets</span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ink-900">Asset Register</h1>
                <p class="text-smoke-600 mt-1">Track fixed assets acquired for this project.</p>
            </div>
            <a href="{{ route('projects.assets.create', $project) }}" class="btn-primary">
                + Register Asset
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <x-ui.stat-card 
            title="Total Assets"
            :value="$stats['total']"
            subtitle="Registered assets"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>'
        />
        
        <x-ui.stat-card 
            title="Active"
            :value="$stats['active']"
            subtitle="In use"
            variant="success"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'
        />
        
        <x-ui.stat-card 
            title="In Maintenance"
            :value="$stats['in_maintenance']"
            subtitle="Under service"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'
        />
        
        <x-ui.stat-card 
            title="Disposed"
            :value="$stats['disposed']"
            subtitle="No longer in use"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>'
        />
    </div>

    <!-- Assets Table -->
    <div class="card animate-fade-in">
        @if($assets->isEmpty())
            <x-ui.empty-state
                title="No assets registered"
                description="Assets can be registered from confirmed goods receipts or added manually."
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>'
            >
                <a href="{{ route('projects.assets.create', $project) }}" class="btn-primary mt-4">
                    Register First Asset
                </a>
            </x-ui.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-smoke-50 border-b border-smoke-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Asset Tag</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Hub</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Assigned To</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Condition</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-smoke-500 uppercase tracking-wider">Value</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-smoke-100">
                        @foreach($assets as $asset)
                        <tr class="hover:bg-smoke-50 transition-colors">
                            <td class="px-4 py-3">
                                <a href="{{ route('projects.assets.show', [$project, $asset]) }}" class="font-mono text-sm text-ink-900 hover:text-ink-700">
                                    {{ $asset->asset_tag }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <div>
                                    <p class="text-ink-900 font-medium">{{ $asset->name }}</p>
                                    @if($asset->serial_number)
                                        <p class="text-xs text-smoke-500">S/N: {{ $asset->serial_number }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-smoke-600">{{ $asset->category }}</td>
                            <td class="px-4 py-3 text-smoke-600">{{ $asset->hub?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-smoke-600">{{ $asset->assignedUser?->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $asset->getConditionBadgeClass() }}">
                                    {{ ucfirst(str_replace('_', ' ', $asset->condition)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $asset->getStatusBadgeClass() }}">
                                    {{ ucfirst(str_replace('_', ' ', $asset->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-sm text-ink-900">
                                {{ number_format($asset->current_value ?? $asset->acquisition_cost, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('projects.assets.show', [$project, $asset]) }}" class="text-sm text-smoke-600 hover:text-ink-900">
                                    View →
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($assets->hasPages())
                <div class="px-4 py-3 border-t border-smoke-200">
                    {{ $assets->links() }}
                </div>
            @endif
        @endif
    </div>
</x-workspace-layout>
