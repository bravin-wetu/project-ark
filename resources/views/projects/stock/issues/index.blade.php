@section('title', 'Stock Issues - ' . $project->name)

<x-workspace-layout :workspace="$project" :workspaceType="'projects'">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
                <a href="{{ route('projects.stock.index', $project) }}" class="hover:text-ink-900 transition-colors">Stock Items</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-ink-900 font-medium">Stock Issues</span>
            </div>
            <h1 class="text-2xl font-semibold text-ink-900">Stock Issues</h1>
            <p class="text-smoke-600 mt-1">Manage stock issue requests and distributions</p>
        </div>
        <a href="{{ route('projects.stock.create-issue', $project) }}" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            New Issue Request
        </a>
    </div>

    <!-- Status Filter Tabs -->
    <div class="flex gap-1 mb-6 p-1 bg-smoke-100 rounded-lg w-fit">
        <a href="{{ route('projects.stock.issues', [$project, 'status' => 'all']) }}" 
           class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ request('status', 'all') == 'all' ? 'bg-white text-ink-900 shadow-sm' : 'text-smoke-600 hover:text-ink-900' }}">
            All
        </a>
        <a href="{{ route('projects.stock.issues', [$project, 'status' => 'pending_approval']) }}" 
           class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ request('status') == 'pending_approval' ? 'bg-white text-ink-900 shadow-sm' : 'text-smoke-600 hover:text-ink-900' }}">
            Pending ({{ $pendingCount ?? 0 }})
        </a>
        <a href="{{ route('projects.stock.issues', [$project, 'status' => 'approved']) }}" 
           class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ request('status') == 'approved' ? 'bg-white text-ink-900 shadow-sm' : 'text-smoke-600 hover:text-ink-900' }}">
            Approved
        </a>
        <a href="{{ route('projects.stock.issues', [$project, 'status' => 'issued']) }}" 
           class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ request('status') == 'issued' ? 'bg-white text-ink-900 shadow-sm' : 'text-smoke-600 hover:text-ink-900' }}">
            Issued
        </a>
    </div>

    <!-- Issues Table -->
    @if($issues->isNotEmpty())
    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-smoke-200">
            <thead class="bg-smoke-50 border-b border-smoke-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Issue #</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Requested By</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Purpose</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">Items</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">Date</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-smoke-100">
                @foreach($issues as $issue)
                <tr class="hover:bg-smoke-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('projects.stock.show-issue', [$project, $issue]) }}" class="font-mono text-primary-600 hover:text-primary-700">
                            {{ $issue->issue_number }}
                        </a>
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-ink-900">{{ $issue->requestedBy?->name ?? 'Unknown' }}</div>
                        <div class="text-sm text-smoke-500">{{ $issue->department ?? $issue->hub?->name ?? '' }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-ink-900 max-w-xs truncate">{{ $issue->purpose ?? '-' }}</div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center px-2 py-0.5 bg-smoke-100 text-smoke-700 rounded text-sm">
                            {{ $issue->items->count() }} items
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @switch($issue->status)
                            @case('draft')
                                <span class="inline-flex px-2 py-0.5 text-xs font-medium bg-smoke-100 text-smoke-600 rounded-full">Draft</span>
                                @break
                            @case('pending_approval')
                                <span class="inline-flex px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 rounded-full">Pending</span>
                                @break
                            @case('approved')
                                <span class="inline-flex px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 rounded-full">Approved</span>
                                @break
                            @case('issued')
                                <span class="inline-flex px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 rounded-full">Issued</span>
                                @break
                            @case('rejected')
                                <span class="inline-flex px-2 py-0.5 text-xs font-medium bg-red-100 text-red-700 rounded-full">Rejected</span>
                                @break
                            @case('cancelled')
                                <span class="inline-flex px-2 py-0.5 text-xs font-medium bg-smoke-100 text-smoke-600 rounded-full">Cancelled</span>
                                @break
                        @endswitch
                    </td>
                    <td class="px-4 py-3 text-center text-smoke-600">
                        {{ $issue->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('projects.stock.show-issue', [$project, $issue]) }}" class="text-smoke-400 hover:text-primary-600" title="View">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            @if($issue->status == 'pending_approval')
                            <form action="{{ route('projects.stock.approve-issue', [$project, $issue]) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-smoke-400 hover:text-green-600" title="Approve">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                            @if($issue->status == 'approved')
                            <form action="{{ route('projects.stock.complete-issue', [$project, $issue]) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-smoke-400 hover:text-blue-600" title="Mark Issued">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $issues->links() }}
    </div>
    @else
    <!-- Empty State -->
    <div class="card p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-smoke-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
        </svg>
        <h3 class="text-lg font-medium text-ink-900 mb-1">No stock issues yet</h3>
        <p class="text-smoke-500 mb-4">Create your first stock issue request to distribute items.</p>
        <a href="{{ route('projects.stock.create-issue', $project) }}" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            New Issue Request
        </a>
    </div>
    @endif
</x-workspace-layout>
