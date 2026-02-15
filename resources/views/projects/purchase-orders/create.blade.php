@section('title', 'Create Purchase Order - ' . $project->name)

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
            <span class="text-ink-900 font-medium">Create from Quote</span>
        </div>
        <div>
            <h1 class="text-2xl font-semibold text-ink-900">Create Purchase Order</h1>
            <p class="text-smoke-600 mt-1">Create a purchase order from awarded quote {{ $quote->quote_number }}</p>
        </div>
    </div>

    <form action="{{ route('projects.purchase-orders.store', $project) }}" method="POST" id="poForm">
        @csrf
        <input type="hidden" name="quote_id" value="{{ $quote->id }}">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Quote & Supplier Info -->
                <div class="bg-white rounded-lg border border-smoke-200 p-6">
                    <h2 class="text-lg font-semibold text-ink-900 mb-4">Quote & Supplier Information</h2>
                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-smoke-700">Quote Number</label>
                            <p class="mt-1 text-ink-900 font-medium">{{ $quote->quote_number }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-smoke-700">RFQ Reference</label>
                            <p class="mt-1 text-ink-900">{{ $quote->rfq->rfq_number ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="border-t border-smoke-200 pt-4">
                        <label class="block text-sm font-medium text-smoke-700 mb-2">Supplier</label>
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full bg-ink-100 flex items-center justify-center">
                                <span class="text-ink-600 font-semibold text-sm">{{ substr($quote->supplier->name ?? 'S', 0, 2) }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-ink-900">{{ $quote->supplier->name }}</p>
                                <p class="text-sm text-smoke-600">{{ $quote->supplier->code }}</p>
                                @if($quote->supplier->email)
                                    <p class="text-sm text-smoke-500">{{ $quote->supplier->email }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Details -->
                <div class="bg-white rounded-lg border border-smoke-200 p-6">
                    <h2 class="text-lg font-semibold text-ink-900 mb-4">Delivery Details</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="delivery_address" class="block text-sm font-medium text-smoke-700">Delivery Address <span class="text-red-500">*</span></label>
                            <textarea name="delivery_address" id="delivery_address" rows="3" required
                                class="mt-1 block w-full rounded-lg border-smoke-300 shadow-sm focus:border-ink-500 focus:ring-ink-500 sm:text-sm"
                                placeholder="Enter full delivery address">{{ old('delivery_address', $project->address ?? '') }}</textarea>
                            @error('delivery_address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="expected_delivery_date" class="block text-sm font-medium text-smoke-700">Expected Delivery Date <span class="text-red-500">*</span></label>
                                <input type="date" name="expected_delivery_date" id="expected_delivery_date" required
                                    value="{{ old('expected_delivery_date', $quote->delivery_date?->format('Y-m-d')) }}"
                                    min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                    class="mt-1 block w-full rounded-lg border-smoke-300 shadow-sm focus:border-ink-500 focus:ring-ink-500 sm:text-sm">
                                @error('expected_delivery_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="shipping_method" class="block text-sm font-medium text-smoke-700">Shipping Method</label>
                                <select name="shipping_method" id="shipping_method"
                                    class="mt-1 block w-full rounded-lg border-smoke-300 shadow-sm focus:border-ink-500 focus:ring-ink-500 sm:text-sm">
                                    <option value="">Select method</option>
                                    <option value="standard" {{ old('shipping_method') == 'standard' ? 'selected' : '' }}>Standard Delivery</option>
                                    <option value="express" {{ old('shipping_method') == 'express' ? 'selected' : '' }}>Express Delivery</option>
                                    <option value="pickup" {{ old('shipping_method') == 'pickup' ? 'selected' : '' }}>Supplier Pickup</option>
                                    <option value="freight" {{ old('shipping_method') == 'freight' ? 'selected' : '' }}>Freight/Cargo</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="payment_terms" class="block text-sm font-medium text-smoke-700">Payment Terms</label>
                            <select name="payment_terms" id="payment_terms"
                                class="mt-1 block w-full rounded-lg border-smoke-300 shadow-sm focus:border-ink-500 focus:ring-ink-500 sm:text-sm">
                                <option value="">Select payment terms</option>
                                <option value="net_30" {{ old('payment_terms', $quote->payment_terms) == 'net_30' ? 'selected' : '' }}>Net 30 Days</option>
                                <option value="net_60" {{ old('payment_terms', $quote->payment_terms) == 'net_60' ? 'selected' : '' }}>Net 60 Days</option>
                                <option value="net_90" {{ old('payment_terms', $quote->payment_terms) == 'net_90' ? 'selected' : '' }}>Net 90 Days</option>
                                <option value="cod" {{ old('payment_terms', $quote->payment_terms) == 'cod' ? 'selected' : '' }}>Cash on Delivery</option>
                                <option value="prepaid" {{ old('payment_terms', $quote->payment_terms) == 'prepaid' ? 'selected' : '' }}>Prepaid</option>
                                <option value="50_50" {{ old('payment_terms', $quote->payment_terms) == '50_50' ? 'selected' : '' }}>50% Advance, 50% on Delivery</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="bg-white rounded-lg border border-smoke-200 p-6">
                    <h2 class="text-lg font-semibold text-ink-900 mb-4">Order Items</h2>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-smoke-200">
                            <thead class="bg-smoke-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase">Include</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase">Item</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-smoke-500 uppercase">Quoted Qty</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-smoke-500 uppercase">Order Qty</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-smoke-500 uppercase">Unit Price</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-smoke-500 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-smoke-200">
                                @foreach($quote->items as $index => $item)
                                    <tr class="item-row" data-unit-price="{{ $item->unit_price }}">
                                        <td class="px-4 py-3">
                                            <input type="checkbox" name="items[{{ $index }}][include]" value="1" checked
                                                class="item-checkbox rounded border-smoke-300 text-ink-600 focus:ring-ink-500"
                                                onchange="toggleItemRow(this)">
                                            <input type="hidden" name="items[{{ $index }}][quote_item_id]" value="{{ $item->id }}">
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-medium text-ink-900">{{ $item->name }}</div>
                                            @if($item->description)
                                                <div class="text-xs text-smoke-500">{{ Str::limit($item->description, 50) }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm text-smoke-600">
                                            {{ number_format($item->quantity, 2) }} {{ $item->unit }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <input type="number" name="items[{{ $index }}][quantity]" 
                                                value="{{ $item->quantity }}"
                                                min="0.01" max="{{ $item->quantity }}" step="0.01"
                                                class="item-quantity w-24 text-right rounded-lg border-smoke-300 shadow-sm focus:border-ink-500 focus:ring-ink-500 text-sm"
                                                onchange="updateItemTotal(this)">
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm text-ink-900">
                                            K{{ number_format($item->unit_price, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm font-medium text-ink-900 item-total">
                                            K{{ number_format($item->total_price, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-smoke-50">
                                <tr>
                                    <td colspan="5" class="px-4 py-3 text-right text-sm font-medium text-smoke-700">Subtotal</td>
                                    <td class="px-4 py-3 text-right text-sm font-bold text-ink-900" id="subtotal">K{{ number_format($quote->subtotal, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Additional Notes -->
                <div class="bg-white rounded-lg border border-smoke-200 p-6">
                    <h2 class="text-lg font-semibold text-ink-900 mb-4">Additional Information</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="notes" class="block text-sm font-medium text-smoke-700">Internal Notes</label>
                            <textarea name="notes" id="notes" rows="2"
                                class="mt-1 block w-full rounded-lg border-smoke-300 shadow-sm focus:border-ink-500 focus:ring-ink-500 sm:text-sm"
                                placeholder="Internal notes (not visible to supplier)">{{ old('notes') }}</textarea>
                        </div>

                        <div>
                            <label for="terms_conditions" class="block text-sm font-medium text-smoke-700">Terms & Conditions</label>
                            <textarea name="terms_conditions" id="terms_conditions" rows="3"
                                class="mt-1 block w-full rounded-lg border-smoke-300 shadow-sm focus:border-ink-500 focus:ring-ink-500 sm:text-sm"
                                placeholder="Specific terms and conditions for this order">{{ old('terms_conditions') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Budget Allocation -->
                <div class="bg-white rounded-lg border border-smoke-200 p-6">
                    <h2 class="text-lg font-semibold text-ink-900 mb-4">Budget Allocation</h2>
                    
                    <div>
                        <label for="budget_line_id" class="block text-sm font-medium text-smoke-700">Budget Line</label>
                        <select name="budget_line_id" id="budget_line_id"
                            class="mt-1 block w-full rounded-lg border-smoke-300 shadow-sm focus:border-ink-500 focus:ring-ink-500 sm:text-sm">
                            <option value="">Select budget line</option>
                            @foreach($budgetLines as $budgetLine)
                                <option value="{{ $budgetLine->id }}" 
                                    data-available="{{ $budgetLine->available_amount }}"
                                    {{ old('budget_line_id') == $budgetLine->id ? 'selected' : '' }}>
                                    {{ $budgetLine->name }} (K{{ number_format($budgetLine->available_amount, 2) }} available)
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-smoke-500">This PO will create a commitment against the selected budget line.</p>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="bg-white rounded-lg border border-smoke-200 p-6">
                    <h2 class="text-lg font-semibold text-ink-900 mb-4">Order Summary</h2>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-smoke-600">Items Subtotal</span>
                            <span class="text-ink-900" id="summary-subtotal">K{{ number_format($quote->subtotal, 2) }}</span>
                        </div>
                        
                        <div class="flex justify-between text-sm items-center">
                            <span class="text-smoke-600">Shipping</span>
                            <div class="flex items-center gap-1">
                                <span class="text-smoke-500">K</span>
                                <input type="number" name="shipping_amount" value="{{ old('shipping_amount', 0) }}" 
                                    min="0" step="0.01"
                                    class="w-20 text-right rounded border-smoke-300 text-sm focus:border-ink-500 focus:ring-ink-500"
                                    onchange="updateOrderTotal()">
                            </div>
                        </div>

                        <div class="flex justify-between text-sm items-center">
                            <span class="text-smoke-600">Discount</span>
                            <div class="flex items-center gap-1">
                                <span class="text-smoke-500">-K</span>
                                <input type="number" name="discount_amount" value="{{ old('discount_amount', 0) }}" 
                                    min="0" step="0.01"
                                    class="w-20 text-right rounded border-smoke-300 text-sm focus:border-ink-500 focus:ring-ink-500"
                                    onchange="updateOrderTotal()">
                            </div>
                        </div>

                        <div class="flex justify-between text-sm">
                            <span class="text-smoke-600">Tax ({{ $quote->tax_rate ?? 16 }}%)</span>
                            <span class="text-ink-900" id="summary-tax">K{{ number_format($quote->tax_total ?? 0, 2) }}</span>
                        </div>

                        <div class="border-t border-smoke-200 pt-3">
                            <div class="flex justify-between">
                                <span class="font-semibold text-ink-900">Total</span>
                                <span class="font-bold text-lg text-ink-900" id="summary-total">K{{ number_format($quote->total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-lg border border-smoke-200 p-6">
                    <div class="space-y-3">
                        <button type="submit" name="action" value="draft" class="w-full btn-primary">
                            Create Purchase Order
                        </button>
                        <a href="{{ route('projects.rfqs.show', [$project, $quote->rfq]) }}" class="w-full btn-secondary block text-center">
                            Cancel
                        </a>
                    </div>
                    <p class="mt-3 text-xs text-smoke-500 text-center">
                        The PO will be created as a draft. You can review and submit for approval afterward.
                    </p>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        function toggleItemRow(checkbox) {
            const row = checkbox.closest('tr');
            const quantityInput = row.querySelector('.item-quantity');
            
            if (checkbox.checked) {
                row.classList.remove('opacity-50');
                quantityInput.disabled = false;
            } else {
                row.classList.add('opacity-50');
                quantityInput.disabled = true;
            }
            updateOrderTotal();
        }

        function updateItemTotal(input) {
            const row = input.closest('tr');
            const unitPrice = parseFloat(row.dataset.unitPrice);
            const quantity = parseFloat(input.value) || 0;
            const total = unitPrice * quantity;
            
            row.querySelector('.item-total').textContent = 'K' + total.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            
            updateOrderTotal();
        }

        function updateOrderTotal() {
            let subtotal = 0;
            
            document.querySelectorAll('.item-row').forEach(row => {
                const checkbox = row.querySelector('.item-checkbox');
                if (checkbox.checked) {
                    const unitPrice = parseFloat(row.dataset.unitPrice);
                    const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
                    subtotal += unitPrice * quantity;
                }
            });

            const shipping = parseFloat(document.querySelector('[name="shipping_amount"]').value) || 0;
            const discount = parseFloat(document.querySelector('[name="discount_amount"]').value) || 0;
            const taxRate = {{ $quote->tax_rate ?? 16 }} / 100;
            const tax = subtotal * taxRate;
            const total = subtotal + shipping - discount + tax;

            document.getElementById('subtotal').textContent = 'K' + subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('summary-subtotal').textContent = 'K' + subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('summary-tax').textContent = 'K' + tax.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('summary-total').textContent = 'K' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        // Filter out unchecked items before submit
        document.getElementById('poForm').addEventListener('submit', function(e) {
            document.querySelectorAll('.item-row').forEach(row => {
                const checkbox = row.querySelector('.item-checkbox');
                if (!checkbox.checked) {
                    row.querySelectorAll('input').forEach(input => input.disabled = true);
                }
            });
        });
    </script>
    @endpush
</x-workspace-layout>
