@section('title', 'Record Goods Receipt - ' . $purchaseOrder->po_number)

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
            <a href="{{ route('projects.purchase-orders.show', [$project, $purchaseOrder]) }}" class="hover:text-ink-900 transition-colors">{{ $purchaseOrder->po_number }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">Record Receipt</span>
        </div>
        <div>
            <h1 class="text-2xl font-semibold text-ink-900">Record Goods Receipt</h1>
            <p class="text-smoke-600 mt-1">Record delivery for {{ $purchaseOrder->po_number }} from {{ $purchaseOrder->supplier->name }}</p>
        </div>
    </div>

    <form action="{{ route('projects.purchase-orders.receipts.store', [$project, $purchaseOrder]) }}" method="POST" id="receiptForm">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Receipt Details -->
                <div class="bg-white rounded-lg border border-smoke-200 p-6">
                    <h2 class="text-lg font-semibold text-ink-900 mb-4">Receipt Details</h2>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="received_by" class="block text-sm font-medium text-smoke-700">Received By <span class="text-red-500">*</span></label>
                            <input type="text" name="received_by" id="received_by" required
                                value="{{ old('received_by', auth()->user()->name ?? '') }}"
                                class="mt-1 block w-full rounded-lg border-smoke-300 shadow-sm focus:border-ink-500 focus:ring-ink-500 sm:text-sm"
                                placeholder="Name of person receiving goods">
                            @error('received_by')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="received_at" class="block text-sm font-medium text-smoke-700">Date Received <span class="text-red-500">*</span></label>
                            <input type="datetime-local" name="received_at" id="received_at" required
                                value="{{ old('received_at', now()->format('Y-m-d\TH:i')) }}"
                                class="mt-1 block w-full rounded-lg border-smoke-300 shadow-sm focus:border-ink-500 focus:ring-ink-500 sm:text-sm">
                            @error('received_at')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="delivery_note_number" class="block text-sm font-medium text-smoke-700">Delivery Note Number</label>
                            <input type="text" name="delivery_note_number" id="delivery_note_number"
                                value="{{ old('delivery_note_number') }}"
                                class="mt-1 block w-full rounded-lg border-smoke-300 shadow-sm focus:border-ink-500 focus:ring-ink-500 sm:text-sm"
                                placeholder="Supplier's delivery note #">
                        </div>
                        <div>
                            <label for="invoice_number" class="block text-sm font-medium text-smoke-700">Invoice Number</label>
                            <input type="text" name="invoice_number" id="invoice_number"
                                value="{{ old('invoice_number') }}"
                                class="mt-1 block w-full rounded-lg border-smoke-300 shadow-sm focus:border-ink-500 focus:ring-ink-500 sm:text-sm"
                                placeholder="Supplier's invoice #">
                        </div>
                        <div>
                            <label for="receiving_location" class="block text-sm font-medium text-smoke-700">Receiving Location</label>
                            <input type="text" name="receiving_location" id="receiving_location"
                                value="{{ old('receiving_location') }}"
                                class="mt-1 block w-full rounded-lg border-smoke-300 shadow-sm focus:border-ink-500 focus:ring-ink-500 sm:text-sm"
                                placeholder="Warehouse, dock, etc.">
                        </div>
                        <div>
                            <label for="overall_condition" class="block text-sm font-medium text-smoke-700">Overall Condition <span class="text-red-500">*</span></label>
                            <select name="overall_condition" id="overall_condition" required
                                class="mt-1 block w-full rounded-lg border-smoke-300 shadow-sm focus:border-ink-500 focus:ring-ink-500 sm:text-sm">
                                <option value="excellent" {{ old('overall_condition') == 'excellent' ? 'selected' : '' }}>Excellent</option>
                                <option value="good" {{ old('overall_condition', 'good') == 'good' ? 'selected' : '' }}>Good</option>
                                <option value="acceptable" {{ old('overall_condition') == 'acceptable' ? 'selected' : '' }}>Acceptable</option>
                                <option value="damaged" {{ old('overall_condition') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                                <option value="rejected" {{ old('overall_condition') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Received Items -->
                <div class="bg-white rounded-lg border border-smoke-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-smoke-200">
                        <h2 class="text-lg font-semibold text-ink-900">Received Items</h2>
                        <p class="text-sm text-smoke-500 mt-1">Record quantities received for each item. Only items with remaining quantities are shown.</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-smoke-200">
                            <thead class="bg-smoke-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase">Item</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-smoke-500 uppercase">Expected</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-smoke-500 uppercase">Received</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-smoke-500 uppercase">Accepted</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-smoke-500 uppercase">Rejected</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase">Condition</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-smoke-200">
                                @foreach($receivableItems as $index => $item)
                                    <tr class="item-row">
                                        <input type="hidden" name="items[{{ $index }}][purchase_order_item_id]" value="{{ $item->id }}">
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-medium text-ink-900">{{ $item->name }}</div>
                                            <div class="text-xs text-smoke-500">{{ $item->unit }} • K{{ number_format($item->unit_price, 2) }}/unit</div>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="text-sm text-ink-900">{{ number_format($item->remaining_quantity, 2) }}</div>
                                            <div class="text-xs text-smoke-500">of {{ number_format($item->quantity, 2) }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" name="items[{{ $index }}][received_quantity]"
                                                value="{{ $item->remaining_quantity }}"
                                                min="0" max="{{ $item->remaining_quantity }}" step="0.01"
                                                class="w-24 text-right rounded-lg border-smoke-300 shadow-sm focus:border-ink-500 focus:ring-ink-500 text-sm received-qty"
                                                onchange="updateAccepted(this)">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" name="items[{{ $index }}][accepted_quantity]"
                                                value="{{ $item->remaining_quantity }}"
                                                min="0" step="0.01"
                                                class="w-24 text-right rounded-lg border-smoke-300 shadow-sm focus:border-ink-500 focus:ring-ink-500 text-sm accepted-qty"
                                                onchange="updateRejected(this)">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" name="items[{{ $index }}][rejected_quantity]"
                                                value="0"
                                                min="0" step="0.01"
                                                class="w-24 text-right rounded-lg border-smoke-300 shadow-sm focus:border-ink-500 focus:ring-ink-500 text-sm rejected-qty"
                                                readonly>
                                        </td>
                                        <td class="px-4 py-3">
                                            <select name="items[{{ $index }}][condition]"
                                                class="rounded-lg border-smoke-300 shadow-sm focus:border-ink-500 focus:ring-ink-500 text-sm">
                                                <option value="excellent">Excellent</option>
                                                <option value="good" selected>Good</option>
                                                <option value="acceptable">Acceptable</option>
                                                <option value="damaged">Damaged</option>
                                                <option value="rejected">Rejected</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr class="bg-smoke-50">
                                        <td colspan="6" class="px-4 py-2">
                                            <div class="flex gap-4">
                                                <div class="flex-1">
                                                    <input type="text" name="items[{{ $index }}][storage_location]"
                                                        placeholder="Storage location"
                                                        class="w-full text-sm rounded border-smoke-300 focus:border-ink-500 focus:ring-ink-500">
                                                </div>
                                                <div class="flex-1">
                                                    <input type="text" name="items[{{ $index }}][batch_number]"
                                                        placeholder="Batch number"
                                                        class="w-full text-sm rounded border-smoke-300 focus:border-ink-500 focus:ring-ink-500">
                                                </div>
                                                <div class="flex-1">
                                                    <input type="text" name="items[{{ $index }}][rejection_reason]"
                                                        placeholder="Rejection reason (if any)"
                                                        class="w-full text-sm rounded border-smoke-300 focus:border-ink-500 focus:ring-ink-500">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Notes -->
                <div class="bg-white rounded-lg border border-smoke-200 p-6">
                    <h2 class="text-lg font-semibold text-ink-900 mb-4">Notes</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="notes" class="block text-sm font-medium text-smoke-700">General Notes</label>
                            <textarea name="notes" id="notes" rows="2"
                                class="mt-1 block w-full rounded-lg border-smoke-300 shadow-sm focus:border-ink-500 focus:ring-ink-500 sm:text-sm"
                                placeholder="Any general observations about this delivery">{{ old('notes') }}</textarea>
                        </div>

                        <div>
                            <label for="discrepancy_notes" class="block text-sm font-medium text-smoke-700">Discrepancy Notes</label>
                            <textarea name="discrepancy_notes" id="discrepancy_notes" rows="2"
                                class="mt-1 block w-full rounded-lg border-smoke-300 shadow-sm focus:border-ink-500 focus:ring-ink-500 sm:text-sm"
                                placeholder="Document any discrepancies between ordered and received">{{ old('discrepancy_notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- PO Summary -->
                <div class="bg-white rounded-lg border border-smoke-200 p-6">
                    <h2 class="text-lg font-semibold text-ink-900 mb-4">Purchase Order</h2>
                    
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm text-smoke-600">PO Number</span>
                            <p class="font-medium text-ink-900">{{ $purchaseOrder->po_number }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-smoke-600">Supplier</span>
                            <p class="font-medium text-ink-900">{{ $purchaseOrder->supplier->name }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-smoke-600">Total Value</span>
                            <p class="font-medium text-ink-900">K{{ number_format($purchaseOrder->total_amount, 2) }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-smoke-600">Items to Receive</span>
                            <p class="font-medium text-ink-900">{{ $receivableItems->count() }} items</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-lg border border-smoke-200 p-6">
                    <div class="space-y-3">
                        <button type="submit" class="w-full btn-primary">
                            Save as Draft
                        </button>
                        <a href="{{ route('projects.purchase-orders.show', [$project, $purchaseOrder]) }}" class="w-full btn-secondary block text-center">
                            Cancel
                        </a>
                    </div>
                    <p class="mt-3 text-xs text-smoke-500 text-center">
                        Receipt will be saved as draft. You'll need to confirm it to update inventory quantities.
                    </p>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        function updateAccepted(input) {
            const row = input.closest('tr');
            const received = parseFloat(input.value) || 0;
            const acceptedInput = row.querySelector('.accepted-qty');
            const rejectedInput = row.querySelector('.rejected-qty');
            
            // Set accepted to received by default
            acceptedInput.value = received;
            acceptedInput.max = received;
            rejectedInput.value = 0;
        }

        function updateRejected(input) {
            const row = input.closest('tr');
            const receivedInput = row.querySelector('.received-qty');
            const rejectedInput = row.querySelector('.rejected-qty');
            
            const received = parseFloat(receivedInput.value) || 0;
            const accepted = parseFloat(input.value) || 0;
            
            rejectedInput.value = Math.max(0, received - accepted);
        }
    </script>
    @endpush
</x-workspace-layout>
