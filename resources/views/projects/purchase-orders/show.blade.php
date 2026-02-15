@section('title', $purchaseOrder->po_number . ' - ' . $project->name)

<x-workspace-layout :workspace="$project" :workspaceType="'projects'">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
            <a href="{{ route('projects.show', $project) }}" class="hover:text-ink-900 transition-colors">{{ $project->name }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('projects.purchase-orders.index', $project) }}" class="hover:text-ink-900 transition-colors">Purchase Orders</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">{{ $purchaseOrder->po_number }}</span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-semibold text-ink-900">{{ $purchaseOrder->po_number }}</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @switch($purchaseOrder->status)
                            @case('draft') bg-gray-100 text-gray-800 @break
                            @case('pending_approval') bg-yellow-100 text-yellow-800 @break
                            @case('approved') bg-green-100 text-green-800 @break
                            @case('rejected') bg-red-100 text-red-800 @break
                            @case('sent') bg-blue-100 text-blue-800 @break
                            @case('acknowledged') bg-indigo-100 text-indigo-800 @break
                            @case('partially_received') bg-purple-100 text-purple-800 @break
                            @case('received') bg-teal-100 text-teal-800 @break
                            @case('closed') bg-gray-100 text-gray-800 @break
                            @case('cancelled') bg-red-100 text-red-800 @break
                            @default bg-gray-100 text-gray-800
                        @endswitch
                    ">
                        {{ str_replace('_', ' ', ucfirst($purchaseOrder->status)) }}
                    </span>
                </div>
                <p class="text-smoke-600 mt-1">Created {{ $purchaseOrder->created_at->format('M d, Y') }} by {{ $purchaseOrder->creator->name ?? 'System' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('projects.purchase-orders.print', [$project, $purchaseOrder]) }}" target="_blank" class="btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print
                </a>
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
            <!-- Supplier Info -->
            <div class="bg-white rounded-lg border border-smoke-200 p-6">
                <h2 class="text-lg font-semibold text-ink-900 mb-4">Supplier Information</h2>
                
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-ink-100 flex items-center justify-center flex-shrink-0">
                        <span class="text-ink-600 font-semibold">{{ substr($purchaseOrder->supplier->name ?? 'S', 0, 2) }}</span>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-medium text-ink-900">{{ $purchaseOrder->supplier->name }}</h3>
                        <p class="text-sm text-smoke-600">{{ $purchaseOrder->supplier->code }}</p>
                        @if($purchaseOrder->supplier->email)
                            <p class="text-sm text-smoke-500 mt-1">{{ $purchaseOrder->supplier->email }}</p>
                        @endif
                        @if($purchaseOrder->supplier->phone)
                            <p class="text-sm text-smoke-500">{{ $purchaseOrder->supplier->phone }}</p>
                        @endif
                    </div>
                </div>

                @if($purchaseOrder->supplier_reference)
                    <div class="mt-4 pt-4 border-t border-smoke-200">
                        <p class="text-sm text-smoke-600">Supplier Reference: <span class="font-medium text-ink-900">{{ $purchaseOrder->supplier_reference }}</span></p>
                    </div>
                @endif
            </div>

            <!-- Order Items -->
            <div class="bg-white rounded-lg border border-smoke-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-smoke-200">
                    <h2 class="text-lg font-semibold text-ink-900">Order Items</h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-smoke-200">
                        <thead class="bg-smoke-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase">Item</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-smoke-500 uppercase">Qty</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-smoke-500 uppercase">Received</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-smoke-500 uppercase">Unit Price</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-smoke-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-smoke-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-smoke-200">
                            @foreach($purchaseOrder->items as $item)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-ink-900">{{ $item->name }}</div>
                                        @if($item->description)
                                            <div class="text-xs text-smoke-500">{{ Str::limit($item->description, 60) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm text-ink-900">
                                        {{ number_format($item->quantity, 2) }} {{ $item->unit }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="text-sm text-ink-900">{{ number_format($item->received_quantity, 2) }}</div>
                                        <div class="text-xs text-smoke-500">{{ $item->receipt_percentage }}%</div>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm text-ink-900">
                                        K{{ number_format($item->unit_price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium text-ink-900">
                                        K{{ number_format($item->total_price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                            @switch($item->status)
                                                @case('pending') bg-gray-100 text-gray-800 @break
                                                @case('partially_received') bg-yellow-100 text-yellow-800 @break
                                                @case('received') bg-green-100 text-green-800 @break
                                                @case('cancelled') bg-red-100 text-red-800 @break
                                                @default bg-gray-100 text-gray-800
                                            @endswitch
                                        ">
                                            {{ str_replace('_', ' ', ucfirst($item->status)) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-smoke-50">
                            <tr>
                                <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-smoke-700">Subtotal</td>
                                <td class="px-6 py-3 text-right text-sm font-medium text-ink-900">K{{ number_format($purchaseOrder->subtotal_amount, 2) }}</td>
                                <td></td>
                            </tr>
                            @if($purchaseOrder->shipping_amount > 0)
                            <tr>
                                <td colspan="4" class="px-6 py-3 text-right text-sm text-smoke-700">Shipping</td>
                                <td class="px-6 py-3 text-right text-sm text-ink-900">K{{ number_format($purchaseOrder->shipping_amount, 2) }}</td>
                                <td></td>
                            </tr>
                            @endif
                            @if($purchaseOrder->discount_amount > 0)
                            <tr>
                                <td colspan="4" class="px-6 py-3 text-right text-sm text-smoke-700">Discount</td>
                                <td class="px-6 py-3 text-right text-sm text-ink-900">-K{{ number_format($purchaseOrder->discount_amount, 2) }}</td>
                                <td></td>
                            </tr>
                            @endif
                            @if($purchaseOrder->tax_amount > 0)
                            <tr>
                                <td colspan="4" class="px-6 py-3 text-right text-sm text-smoke-700">Tax</td>
                                <td class="px-6 py-3 text-right text-sm text-ink-900">K{{ number_format($purchaseOrder->tax_amount, 2) }}</td>
                                <td></td>
                            </tr>
                            @endif
                            <tr class="border-t-2 border-smoke-300">
                                <td colspan="4" class="px-6 py-3 text-right text-sm font-bold text-ink-900">Total</td>
                                <td class="px-6 py-3 text-right text-lg font-bold text-ink-900">K{{ number_format($purchaseOrder->total_amount, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Delivery Information -->
            <div class="bg-white rounded-lg border border-smoke-200 p-6">
                <h2 class="text-lg font-semibold text-ink-900 mb-4">Delivery Information</h2>
                
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-smoke-700">Delivery Address</label>
                        <p class="mt-1 text-ink-900">{{ $purchaseOrder->delivery_address ?: 'Not specified' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-smoke-700">Expected Delivery Date</label>
                        <p class="mt-1 text-ink-900">
                            @if($purchaseOrder->expected_delivery_date)
                                {{ $purchaseOrder->expected_delivery_date->format('M d, Y') }}
                                @if($purchaseOrder->expected_delivery_date->isPast() && !$purchaseOrder->isReceived())
                                    <span class="text-red-500 ml-2">(Overdue)</span>
                                @endif
                            @else
                                Not specified
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-smoke-700">Shipping Method</label>
                        <p class="mt-1 text-ink-900">{{ ucfirst($purchaseOrder->shipping_method ?? 'Not specified') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-smoke-700">Payment Terms</label>
                        <p class="mt-1 text-ink-900">{{ str_replace('_', ' ', ucfirst($purchaseOrder->payment_terms ?? 'Not specified')) }}</p>
                    </div>
                </div>

                @if($purchaseOrder->actual_delivery_date)
                    <div class="mt-4 pt-4 border-t border-smoke-200">
                        <label class="block text-sm font-medium text-smoke-700">Actual Delivery Date</label>
                        <p class="mt-1 text-ink-900">{{ $purchaseOrder->actual_delivery_date->format('M d, Y') }}</p>
                    </div>
                @endif
            </div>

            <!-- Goods Receipts -->
            <div class="bg-white rounded-lg border border-smoke-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-smoke-200 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-ink-900">Goods Receipts</h2>
                    @if($purchaseOrder->canReceiveGoods())
                        <a href="{{ route('projects.purchase-orders.receipts.create', [$project, $purchaseOrder]) }}" class="btn-primary text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Record Receipt
                        </a>
                    @endif
                </div>

                @if($purchaseOrder->receipts->isEmpty())
                    <div class="px-6 py-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-smoke-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-ink-900">No receipts recorded</h3>
                        <p class="mt-1 text-sm text-smoke-500">Goods receipts will appear here once items are received.</p>
                    </div>
                @else
                    <div class="divide-y divide-smoke-200">
                        @foreach($purchaseOrder->receipts as $receipt)
                            <div class="px-6 py-4 hover:bg-smoke-50">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <a href="{{ route('projects.purchase-orders.receipts.show', [$project, $purchaseOrder, $receipt]) }}" class="font-medium text-ink-600 hover:text-ink-900">
                                            {{ $receipt->receipt_number }}
                                        </a>
                                        <p class="text-sm text-smoke-500">Received {{ $receipt->received_at->format('M d, Y') }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
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
                                        <p class="text-sm text-smoke-600 mt-1">{{ $receipt->items->count() }} items</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Notes -->
            @if($purchaseOrder->notes || $purchaseOrder->terms_conditions)
                <div class="bg-white rounded-lg border border-smoke-200 p-6">
                    <h2 class="text-lg font-semibold text-ink-900 mb-4">Notes & Terms</h2>
                    
                    @if($purchaseOrder->notes)
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-smoke-700">Internal Notes</label>
                            <p class="mt-1 text-ink-900 whitespace-pre-wrap">{{ $purchaseOrder->notes }}</p>
                        </div>
                    @endif

                    @if($purchaseOrder->terms_conditions)
                        <div>
                            <label class="block text-sm font-medium text-smoke-700">Terms & Conditions</label>
                            <p class="mt-1 text-ink-900 whitespace-pre-wrap">{{ $purchaseOrder->terms_conditions }}</p>
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
                    @if($purchaseOrder->isDraft())
                        <form action="{{ route('projects.purchase-orders.submit', [$project, $purchaseOrder]) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full btn-primary">
                                Submit for Approval
                            </button>
                        </form>
                    @endif

                    @if($purchaseOrder->isPendingApproval())
                        <button type="button" onclick="document.getElementById('approveModal').classList.remove('hidden')" class="w-full btn-primary bg-green-600 hover:bg-green-700">
                            Approve
                        </button>
                        <button type="button" onclick="document.getElementById('rejectModal').classList.remove('hidden')" class="w-full btn-secondary text-red-600 border-red-300 hover:bg-red-50">
                            Reject
                        </button>
                    @endif

                    @if($purchaseOrder->isApproved())
                        <form action="{{ route('projects.purchase-orders.send', [$project, $purchaseOrder]) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full btn-primary">
                                Mark as Sent to Supplier
                            </button>
                        </form>
                    @endif

                    @if($purchaseOrder->isSent())
                        <button type="button" onclick="document.getElementById('acknowledgeModal').classList.remove('hidden')" class="w-full btn-secondary">
                            Record Supplier Acknowledgment
                        </button>
                    @endif

                    @if($purchaseOrder->canReceiveGoods())
                        <a href="{{ route('projects.purchase-orders.receipts.create', [$project, $purchaseOrder]) }}" class="w-full btn-secondary block text-center">
                            Record Goods Receipt
                        </a>
                    @endif

                    @if($purchaseOrder->isReceived())
                        <form action="{{ route('projects.purchase-orders.close', [$project, $purchaseOrder]) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full btn-secondary">
                                Close PO
                            </button>
                        </form>
                    @endif

                    @if(!$purchaseOrder->isClosed() && !$purchaseOrder->isCancelled())
                        <button type="button" onclick="document.getElementById('cancelModal').classList.remove('hidden')" class="w-full btn-secondary text-red-600 border-red-300 hover:bg-red-50">
                            Cancel PO
                        </button>
                    @endif
                </div>
            </div>

            <!-- Receipt Progress -->
            <div class="bg-white rounded-lg border border-smoke-200 p-6">
                <h2 class="text-lg font-semibold text-ink-900 mb-4">Receipt Progress</h2>
                
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-smoke-600">Items Received</span>
                            <span class="font-medium text-ink-900">{{ $receiptStats['receipt_percentage'] }}%</span>
                        </div>
                        <div class="w-full bg-smoke-200 rounded-full h-2.5">
                            <div class="bg-green-500 h-2.5 rounded-full" style="width: {{ $receiptStats['receipt_percentage'] }}%"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-smoke-600">Ordered</span>
                            <p class="font-medium text-ink-900">{{ number_format($receiptStats['items_ordered'], 2) }}</p>
                        </div>
                        <div>
                            <span class="text-smoke-600">Received</span>
                            <p class="font-medium text-ink-900">{{ number_format($receiptStats['items_received'], 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Documents -->
            <div class="bg-white rounded-lg border border-smoke-200 p-6">
                <h2 class="text-lg font-semibold text-ink-900 mb-4">Related Documents</h2>
                
                <div class="space-y-3">
                    @if($purchaseOrder->rfq)
                        <a href="{{ route('projects.rfqs.show', [$project, $purchaseOrder->rfq]) }}" class="flex items-center gap-3 p-3 rounded-lg border border-smoke-200 hover:bg-smoke-50">
                            <div class="w-8 h-8 rounded bg-blue-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-ink-900">{{ $purchaseOrder->rfq->rfq_number }}</p>
                                <p class="text-xs text-smoke-500">RFQ</p>
                            </div>
                        </a>
                    @endif

                    @if($purchaseOrder->quote)
                        <div class="flex items-center gap-3 p-3 rounded-lg border border-smoke-200">
                            <div class="w-8 h-8 rounded bg-green-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-ink-900">{{ $purchaseOrder->quote->quote_number }}</p>
                                <p class="text-xs text-smoke-500">Quote</p>
                            </div>
                        </div>
                    @endif

                    @if($purchaseOrder->requisition)
                        <a href="{{ route('projects.requisitions.show', [$project, $purchaseOrder->requisition]) }}" class="flex items-center gap-3 p-3 rounded-lg border border-smoke-200 hover:bg-smoke-50">
                            <div class="w-8 h-8 rounded bg-purple-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-ink-900">{{ $purchaseOrder->requisition->requisition_number }}</p>
                                <p class="text-xs text-smoke-500">Requisition</p>
                            </div>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Budget Info -->
            @if($purchaseOrder->budgetLine)
                <div class="bg-white rounded-lg border border-smoke-200 p-6">
                    <h2 class="text-lg font-semibold text-ink-900 mb-4">Budget Information</h2>
                    
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm text-smoke-600">Budget Line</span>
                            <p class="font-medium text-ink-900">{{ $purchaseOrder->budgetLine->name }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-smoke-600">Committed Amount</span>
                            <p class="font-medium text-ink-900">K{{ number_format($purchaseOrder->total_amount, 2) }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Approval History -->
            @if($purchaseOrder->approved_at || $purchaseOrder->rejected_at)
                <div class="bg-white rounded-lg border border-smoke-200 p-6">
                    <h2 class="text-lg font-semibold text-ink-900 mb-4">Approval History</h2>
                    
                    <div class="space-y-4">
                        @if($purchaseOrder->approved_at)
                            <div class="flex gap-3">
                                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-ink-900">Approved</p>
                                    <p class="text-xs text-smoke-500">{{ $purchaseOrder->approved_at->format('M d, Y H:i') }} by {{ $purchaseOrder->approver->name ?? 'System' }}</p>
                                    @if($purchaseOrder->approval_notes)
                                        <p class="text-sm text-smoke-600 mt-1">{{ $purchaseOrder->approval_notes }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($purchaseOrder->rejected_at)
                            <div class="flex gap-3">
                                <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-ink-900">Rejected</p>
                                    <p class="text-xs text-smoke-500">{{ $purchaseOrder->rejected_at->format('M d, Y H:i') }}</p>
                                    @if($purchaseOrder->rejection_reason)
                                        <p class="text-sm text-red-600 mt-1">{{ $purchaseOrder->rejection_reason }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Approve Modal -->
    <div id="approveModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-ink-900 mb-4">Approve Purchase Order</h3>
            <form action="{{ route('projects.purchase-orders.approve', [$project, $purchaseOrder]) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="approval_notes" class="block text-sm font-medium text-smoke-700">Approval Notes (Optional)</label>
                    <textarea name="approval_notes" id="approval_notes" rows="3"
                        class="mt-1 block w-full rounded-lg border-smoke-300 shadow-sm focus:border-ink-500 focus:ring-ink-500 sm:text-sm"
                        placeholder="Add any notes about this approval"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 btn-primary bg-green-600 hover:bg-green-700">Approve</button>
                    <button type="button" onclick="document.getElementById('approveModal').classList.add('hidden')" class="flex-1 btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-ink-900 mb-4">Reject Purchase Order</h3>
            <form action="{{ route('projects.purchase-orders.reject', [$project, $purchaseOrder]) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="rejection_reason" class="block text-sm font-medium text-smoke-700">Reason for Rejection <span class="text-red-500">*</span></label>
                    <textarea name="rejection_reason" id="rejection_reason" rows="3" required
                        class="mt-1 block w-full rounded-lg border-smoke-300 shadow-sm focus:border-ink-500 focus:ring-ink-500 sm:text-sm"
                        placeholder="Explain why this PO is being rejected"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 btn-primary bg-red-600 hover:bg-red-700">Reject</button>
                    <button type="button" onclick="document.getElementById('rejectModal').classList.add('hidden')" class="flex-1 btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Acknowledge Modal -->
    <div id="acknowledgeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-ink-900 mb-4">Record Supplier Acknowledgment</h3>
            <form action="{{ route('projects.purchase-orders.acknowledge', [$project, $purchaseOrder]) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="supplier_reference" class="block text-sm font-medium text-smoke-700">Supplier Reference Number</label>
                    <input type="text" name="supplier_reference" id="supplier_reference"
                        class="mt-1 block w-full rounded-lg border-smoke-300 shadow-sm focus:border-ink-500 focus:ring-ink-500 sm:text-sm"
                        placeholder="Supplier's order confirmation number">
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 btn-primary">Record Acknowledgment</button>
                    <button type="button" onclick="document.getElementById('acknowledgeModal').classList.add('hidden')" class="flex-1 btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div id="cancelModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-ink-900 mb-4">Cancel Purchase Order</h3>
            <form action="{{ route('projects.purchase-orders.cancel', [$project, $purchaseOrder]) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="cancellation_reason" class="block text-sm font-medium text-smoke-700">Reason for Cancellation <span class="text-red-500">*</span></label>
                    <textarea name="cancellation_reason" id="cancellation_reason" rows="3" required
                        class="mt-1 block w-full rounded-lg border-smoke-300 shadow-sm focus:border-ink-500 focus:ring-ink-500 sm:text-sm"
                        placeholder="Explain why this PO is being cancelled"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 btn-primary bg-red-600 hover:bg-red-700">Cancel PO</button>
                    <button type="button" onclick="document.getElementById('cancelModal').classList.add('hidden')" class="flex-1 btn-secondary">Keep PO</button>
                </div>
            </form>
        </div>
    </div>
</x-workspace-layout>
