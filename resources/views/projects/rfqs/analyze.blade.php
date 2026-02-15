@section('title', 'Analyze Quotes - ' . $rfq->rfq_number)

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
            <a href="{{ route('projects.rfqs.show', [$project, $rfq]) }}" class="hover:text-ink-900 transition-colors">{{ $rfq->rfq_number }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">Analyze Quotes</span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ink-900">Quote Analysis</h1>
                <p class="text-smoke-600 mt-1">{{ $rfq->title }}</p>
            </div>
            <a href="{{ route('projects.rfqs.show', [$project, $rfq]) }}" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to RFQ
            </a>
        </div>
    </div>

    @if($rfq->quotes->isEmpty())
        <div class="card p-6">
            <x-ui.empty-state
                title="No Quotes to Analyze"
                description="No quotes have been received for this RFQ yet."
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'
            />
        </div>
    @else
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <x-ui.stat-card 
                title="Quotes Received"
                :value="$rfq->quotes->count()"
                :subtitle="'Min required: ' . $rfq->min_quotes"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'
            />
            
            @php
                $lowestQuote = $rfq->quotes->sortBy('total_amount')->first();
                $highestQuote = $rfq->quotes->sortByDesc('total_amount')->first();
                $avgAmount = $rfq->quotes->avg('total_amount');
            @endphp
            
            <x-ui.stat-card 
                title="Lowest Quote"
                :value="'$' . number_format($lowestQuote->total_amount, 2)"
                :subtitle="$lowestQuote->supplier->name"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>'
            />
            
            <x-ui.stat-card 
                title="Highest Quote"
                :value="'$' . number_format($highestQuote->total_amount, 2)"
                :subtitle="$highestQuote->supplier->name"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>'
            />
            
            <x-ui.stat-card 
                title="Budget Estimate"
                :value="'$' . number_format($rfq->requisition->estimated_total, 2)"
                :subtitle="'Avg quote: $' . number_format($avgAmount, 2)"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
            />
        </div>

        <!-- Quote Comparison Table -->
        <div class="card p-6 mb-6">
            <h3 class="text-lg font-medium text-ink-900 mb-4">Quote Comparison</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-smoke-200">
                            <th class="text-left py-3 px-4 text-sm font-medium text-smoke-600 min-w-[200px]">Supplier</th>
                            <th class="text-right py-3 px-4 text-sm font-medium text-smoke-600">Total Amount</th>
                            <th class="text-center py-3 px-4 text-sm font-medium text-smoke-600">vs Estimate</th>
                            <th class="text-center py-3 px-4 text-sm font-medium text-smoke-600">Delivery</th>
                            <th class="text-center py-3 px-4 text-sm font-medium text-smoke-600">Valid Until</th>
                            <th class="text-center py-3 px-4 text-sm font-medium text-smoke-600">Score</th>
                            <th class="text-center py-3 px-4 text-sm font-medium text-smoke-600">Status</th>
                            <th class="text-right py-3 px-4 text-sm font-medium text-smoke-600">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-smoke-100">
                        @foreach($rfq->quotes->sortBy('total_amount') as $quote)
                            @php
                                $variance = $rfq->requisition->estimated_total > 0 
                                    ? (($quote->total_amount - $rfq->requisition->estimated_total) / $rfq->requisition->estimated_total) * 100 
                                    : 0;
                                $isLowest = $quote->id === $lowestQuote->id;
                            @endphp
                            <tr class="hover:bg-smoke-50 {{ $isLowest ? 'bg-green-50' : '' }}">
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        @if($isLowest)
                                            <span class="w-6 h-6 bg-green-500 text-white rounded-full flex items-center justify-center text-xs font-bold">1</span>
                                        @else
                                            <span class="w-6 h-6 bg-smoke-200 text-smoke-600 rounded-full flex items-center justify-center text-xs font-bold">{{ $loop->iteration }}</span>
                                        @endif
                                        <div>
                                            <div class="font-medium text-ink-900">{{ $quote->supplier->name }}</div>
                                            <div class="text-xs text-smoke-500 font-mono">{{ $quote->quote_number }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <span class="text-lg font-semibold {{ $isLowest ? 'text-green-600' : 'text-ink-900' }}">
                                        ${{ number_format($quote->total_amount, 2) }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span class="{{ $variance <= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                                        {{ $variance >= 0 ? '+' : '' }}{{ number_format($variance, 1) }}%
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-center text-smoke-600">
                                    {{ $quote->delivery_days ? $quote->delivery_days . ' days' : '—' }}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    @if($quote->valid_until)
                                        <span class="{{ $quote->valid_until->isPast() ? 'text-red-600' : 'text-smoke-600' }}">
                                            {{ $quote->valid_until->format('M d') }}
                                        </span>
                                    @else
                                        <span class="text-smoke-400">—</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-center">
                                    @if($quote->evaluation_score)
                                        <span class="font-medium text-ink-900">{{ number_format($quote->evaluation_score, 1) }}</span>
                                    @else
                                        <span class="text-smoke-400">—</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span class="badge {{ $quote->getStatusBadgeClass() }}">{{ $quote->status_label }}</span>
                                </td>
                                <td class="py-4 px-4 text-right">
                                    @if($rfq->canAward() && !$quote->isSelected())
                                        <button type="button" 
                                                onclick="openAwardModal({{ $quote->id }}, '{{ $quote->supplier->name }}', {{ $quote->total_amount }})"
                                                class="btn-primary text-xs py-1.5 px-3">
                                            Award
                                        </button>
                                    @elseif($quote->isSelected())
                                        <div class="flex items-center justify-end gap-2">
                                            <span class="text-green-600 font-medium text-sm">
                                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                Awarded
                                            </span>
                                            @if(!\App\Models\PurchaseOrder::where('quote_id', $quote->id)->exists())
                                                <a href="{{ route('projects.purchase-orders.create-from-quote', [$project, $quote]) }}" 
                                                   class="btn-secondary text-xs py-1.5 px-3">
                                                    Create PO
                                                </a>
                                            @else
                                                <span class="text-smoke-500 text-xs">PO Created</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Item-by-Item Comparison -->
        <div class="card p-6 mb-6">
            <h3 class="text-lg font-medium text-ink-900 mb-4">Item-by-Item Comparison</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-smoke-200">
                            <th class="text-left py-3 px-4 text-sm font-medium text-smoke-600 min-w-[200px]">Item</th>
                            <th class="text-right py-3 px-4 text-sm font-medium text-smoke-600">Qty</th>
                            <th class="text-right py-3 px-4 text-sm font-medium text-smoke-600">Estimate</th>
                            @foreach($rfq->quotes->sortBy('total_amount') as $quote)
                                <th class="text-right py-3 px-4 text-sm font-medium text-smoke-600 min-w-[120px]">
                                    {{ Str::limit($quote->supplier->name, 15) }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-smoke-100">
                        @foreach($rfq->requisition->items as $reqItem)
                            <tr class="hover:bg-smoke-50">
                                <td class="py-3 px-4">
                                    <div class="text-ink-900">{{ $reqItem->name }}</div>
                                </td>
                                <td class="py-3 px-4 text-right text-smoke-600">{{ $reqItem->quantity }} {{ $reqItem->unit }}</td>
                                <td class="py-3 px-4 text-right text-smoke-600">${{ number_format($reqItem->estimated_unit_price, 2) }}</td>
                                @foreach($rfq->quotes->sortBy('total_amount') as $quote)
                                    @php
                                        $quoteItem = $quote->items->where('requisition_item_id', $reqItem->id)->first()
                                            ?? $quote->items->where('name', $reqItem->name)->first();
                                        $unitPrice = $quoteItem?->unit_price ?? 0;
                                        $isLowest = $rfq->quotes->every(function($q) use ($reqItem, $unitPrice) {
                                            $item = $q->items->where('requisition_item_id', $reqItem->id)->first()
                                                ?? $q->items->where('name', $reqItem->name)->first();
                                            return ($item?->unit_price ?? PHP_INT_MAX) >= $unitPrice;
                                        });
                                    @endphp
                                    <td class="py-3 px-4 text-right {{ $isLowest && $unitPrice > 0 ? 'text-green-600 font-medium' : 'text-ink-900' }}">
                                        @if($quoteItem)
                                            ${{ number_format($unitPrice, 2) }}
                                        @else
                                            <span class="text-smoke-400">—</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-smoke-300">
                        <tr class="font-semibold">
                            <td class="py-3 px-4 text-ink-900" colspan="2">Total</td>
                            <td class="py-3 px-4 text-right text-ink-900">${{ number_format($rfq->requisition->estimated_total, 2) }}</td>
                            @foreach($rfq->quotes->sortBy('total_amount') as $quote)
                                <td class="py-3 px-4 text-right {{ $quote->id === $lowestQuote->id ? 'text-green-600' : 'text-ink-900' }}">
                                    ${{ number_format($quote->total_amount, 2) }}
                                </td>
                            @endforeach
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Evaluation Criteria -->
        @if($rfq->evaluation_criteria)
            <div class="card p-6">
                <h3 class="text-lg font-medium text-ink-900 mb-4">Evaluation Criteria</h3>
                <div class="prose prose-sm max-w-none text-ink-700 whitespace-pre-line">{{ $rfq->evaluation_criteria }}</div>
            </div>
        @endif

        <!-- Award Modal -->
        <div id="awardModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-ink-900/50 transition-opacity" onclick="closeAwardModal()"></div>
                <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-medium text-ink-900 mb-4">Award RFQ</h3>
                    <form action="" method="POST" id="awardForm">
                        @csrf
                        <input type="hidden" name="quote_id" id="awardQuoteId">
                        
                        <div class="mb-4">
                            <p class="text-smoke-600">
                                Award this RFQ to <strong id="awardSupplierName" class="text-ink-900"></strong> 
                                for <strong id="awardAmount" class="text-ink-900"></strong>?
                            </p>
                        </div>

                        <div class="mb-6">
                            <label for="justification" class="block text-sm font-medium text-ink-700 mb-1">
                                Justification (optional)
                            </label>
                            <textarea name="justification" 
                                      id="justification" 
                                      rows="3"
                                      class="input-field"
                                      placeholder="Reason for selecting this supplier..."></textarea>
                        </div>

                        <div class="flex gap-3 justify-end">
                            <button type="button" onclick="closeAwardModal()" class="btn-ghost">
                                Cancel
                            </button>
                            <button type="submit" class="btn-primary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Confirm Award
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @push('scripts')
        <script>
            function openAwardModal(quoteId, supplierName, amount) {
                document.getElementById('awardQuoteId').value = quoteId;
                document.getElementById('awardSupplierName').textContent = supplierName;
                document.getElementById('awardAmount').textContent = '$' + parseFloat(amount).toFixed(2);
                document.getElementById('awardForm').action = '{{ route("projects.rfqs.award", [$project, $rfq]) }}';
                document.getElementById('awardModal').classList.remove('hidden');
            }

            function closeAwardModal() {
                document.getElementById('awardModal').classList.add('hidden');
            }

            // Close modal on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeAwardModal();
                }
            });
        </script>
        @endpush
    @endif
</x-workspace-layout>
