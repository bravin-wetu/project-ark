@section('title', 'Asset Report - ' . $project->name)

<x-workspace-layout :workspace="$project" :workspaceType="'projects'">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
            <a href="{{ route('projects.reports.index', $project) }}" class="hover:text-ink-900 transition-colors">Reports</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">Asset Report</span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ink-900">Asset Report</h1>
                <p class="text-smoke-600 mt-1">Asset register, depreciation, and condition tracking.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('projects.reports.index', $project) }}?export=excel" class="btn-secondary text-sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export
                </a>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Total Assets</div>
            <div class="text-3xl font-semibold text-ink-900">{{ $totalAssets }}</div>
        </div>
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Total Value</div>
            <div class="text-2xl font-semibold text-ink-900">KES {{ number_format($totalValue, 0) }}</div>
        </div>
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Current Book Value</div>
            <div class="text-2xl font-semibold text-ink-900">KES {{ number_format($depreciation['current_book_value'] ?? 0, 0) }}</div>
        </div>
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Depreciation YTD</div>
            <div class="text-2xl font-semibold text-amber-600">KES {{ number_format($depreciation['yearly_depreciation'] ?? 0, 0) }}</div>
        </div>
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Attention Needed</div>
            <div class="text-3xl font-semibold {{ $attentionNeeded->count() > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $attentionNeeded->count() }}</div>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6 mb-8">
        <!-- Assets by Status -->
        <div class="card">
            <div class="px-6 py-4 border-b border-smoke-200">
                <h2 class="font-medium text-ink-900">By Status</h2>
            </div>
            <div class="p-6">
                @if($byStatus->isNotEmpty())
                <div class="space-y-3">
                    @php
                        $statusColors = [
                            'active' => 'bg-green-500',
                            'in_use' => 'bg-blue-500',
                            'maintenance' => 'bg-amber-500',
                            'disposed' => 'bg-smoke-400',
                            'lost' => 'bg-red-500',
                        ];
                    @endphp
                    @foreach($byStatus as $item)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full {{ $statusColors[$item['status']] ?? 'bg-smoke-400' }}"></div>
                            <span class="text-sm text-ink-900 capitalize">{{ str_replace('_', ' ', $item['status']) }}</span>
                        </div>
                        <span class="font-medium text-ink-900">{{ $item['count'] }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-smoke-500 text-center py-4">No asset data</p>
                @endif
            </div>
        </div>

        <!-- Assets by Category -->
        <div class="card">
            <div class="px-6 py-4 border-b border-smoke-200">
                <h2 class="font-medium text-ink-900">By Category</h2>
            </div>
            <div class="p-6">
                @if($byCategory->isNotEmpty())
                <div class="space-y-3">
                    @foreach($byCategory as $item)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-ink-900">{{ $item['category'] ?? 'Uncategorized' }}</span>
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-smoke-500">{{ $item['count'] }} items</span>
                            <span class="font-medium text-ink-900">KES {{ number_format($item['value'], 0) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-smoke-500 text-center py-4">No category data</p>
                @endif
            </div>
        </div>

        <!-- Assets by Hub -->
        <div class="card">
            <div class="px-6 py-4 border-b border-smoke-200">
                <h2 class="font-medium text-ink-900">By Location/Hub</h2>
            </div>
            <div class="p-6">
                @if($byHub->isNotEmpty())
                <div class="space-y-3">
                    @foreach($byHub as $item)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-ink-900">{{ $item['hub'] ?? 'Unassigned' }}</span>
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-smoke-500">{{ $item['count'] }} items</span>
                            <span class="font-medium text-ink-900">KES {{ number_format($item['value'], 0) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-smoke-500 text-center py-4">No hub data</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Depreciation Summary -->
    <div class="card mb-8">
        <div class="px-6 py-4 border-b border-smoke-200">
            <h2 class="font-medium text-ink-900">Depreciation Summary</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-4 gap-6">
                <div>
                    <div class="text-sm text-smoke-500 mb-1">Original Cost</div>
                    <div class="text-xl font-semibold text-ink-900">KES {{ number_format($depreciation['original_cost'] ?? 0, 0) }}</div>
                </div>
                <div>
                    <div class="text-sm text-smoke-500 mb-1">Accumulated Depreciation</div>
                    <div class="text-xl font-semibold text-amber-600">KES {{ number_format($depreciation['accumulated_depreciation'] ?? 0, 0) }}</div>
                </div>
                <div>
                    <div class="text-sm text-smoke-500 mb-1">Current Book Value</div>
                    <div class="text-xl font-semibold text-ink-900">KES {{ number_format($depreciation['current_book_value'] ?? 0, 0) }}</div>
                </div>
                <div>
                    <div class="text-sm text-smoke-500 mb-1">Depreciation This Year</div>
                    <div class="text-xl font-semibold text-red-600">KES {{ number_format($depreciation['yearly_depreciation'] ?? 0, 0) }}</div>
                </div>
            </div>
            @if(isset($depreciation['original_cost']) && $depreciation['original_cost'] > 0)
            <div class="mt-6">
                <div class="text-sm text-smoke-500 mb-2">Value Retention</div>
                @php
                    $retentionRate = ($depreciation['current_book_value'] / $depreciation['original_cost']) * 100;
                @endphp
                <div class="h-4 bg-smoke-100 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-green-500 to-amber-500" style="width: {{ min($retentionRate, 100) }}%"></div>
                </div>
                <div class="flex justify-between text-xs text-smoke-500 mt-1">
                    <span>{{ number_format($retentionRate, 1) }}% retained</span>
                    <span>{{ number_format(100 - $retentionRate, 1) }}% depreciated</span>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Assets Needing Attention -->
    @if($attentionNeeded->isNotEmpty())
    <div class="card mb-8 border-l-4 border-red-500">
        <div class="px-6 py-4 border-b border-smoke-200">
            <h2 class="font-medium text-red-600">Assets Needing Attention</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-smoke-200">
                <thead class="bg-red-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase">Asset</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase">Category</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase">Location</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase">Issue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-smoke-100">
                    @foreach($attentionNeeded as $asset)
                    <tr class="hover:bg-red-50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-ink-900">{{ $asset->name }}</div>
                            <div class="text-xs text-smoke-500">{{ $asset->asset_tag }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-smoke-600">{{ $asset->category ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-smoke-600">{{ $asset->hub?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex px-2 py-0.5 text-xs rounded 
                                {{ $asset->status === 'maintenance' ? 'bg-amber-100 text-amber-700' : 
                                   ($asset->status === 'lost' ? 'bg-red-100 text-red-700' : 'bg-smoke-100 text-smoke-700') }}">
                                {{ ucfirst($asset->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-red-600">
                            @if($asset->status === 'maintenance')
                                Requires maintenance
                            @elseif($asset->status === 'lost')
                                Reported lost
                            @elseif($asset->condition === 'poor')
                                Poor condition
                            @else
                                Needs review
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Quick Links -->
    <div class="flex items-center gap-4">
        <a href="{{ route('projects.assets.index', $project) }}" class="btn-secondary text-sm">
            View All Assets
        </a>
        <a href="{{ route('projects.hubs.index', $project) }}" class="btn-secondary text-sm">
            Manage Hubs
        </a>
    </div>
</x-workspace-layout>
