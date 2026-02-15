@section('title', 'RFQs - ' . $project->name)

<x-workspace-layout :workspace="$project" :workspaceType="'projects'">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
            <a href="{{ route('projects.show', $project) }}" class="hover:text-ink-900 transition-colors">{{ $project->name }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">Request for Quotations</span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ink-900">Request for Quotations</h1>
                <p class="text-smoke-600 mt-1">Send RFQs to suppliers and collect quotes for procurement.</p>
            </div>
            <a href="{{ route('projects.rfqs.create', $project) }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create RFQ
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <x-ui.stat-card 
            title="Total RFQs"
            :value="$stats['total']"
            subtitle="All time"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'
        />
        
        <x-ui.stat-card 
            title="Draft"
            :value="$stats['draft']"
            subtitle="Not yet sent"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>'
        />
        
        <x-ui.stat-card 
            title="Open"
            :value="$stats['open']"
            subtitle="Awaiting quotes"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'
        />
        
        <x-ui.stat-card 
            title="Awarded"
            :value="$stats['awarded']"
            subtitle="Vendor selected"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'
        />
    </div>

    <!-- RFQs List -->
    <div class="card animate-fade-in">
        @if($rfqs->isEmpty())
            <x-ui.empty-state
                title="No RFQs yet"
                description="Request for Quotations allow you to solicit competitive quotes from suppliers. Create your first RFQ to get started."
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'
            >
                <a href="{{ route('projects.rfqs.create', $project) }}" class="btn-primary mt-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create RFQ
                </a>
            </x-ui.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-smoke-200">
                            <th class="text-left py-3 px-4 text-sm font-medium text-smoke-600">RFQ Number</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-smoke-600">Title</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-smoke-600">Requisition</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-smoke-600">Suppliers</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-smoke-600">Quotes</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-smoke-600">Closing Date</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-smoke-600">Status</th>
                            <th class="text-right py-3 px-4 text-sm font-medium text-smoke-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-smoke-100">
                        @foreach($rfqs as $rfq)
                            <tr class="hover:bg-smoke-50 transition-colors">
                                <td class="py-3 px-4">
                                    <a href="{{ route('projects.rfqs.show', [$project, $rfq]) }}" class="font-mono text-sm text-ink-900 hover:text-ink-600">
                                        {{ $rfq->rfq_number }}
                                    </a>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="text-ink-900">{{ Str::limit($rfq->title, 40) }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-sm text-smoke-600 font-mono">{{ $rfq->requisition->requisition_number }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-sm text-ink-900">{{ $rfq->suppliers_count }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-sm {{ $rfq->quotes_count >= $rfq->min_quotes ? 'text-green-600' : 'text-smoke-600' }}">
                                        {{ $rfq->quotes_count }}/{{ $rfq->min_quotes }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    @if($rfq->closing_date)
                                        <span class="text-sm {{ $rfq->closing_date->isPast() ? 'text-red-600' : 'text-smoke-600' }}">
                                            {{ $rfq->closing_date->format('M d, Y') }}
                                        </span>
                                    @else
                                        <span class="text-sm text-smoke-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="badge {{ $rfq->getStatusBadgeClass() }}">
                                        {{ $rfq->status_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('projects.rfqs.show', [$project, $rfq]) }}" 
                                           class="p-1.5 text-smoke-400 hover:text-ink-900 hover:bg-smoke-100 rounded-lg transition-colors"
                                           title="View">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        @if($rfq->canEvaluate())
                                            <a href="{{ route('projects.rfqs.analyze', [$project, $rfq]) }}" 
                                               class="p-1.5 text-smoke-400 hover:text-ink-900 hover:bg-smoke-100 rounded-lg transition-colors"
                                               title="Analyze Quotes">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                                </svg>
                                            </a>
                                        @endif
                                        @if($rfq->canEdit())
                                            <a href="{{ route('projects.rfqs.edit', [$project, $rfq]) }}" 
                                               class="p-1.5 text-smoke-400 hover:text-ink-900 hover:bg-smoke-100 rounded-lg transition-colors"
                                               title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($rfqs->hasPages())
                <div class="px-4 py-3 border-t border-smoke-200">
                    {{ $rfqs->links() }}
                </div>
            @endif
        @endif
    </div>
</x-workspace-layout>
