@section('title', 'Edit Requisition - ' . $project->name)

<x-workspace-layout :workspace="$project" :workspaceType="'projects'">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
            <a href="{{ route('projects.show', $project) }}" class="hover:text-ink-900 transition-colors">{{ $project->name }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('projects.requisitions.index', $project) }}" class="hover:text-ink-900 transition-colors">Requisitions</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('projects.requisitions.show', [$project, $requisition]) }}" class="hover:text-ink-900 transition-colors">{{ $requisition->requisition_number }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">Edit</span>
        </div>
        <h1 class="text-2xl font-semibold text-ink-900">Edit Requisition</h1>
        <p class="text-smoke-600 mt-1 font-mono">{{ $requisition->requisition_number }}</p>
    </div>

    <form action="{{ route('projects.requisitions.update', [$project, $requisition]) }}" method="POST" id="requisitionForm">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="card p-6">
                    <h3 class="text-lg font-medium text-ink-900 mb-4">Basic Information</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="title" class="block text-sm font-medium text-ink-700 mb-1">
                                Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="title" 
                                   id="title" 
                                   value="{{ old('title', $requisition->title) }}"
                                   class="input-field @error('title') border-red-500 @enderror"
                                   required>
                            @error('title')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-ink-700 mb-1">Description</label>
                            <textarea name="description" 
                                      id="description" 
                                      rows="3"
                                      class="input-field @error('description') border-red-500 @enderror">{{ old('description', $requisition->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="justification" class="block text-sm font-medium text-ink-700 mb-1">Justification</label>
                            <textarea name="justification" 
                                      id="justification" 
                                      rows="2"
                                      class="input-field @error('justification') border-red-500 @enderror">{{ old('justification', $requisition->justification) }}</textarea>
                            @error('justification')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Line Items -->
                <div class="card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-ink-900">Line Items</h3>
                        <button type="button" 
                                onclick="addItem()"
                                class="btn-secondary text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Item
                        </button>
                    </div>

                    <div id="itemsContainer" class="space-y-4">
                        <!-- Existing items will be loaded here -->
                    </div>

                    @error('items')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror

                    <!-- Totals -->
                    <div class="mt-6 pt-4 border-t border-smoke-200">
                        <div class="flex justify-end">
                            <div class="w-64">
                                <div class="flex justify-between py-2">
                                    <span class="text-sm text-smoke-600">Estimated Total:</span>
                                    <span class="text-lg font-semibold text-ink-900" id="estimatedTotal">${{ number_format($requisition->estimated_total, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Budget & Delivery -->
                <div class="card p-6">
                    <h3 class="text-lg font-medium text-ink-900 mb-4">Budget & Delivery</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="budget_line_id" class="block text-sm font-medium text-ink-700 mb-1">
                                Budget Line <span class="text-red-500">*</span>
                            </label>
                            <select name="budget_line_id" 
                                    id="budget_line_id" 
                                    class="input-field @error('budget_line_id') border-red-500 @enderror"
                                    required>
                                <option value="">Select budget line...</option>
                                @foreach($budgetLines as $line)
                                    <option value="{{ $line->id }}" 
                                            data-available="{{ $line->available }}"
                                            {{ old('budget_line_id', $requisition->budget_line_id) == $line->id ? 'selected' : '' }}>
                                        {{ $line->code }} - {{ $line->name }} (${{ number_format($line->available, 2) }} available)
                                    </option>
                                @endforeach
                            </select>
                            @error('budget_line_id')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-smoke-500">Available budget: <span id="availableBudget">—</span></p>
                        </div>

                        <div>
                            <label for="delivery_hub_id" class="block text-sm font-medium text-ink-700 mb-1">Delivery Location</label>
                            <select name="delivery_hub_id" 
                                    id="delivery_hub_id" 
                                    class="input-field @error('delivery_hub_id') border-red-500 @enderror">
                                <option value="">Select hub...</option>
                                @foreach($hubs as $hub)
                                    <option value="{{ $hub->id }}" {{ old('delivery_hub_id', $requisition->delivery_hub_id) == $hub->id ? 'selected' : '' }}>
                                        {{ $hub->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('delivery_hub_id')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="required_date" class="block text-sm font-medium text-ink-700 mb-1">Required By Date</label>
                            <input type="date" 
                                   name="required_date" 
                                   id="required_date" 
                                   value="{{ old('required_date', $requisition->required_date?->format('Y-m-d')) }}"
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                   class="input-field @error('required_date') border-red-500 @enderror">
                            @error('required_date')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="priority" class="block text-sm font-medium text-ink-700 mb-1">
                                Priority <span class="text-red-500">*</span>
                            </label>
                            <select name="priority" 
                                    id="priority" 
                                    class="input-field @error('priority') border-red-500 @enderror"
                                    required>
                                <option value="low" {{ old('priority', $requisition->priority) == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="normal" {{ old('priority', $requisition->priority) == 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="high" {{ old('priority', $requisition->priority) == 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ old('priority', $requisition->priority) == 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                            @error('priority')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="card p-6">
                    <h3 class="text-lg font-medium text-ink-900 mb-4">Additional Notes</h3>
                    <textarea name="notes" 
                              id="notes" 
                              rows="4"
                              class="input-field">{{ old('notes', $requisition->notes) }}</textarea>
                </div>

                <!-- Actions -->
                <div class="card p-6">
                    <div class="space-y-3">
                        <button type="submit" 
                                name="action" 
                                value="submit"
                                class="btn-primary w-full justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Update & Submit for Approval
                        </button>
                        <button type="submit" 
                                name="action" 
                                value="draft"
                                class="btn-secondary w-full justify-center">
                            Save Changes
                        </button>
                        <a href="{{ route('projects.requisitions.show', [$project, $requisition]) }}" 
                           class="btn-ghost w-full justify-center">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Item Template (hidden) -->
    <template id="itemTemplate">
        <div class="item-row p-4 bg-smoke-50 rounded-xl border border-smoke-200" data-index="__INDEX__">
            <input type="hidden" name="items[__INDEX__][id]" value="">
            <div class="flex items-start justify-between mb-4">
                <span class="text-sm font-medium text-smoke-600">Item #<span class="item-number">__NUMBER__</span></span>
                <button type="button" onclick="removeItem(this)" class="text-smoke-400 hover:text-red-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-ink-700 mb-1">
                        Item Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="items[__INDEX__][name]" 
                           class="input-field item-name"
                           required>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-ink-700 mb-1">Description</label>
                    <textarea name="items[__INDEX__][description]" 
                              rows="2"
                              class="input-field item-description"></textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-ink-700 mb-1">Specifications</label>
                    <textarea name="items[__INDEX__][specifications]" 
                              rows="2"
                              class="input-field item-specifications"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-ink-700 mb-1">Type</label>
                    <select name="items[__INDEX__][item_type]" class="input-field item-type">
                        <option value="goods">Goods</option>
                        <option value="services">Services</option>
                        <option value="works">Works</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-ink-700 mb-1">Unit</label>
                    <input type="text" 
                           name="items[__INDEX__][unit]" 
                           value="pcs"
                           class="input-field item-unit">
                </div>

                <div>
                    <label class="block text-sm font-medium text-ink-700 mb-1">
                        Quantity <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="items[__INDEX__][quantity]" 
                           step="0.01"
                           min="0.01"
                           value="1"
                           class="input-field item-quantity"
                           onchange="updateItemTotal(this)"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-ink-700 mb-1">
                        Est. Unit Price <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-smoke-500">$</span>
                        <input type="number" 
                               name="items[__INDEX__][estimated_unit_price]" 
                               step="0.01"
                               min="0"
                               value="0"
                               class="input-field pl-7 item-price"
                               onchange="updateItemTotal(this)"
                               required>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-smoke-200 flex justify-end">
                <div class="text-right">
                    <span class="text-sm text-smoke-600">Item Total:</span>
                    <span class="ml-2 text-lg font-semibold text-ink-900 item-total">$0.00</span>
                </div>
            </div>
        </div>
    </template>

    @push('scripts')
    <script>
        let itemIndex = 0;
        const existingItems = @json($requisition->items);

        function addItem(data = null) {
            const template = document.getElementById('itemTemplate').innerHTML;
            const html = template
                .replace(/__INDEX__/g, itemIndex)
                .replace(/__NUMBER__/g, itemIndex + 1);
            
            document.getElementById('itemsContainer').insertAdjacentHTML('beforeend', html);
            
            if (data) {
                const row = document.querySelector(`.item-row[data-index="${itemIndex}"]`);
                row.querySelector('input[name$="[id]"]').value = data.id || '';
                row.querySelector('.item-name').value = data.name || '';
                row.querySelector('.item-description').value = data.description || '';
                row.querySelector('.item-specifications').value = data.specifications || '';
                row.querySelector('.item-type').value = data.item_type || 'goods';
                row.querySelector('.item-unit').value = data.unit || 'pcs';
                row.querySelector('.item-quantity').value = data.quantity || 1;
                row.querySelector('.item-price').value = data.estimated_unit_price || 0;
                
                const total = (parseFloat(data.quantity) || 0) * (parseFloat(data.estimated_unit_price) || 0);
                row.querySelector('.item-total').textContent = '$' + total.toFixed(2);
            }
            
            itemIndex++;
            updateNumbers();
        }

        function removeItem(button) {
            const row = button.closest('.item-row');
            row.remove();
            updateNumbers();
            updateTotal();
        }

        function updateNumbers() {
            document.querySelectorAll('.item-row').forEach((row, index) => {
                row.querySelector('.item-number').textContent = index + 1;
            });
        }

        function updateItemTotal(input) {
            const row = input.closest('.item-row');
            const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const total = quantity * price;
            row.querySelector('.item-total').textContent = '$' + total.toFixed(2);
            updateTotal();
        }

        function updateTotal() {
            let total = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
                const price = parseFloat(row.querySelector('.item-price').value) || 0;
                total += quantity * price;
            });
            document.getElementById('estimatedTotal').textContent = '$' + total.toFixed(2);
        }

        // Update available budget display
        document.getElementById('budget_line_id').addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const available = selected.dataset.available;
            document.getElementById('availableBudget').textContent = available ? '$' + parseFloat(available).toFixed(2) : '—';
        });

        // Load existing items on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (existingItems.length > 0) {
                existingItems.forEach(item => addItem(item));
            } else {
                addItem();
            }
            // Trigger budget line change to show available
            document.getElementById('budget_line_id').dispatchEvent(new Event('change'));
        });
    </script>
    @endpush
</x-workspace-layout>
