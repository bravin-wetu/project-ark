@section('title', $rfq->rfq_number . ' - ' . $project->name)

<x-workspace-layout :workspace="$project" :workspaceType="'projects'">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
            <a href="{{ route('projects.show', $project) }}" class="hover:text-ink-900 transition-colors">{{ $project->name }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('projects.rfqs.index', $project) }}" class="hover:text-ink-900 transition-colors">RFQs</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">{{ $rfq->rfq_number }}</span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-semibold text-ink-900">{{ $rfq->title }}</h1>
                    <span class="badge {{ $rfq->getStatusBadgeClass() }}">{{ $rfq->status_label }}</span>
                </div>
                <p class="text-smoke-600 mt-1 font-mono">{{ $rfq->rfq_number }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if($rfq->canEdit())
                    <a href="{{ route('projects.rfqs.edit', [$project, $rfq]) }}" class="btn-secondary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </a>
                @endif
                @if($rfq->canSend())
                    <form action="{{ route('projects.rfqs.send', [$project, $rfq]) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn-primary" onclick="return confirm('Send this RFQ to all invited suppliers?')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Send to Suppliers
                        </button>
                    </form>
                @endif
                @if($rfq->canEvaluate())
                    <a href="{{ route('projects.rfqs.analyze', [$project, $rfq]) }}" class="btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Analyze Quotes
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Source Requisition -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-ink-900 mb-4">Source Requisition</h3>
                <div class="p-4 bg-smoke-50 rounded-xl">
                    <div class="flex items-start justify-between">
                        <div>
                            <a href="{{ route('projects.requisitions.show', [$project, $rfq->requisition]) }}" 
                               class="font-mono text-sm text-ink-600 hover:text-ink-900">
                                {{ $rfq->requisition->requisition_number }}
                            </a>
                            <h4 class="text-ink-900 font-medium mt-1">{{ $rfq->requisition->title }}</h4>
                        </div>
                        <span class="text-lg font-semibold text-ink-900">${{ number_format($rfq->requisition->estimated_total, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Requisition Items -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-ink-900 mb-4">Items Required</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-smoke-200">
                                <th class="text-left py-2 px-3 text-sm font-medium text-smoke-600">Item</th>
                                <th class="text-right py-2 px-3 text-sm font-medium text-smoke-600">Qty</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-smoke-600">Unit</th>
                                <th class="text-right py-2 px-3 text-sm font-medium text-smoke-600">Est. Price</th>
                                <th class="text-right py-2 px-3 text-sm font-medium text-smoke-600">Est. Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-smoke-100">
                            @foreach($rfq->requisition->items as $item)
                                <tr>
                                    <td class="py-3 px-3">
                                        <div class="text-ink-900">{{ $item->name }}</div>
                                        @if($item->specifications)
                                            <div class="text-xs text-smoke-500 mt-1">{{ Str::limit($item->specifications, 60) }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-right text-ink-900">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="py-3 px-3 text-smoke-600">{{ $item->unit }}</td>
                                    <td class="py-3 px-3 text-right text-smoke-600">${{ number_format($item->estimated_unit_price, 2) }}</td>
                                    <td class="py-3 px-3 text-right font-medium text-ink-900">${{ number_format($item->estimated_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t border-smoke-200">
                            <tr>
                                <td colspan="4" class="py-3 px-3 text-right font-medium text-ink-900">Total Estimate:</td>
                                <td class="py-3 px-3 text-right font-semibold text-ink-900">${{ number_format($rfq->requisition->estimated_total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Invited Suppliers -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-ink-900 mb-4">Invited Suppliers ({{ $rfq->suppliers->count() }})</h3>
                
                @if($rfq->suppliers->isEmpty())
                    <p class="text-smoke-500 text-center py-4">No suppliers invited yet.</p>
                @else
                    <div class="space-y-3">
                        @foreach($rfq->suppliers as $supplier)
                            <div class="flex items-center justify-between p-4 bg-smoke-50 rounded-xl">
                                <div>
                                    <div class="font-medium text-ink-900">{{ $supplier->name }}</div>
                                    <div class="text-sm text-smoke-600">{{ $supplier->email }}</div>
                                </div>
                                <div class="flex items-center gap-3">
                                    @php
                                        $pivotStatus = $supplier->pivot->status;
                                        $hasQuote = $rfq->quotes->where('supplier_id', $supplier->id)->isNotEmpty();
                                    @endphp
                                    @if($hasQuote)
                                        <span class="badge badge-success">Quoted</span>
                                    @elseif($pivotStatus === 'declined')
                                        <span class="badge badge-danger">Declined</span>
                                    @elseif($supplier->pivot->invited_at)
                                        <span class="badge badge-info">Invited</span>
                                    @else
                                        <span class="badge badge-secondary">Pending</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Quotes Received -->
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-ink-900">Quotes Received ({{ $rfq->quotes->count() }}/{{ $rfq->min_quotes }})</h3>
                    @if($rfq->canReceiveQuotes())
                        <button type="button" onclick="openAddQuoteModal()" class="btn-secondary text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Quote
                        </button>
                    @endif
                </div>
                
                @if($rfq->quotes->isEmpty())
                    <div class="text-center py-8">
                        <p class="text-smoke-500">No quotes received yet.</p>
                        @if(!$rfq->isSent() && !$rfq->isDraft())
                            <p class="text-xs text-smoke-400 mt-1">Send the RFQ to start receiving quotes.</p>
                        @endif
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($rfq->quotes as $quote)
                            <div class="p-4 rounded-xl border {{ $quote->isSelected() ? 'border-green-500 bg-green-50' : 'border-smoke-200 bg-smoke-50' }}">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <span class="font-mono text-sm text-smoke-600">{{ $quote->quote_number }}</span>
                                        <div class="font-medium text-ink-900 mt-1">{{ $quote->supplier->name }}</div>
                                        @if($quote->supplier_reference)
                                            <div class="text-xs text-smoke-500">Ref: {{ $quote->supplier_reference }}</div>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xl font-semibold text-ink-900">${{ number_format($quote->total_amount, 2) }}</span>
                                        <span class="block badge {{ $quote->getStatusBadgeClass() }} mt-1">{{ $quote->status_label }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 mt-3 text-sm text-smoke-600">
                                    @if($quote->valid_until)
                                        <span>Valid until: {{ $quote->valid_until->format('M d, Y') }}</span>
                                    @endif
                                    @if($quote->delivery_days)
                                        <span>Delivery: {{ $quote->delivery_days }} days</span>
                                    @endif
                                    @if($quote->evaluation_score)
                                        <span>Score: {{ number_format($quote->evaluation_score, 1) }}/100</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Award Information (if awarded) -->
            @if($rfq->isAwarded() && $rfq->awardedQuote)
                <div class="card p-6 border-2 border-green-500">
                    <h3 class="text-lg font-medium text-green-700 mb-4">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Awarded
                    </h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm text-smoke-600">Supplier</span>
                            <p class="font-medium text-ink-900">{{ $rfq->awardedSupplier->name }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-smoke-600">Amount</span>
                            <p class="font-medium text-ink-900">${{ number_format($rfq->awardedQuote->total_amount, 2) }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-smoke-600">Awarded On</span>
                            <p class="font-medium text-ink-900">{{ $rfq->awarded_at->format('M d, Y') }}</p>
                        </div>
                        @if($rfq->award_justification)
                            <div class="col-span-2">
                                <span class="text-sm text-smoke-600">Justification</span>
                                <p class="text-ink-900">{{ $rfq->award_justification }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- RFQ Details -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-ink-900 mb-4">Details</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm text-smoke-600">Status</dt>
                        <dd class="mt-1">
                            <span class="badge {{ $rfq->getStatusBadgeClass() }}">{{ $rfq->status_label }}</span>
                        </dd>
                    </div>
                    @if($rfq->issue_date)
                        <div>
                            <dt class="text-sm text-smoke-600">Issue Date</dt>
                            <dd class="font-medium text-ink-900">{{ $rfq->issue_date->format('M d, Y') }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-sm text-smoke-600">Closing Date</dt>
                        <dd class="font-medium {{ $rfq->closing_date?->isPast() ? 'text-red-600' : 'text-ink-900' }}">
                            {{ $rfq->closing_date?->format('M d, Y') ?? 'Not set' }}
                        </dd>
                    </div>
                    @if($rfq->delivery_date)
                        <div>
                            <dt class="text-sm text-smoke-600">Expected Delivery</dt>
                            <dd class="font-medium text-ink-900">{{ $rfq->delivery_date->format('M d, Y') }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-sm text-smoke-600">Min Quotes Required</dt>
                        <dd class="font-medium text-ink-900">{{ $rfq->min_quotes }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-smoke-600">Created By</dt>
                        <dd class="font-medium text-ink-900">{{ $rfq->creator?->name ?? 'Unknown' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-smoke-600">Created On</dt>
                        <dd class="font-medium text-ink-900">{{ $rfq->created_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Terms & Conditions -->
            @if($rfq->terms_and_conditions || $rfq->submission_instructions || $rfq->evaluation_criteria)
                <div class="card p-6">
                    <h3 class="text-lg font-medium text-ink-900 mb-4">Terms & Instructions</h3>
                    <div class="space-y-4">
                        @if($rfq->submission_instructions)
                            <div>
                                <span class="text-sm font-medium text-smoke-600">Submission Instructions</span>
                                <p class="text-sm text-ink-900 mt-1 whitespace-pre-line">{{ $rfq->submission_instructions }}</p>
                            </div>
                        @endif
                        @if($rfq->terms_and_conditions)
                            <div>
                                <span class="text-sm font-medium text-smoke-600">Terms & Conditions</span>
                                <p class="text-sm text-ink-900 mt-1 whitespace-pre-line">{{ $rfq->terms_and_conditions }}</p>
                            </div>
                        @endif
                        @if($rfq->evaluation_criteria)
                            <div>
                                <span class="text-sm font-medium text-smoke-600">Evaluation Criteria</span>
                                <p class="text-sm text-ink-900 mt-1 whitespace-pre-line">{{ $rfq->evaluation_criteria }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Actions -->
            @if(!$rfq->isAwarded() && !$rfq->isCancelled())
                <div class="card p-6">
                    <h3 class="text-lg font-medium text-ink-900 mb-4">Actions</h3>
                    <div class="space-y-3">
                        @if($rfq->canSend())
                            <form action="{{ route('projects.rfqs.send', [$project, $rfq]) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-primary w-full justify-center" onclick="return confirm('Send this RFQ to suppliers?')">
                                    Send to Suppliers
                                </button>
                            </form>
                        @endif
                        @if($rfq->canEvaluate())
                            <a href="{{ route('projects.rfqs.analyze', [$project, $rfq]) }}" class="btn-secondary w-full justify-center">
                                Analyze Quotes
                            </a>
                        @endif
                        <form action="{{ route('projects.rfqs.cancel', [$project, $rfq]) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-ghost w-full justify-center text-red-600 hover:bg-red-50" 
                                    onclick="return confirm('Are you sure you want to cancel this RFQ?')">
                                Cancel RFQ
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-workspace-layout>
