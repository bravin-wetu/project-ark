@section('title', $receipt->receipt_number . ' - Goods Receipt')

<x-workspace-layout :workspace="$project" :workspaceType="'projects'">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
            <a href="{{ route('projects.show', $project) }}" class="hover:text-ink-900 transition-colors">{{ $project->name }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('projects.purchase-orders.show', [$project, $purchaseOrder]) }}" class="hover:text-ink-900 transition-colors">{{ $purchaseOrder->po_number }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">{{ $receipt->receipt_number }}</span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-semibold text-ink-900">{{ $receipt->receipt_number }}</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @switch($receipt->status)
                            @case('draft') bg-gray-100 text-gray-800 @break
                            @case('confirmed') bg-green-100 text-green-800 @break
                            @case('partial') bg-yellow-100 text-yellow-800 @break
                            @case('complete') bg-blue-100 text-blue-800 @break
                            @case('cancelled') bg-red-100 text-red-800 @break
                            @default bg-gray-100 text-gray-800
                        @endswitch
                    ">
                        {{ ucfirst($receipt->status) }}
                    </span>
                </div>
                <p class="text-smoke-600 mt-1">Received {{ $receipt->received_at->format('M d, Y H:i') }} by {{ $receipt->received_by }}</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Receipt Details -->
            <div class="bg-white rounded-lg border border-smoke-200 p-6">
                <h2 class="text-lg font-semibold text-ink-900 mb-4">Receipt Details</h2>
                
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-smoke-600">Delivery Note</label>
                        <p class="mt-1 text-ink-900">{{ $receipt->delivery_note_number ?: '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-smoke-600">Invoice Number</label>
                        <p class="mt-1 text-ink-900">{{ $receipt->invoice_number ?: '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-smoke-600">Receiving Location</label>
                        <p class="mt-1 text-ink-900">{{ $receipt->receiving_location ?: '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-smoke-600">Overall Condition</label>
                        <p class="mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                @switch($receipt->overall_condition)
                                    @case('excellent') bg-green-100 text-green-800 @break
                                    @case('good') bg-blue-100 text-blue-800 @break
                                    @case('acceptable') bg-yellow-100 text-yellow-800 @break
                                    @case('damaged') bg-orange-100 text-orange-800 @break
                                    @case('rejected') bg-red-100 text-red-800 @break
                                    @default bg-gray-100 text-gray-800
                                @endswitch
                            ">
                                {{ ucfirst($receipt->overall_condition) }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-smoke-600">Created By</label>
                        <p class="mt-1 text-ink-900">{{ $receipt->creator->name ?? '-' }}</p>
                    </div>
                    @if($receipt->confirmed_at)
                    <div>
                        <label class="block text-sm font-medium text-smoke-600">Confirmed</label>
                        <p class="mt-1 text-ink-900">{{ $receipt->confirmed_at->format('M d, Y H:i') }}</p>
                        <p class="text-xs text-smoke-500">by {{ $receipt->confirmer->name ?? '-' }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Received Items -->
            <div class="bg-white rounded-lg border border-smoke-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-smoke-200">
                    <h2 class="text-lg font-semibold text-ink-900">Received Items</h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-smoke-200">
                        <thead class="bg-smoke-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase">Item</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-smoke-500 uppercase">Expected</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-smoke-500 uppercase">Received</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-smoke-500 uppercase">Accepted</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-smoke-500 uppercase">Rejected</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-smoke-500 uppercase">Condition</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-smoke-200">
                            @foreach($receipt->items as $item)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-ink-900">{{ $item->purchaseOrderItem->name }}</div>
                                        @if($item->storage_location || $item->batch_number)
                                            <div class="text-xs text-smoke-500">
                                                @if($item->storage_location)
                                                    Location: {{ $item->storage_location }}
                                                @endif
                                                @if($item->batch_number)
                                                    • Batch: {{ $item->batch_number }}
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm text-smoke-600">
                                        {{ number_format($item->expected_quantity, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm text-ink-900">
                                        {{ number_format($item->received_quantity, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm text-green-600 font-medium">
                                        {{ number_format($item->accepted_quantity, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm {{ $item->rejected_quantity > 0 ? 'text-red-600 font-medium' : 'text-smoke-500' }}">
                                        {{ number_format($item->rejected_quantity, 2) }}
                                        @if($item->rejection_reason)
                                            <div class="text-xs text-red-500">{{ $item->rejection_reason }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                            @switch($item->condition)
                                                @case('excellent') bg-green-100 text-green-800 @break
                                                @case('good') bg-blue-100 text-blue-800 @break
                                                @case('acceptable') bg-yellow-100 text-yellow-800 @break
                                                @case('damaged') bg-orange-100 text-orange-800 @break
                                                @case('rejected') bg-red-100 text-red-800 @break
                                                @default bg-gray-100 text-gray-800
                                            @endswitch
                                        ">
                                            {{ ucfirst($item->condition) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-smoke-50">
                            <tr>
                                <td class="px-6 py-3 text-sm font-medium text-smoke-700">Totals</td>
                                <td class="px-6 py-3 text-right text-sm font-medium text-smoke-700">{{ number_format($receipt->items->sum('expected_quantity'), 2) }}</td>
                                <td class="px-6 py-3 text-right text-sm font-medium text-ink-900">{{ number_format($receipt->items->sum('received_quantity'), 2) }}</td>
                                <td class="px-6 py-3 text-right text-sm font-medium text-green-600">{{ number_format($receipt->items->sum('accepted_quantity'), 2) }}</td>
                                <td class="px-6 py-3 text-right text-sm font-medium text-red-600">{{ number_format($receipt->items->sum('rejected_quantity'), 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Notes -->
            @if($receipt->notes || $receipt->discrepancy_notes)
                <div class="bg-white rounded-lg border border-smoke-200 p-6">
                    <h2 class="text-lg font-semibold text-ink-900 mb-4">Notes</h2>
                    
                    @if($receipt->notes)
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-smoke-700">General Notes</label>
                            <p class="mt-1 text-ink-900 whitespace-pre-wrap">{{ $receipt->notes }}</p>
                        </div>
                    @endif

                    @if($receipt->discrepancy_notes)
                        <div>
                            <label class="block text-sm font-medium text-smoke-700">Discrepancy Notes</label>
                            <p class="mt-1 text-orange-600 whitespace-pre-wrap">{{ $receipt->discrepancy_notes }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Actions -->
            <div class="bg-white rounded-lg border border-smoke-200 p-6">
                <h2 class="text-lg font-semibold text-ink-900 mb-4">Actions</h2>
                
                <div class="space-y-3">
                    @if($receipt->isDraft())
                        <form action="{{ route('projects.purchase-orders.receipts.confirm', [$project, $purchaseOrder, $receipt]) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full btn-primary bg-green-600 hover:bg-green-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Confirm Receipt
                            </button>
                        </form>
                        <p class="text-xs text-smoke-500 text-center">Confirming will update PO item quantities and cannot be undone.</p>
                        
                        <form action="{{ route('projects.purchase-orders.receipts.cancel', [$project, $purchaseOrder, $receipt]) }}" method="POST" class="mt-4">
                            @csrf
                            <button type="submit" class="w-full btn-secondary text-red-600 border-red-300 hover:bg-red-50"
                                onclick="return confirm('Are you sure you want to cancel this receipt?')">
                                Cancel Receipt
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('projects.purchase-orders.show', [$project, $purchaseOrder]) }}" class="w-full btn-secondary block text-center">
                        Back to Purchase Order
                    </a>
                </div>
            </div>

            <!-- Summary -->
            <div class="bg-white rounded-lg border border-smoke-200 p-6">
                <h2 class="text-lg font-semibold text-ink-900 mb-4">Receipt Summary</h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-smoke-600">Total Items</span>
                        <span class="font-medium text-ink-900">{{ $receipt->items->count() }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-smoke-600">Accepted Value</span>
                        <span class="font-medium text-green-600">K{{ number_format($receipt->total_received_value, 2) }}</span>
                    </div>
                    @if($receipt->items->sum('rejected_quantity') > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-smoke-600">Rejected Items</span>
                            <span class="font-medium text-red-600">{{ $receipt->items->sum('rejected_quantity') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Related PO -->
            <div class="bg-white rounded-lg border border-smoke-200 p-6">
                <h2 class="text-lg font-semibold text-ink-900 mb-4">Purchase Order</h2>
                
                <a href="{{ route('projects.purchase-orders.show', [$project, $purchaseOrder]) }}" class="flex items-center gap-3 p-3 rounded-lg border border-smoke-200 hover:bg-smoke-50">
                    <div class="w-10 h-10 rounded bg-blue-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-ink-900">{{ $purchaseOrder->po_number }}</p>
                        <p class="text-xs text-smoke-500">{{ $purchaseOrder->supplier->name }}</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-workspace-layout>
