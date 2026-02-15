@section('title', 'Requisitions - ' . $project->name)

<x-workspace-layout :workspace="$project" :workspaceType="'projects'">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
            <a href="{{ route('projects.show', $project) }}" class="hover:text-ink-900 transition-colors">{{ $project->name }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">Requisitions</span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ink-900">Requisitions</h1>
                <p class="text-smoke-600 mt-1">Create and manage procurement requisitions for this project.</p>
            </div>
            <a href="{{ route('projects.requisitions.create', $project) }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Requisition
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <x-ui.stat-card 
            title="Total Requisitions"
            :value="$stats['total']"
            subtitle="All time"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'
        />
        
        <x-ui.stat-card 
            title="Pending Approval"
            :value="$stats['pending']"
            subtitle="Awaiting review"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'
        />
        
        <x-ui.stat-card 
            title="Approved"
            :value="$stats['approved']"
            subtitle="Ready for RFQ"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'
        />
        
        <x-ui.stat-card 
            title="Total Value"
            :value="'$' . number_format($stats['total_value'], 2)"
            subtitle="Estimated cost"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
        />
    </div>

    <!-- Requisitions List -->
    <div class="card animate-fade-in">
        @if($requisitions->isEmpty())
            <x-ui.empty-state
                title="No requisitions yet"
                description="Requisitions allow you to request procurement of items against your budget lines."
                actionHref="{{ route('projects.requisitions.create', $project) }}"
                actionText="Create Requisition"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'
            />
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-smoke-50">
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Requisition</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Budget Line</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Requested By</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-smoke-500 uppercase tracking-wider">Est. Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-smoke-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-smoke-100">
                        @foreach($requisitions as $requisition)
                            <tr class="hover:bg-smoke-50 transition-colors">
                                <td class="px-6 py-4">
                                    <a href="{{ route('projects.requisitions.show', [$project, $requisition]) }}" class="group">
                                        <div class="text-sm font-medium text-ink-900 group-hover:text-ink-600">{{ $requisition->title }}</div>
                                        <div class="text-xs text-smoke-500 font-mono">{{ $requisition->requisition_number }}</div>
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-smoke-600">{{ $requisition->budgetLine->name ?? '—' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-smoke-600">{{ $requisition->requester->name ?? '—' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="text-sm font-medium text-ink-900">${{ number_format($requisition->estimated_total, 2) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $priorityColors = [
                                            'low' => 'bg-smoke-100 text-smoke-700',
                                            'normal' => 'bg-blue-100 text-blue-700',
                                            'high' => 'bg-amber-100 text-amber-700',
                                            'urgent' => 'bg-red-100 text-red-700',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $priorityColors[$requisition->priority] ?? 'bg-smoke-100 text-smoke-700' }}">
                                        {{ ucfirst($requisition->priority) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'draft' => 'bg-smoke-100 text-smoke-700',
                                            'pending_approval' => 'bg-amber-100 text-amber-700',
                                            'approved' => 'bg-emerald-100 text-emerald-700',
                                            'rejected' => 'bg-red-100 text-red-700',
                                            'cancelled' => 'bg-smoke-100 text-smoke-700',
                                            'in_progress' => 'bg-blue-100 text-blue-700',
                                            'completed' => 'bg-emerald-100 text-emerald-700',
                                        ];
                                        $statusLabels = [
                                            'draft' => 'Draft',
                                            'pending_approval' => 'Pending',
                                            'approved' => 'Approved',
                                            'rejected' => 'Rejected',
                                            'cancelled' => 'Cancelled',
                                            'in_progress' => 'In Progress',
                                            'completed' => 'Completed',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$requisition->status] ?? 'bg-smoke-100 text-smoke-700' }}">
                                        {{ $statusLabels[$requisition->status] ?? ucfirst($requisition->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-smoke-500">{{ $requisition->requested_at->format('M d, Y') }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <a href="{{ route('projects.requisitions.show', [$project, $requisition]) }}" 
                                       class="text-sm font-medium text-ink-600 hover:text-ink-900">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($requisitions->hasPages())
                <div class="px-6 py-4 border-t border-smoke-100">
                    {{ $requisitions->links() }}
                </div>
            @endif
        @endif
    </div>
</x-workspace-layout>
