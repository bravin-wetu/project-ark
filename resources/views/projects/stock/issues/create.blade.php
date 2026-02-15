@section('title', 'New Issue Request - ' . $project->name)

<x-workspace-layout :workspace="$project" :workspaceType="'projects'">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
            <a href="{{ route('projects.stock.index', $project) }}" class="hover:text-ink-900 transition-colors">Stock Items</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('projects.stock.issues', $project) }}" class="hover:text-ink-900 transition-colors">Issues</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">New Request</span>
        </div>
        <h1 class="text-2xl font-semibold text-ink-900">New Stock Issue Request</h1>
        <p class="text-smoke-600 mt-1">Request items from inventory</p>
    </div>

    <form action="{{ route('projects.stock.store-issue', $project) }}" method="POST" class="max-w-4xl" x-data="issueForm()">
        @csrf

        <div class="space-y-6">
            <!-- Request Details -->
            <div class="card p-6">
                <h2 class="text-lg font-medium text-ink-900 mb-4">Request Details</h2>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="hub_id" class="block text-sm font-medium text-ink-700 mb-1">Issuing Hub *</label>
                        <select name="hub_id" id="hub_id" required class="form-select w-full">
                            <option value="">Select hub...</option>
                            @foreach($hubs as $hub)
                                <option value="{{ $hub->id }}" {{ old('hub_id') == $hub->id ? 'selected' : '' }}>{{ $hub->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="required_date" class="block text-sm font-medium text-ink-700 mb-1">Required By</label>
                        <input type="date" name="required_date" id="required_date" 
                               value="{{ old('required_date') }}" class="form-input w-full">
                    </div>

                    <div class="col-span-2">
                        <label for="purpose" class="block text-sm font-medium text-ink-700 mb-1">Purpose / Reason *</label>
                        <textarea name="purpose" id="purpose" required rows="2" class="form-textarea w-full"
                                  placeholder="Describe why these items are needed...">{{ old('purpose') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-medium text-ink-900">Requested Items</h2>
                    <button type="button" @click="addItem()" class="btn-secondary text-sm">
                        + Add Item
                    </button>
                </div>

                <div class="space-y-4">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="p-4 border border-smoke-200 rounded-lg bg-smoke-50">
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-5">
                                    <label class="block text-sm font-medium text-ink-700 mb-1">Stock Item *</label>
                                    <select :name="'items[' + index + '][stock_item_id]'" required class="form-select w-full"
                                            @change="updateItemInfo(index, $event.target.value)">
                                        <option value="">Select item...</option>
                                        @foreach($stockItems as $stockItem)
                                            <option value="{{ $stockItem->id }}" 
                                                    data-available="{{ $stockItem->getCurrentStock() }}"
                                                    data-unit="{{ $stockItem->unit }}">
                                                {{ $stockItem->sku }} - {{ $stockItem->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-ink-700 mb-1">Qty Requested *</label>
                                    <input type="number" :name="'items[' + index + '][quantity_requested]'" required min="1"
                                           x-model="item.quantity" class="form-input w-full" placeholder="0">
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-ink-700 mb-1">Available</label>
                                    <div class="form-input w-full bg-smoke-100 text-smoke-600" x-text="item.available + ' ' + item.unit">-</div>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-ink-700 mb-1">Unit</label>
                                    <div class="form-input w-full bg-smoke-100 text-smoke-600" x-text="item.unit || '-'">-</div>
                                </div>
                                <div class="col-span-1 flex items-end">
                                    <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                                            class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="col-span-12">
                                    <label class="block text-sm font-medium text-ink-700 mb-1">Notes</label>
                                    <input type="text" :name="'items[' + index + '][notes]'" class="form-input w-full text-sm"
                                           placeholder="Optional notes for this item...">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="items.length === 0" class="text-center py-8 text-smoke-500">
                    No items added. Click "Add Item" to request stock items.
                </div>
            </div>

            <!-- Additional Notes -->
            <div class="card p-6">
                <h2 class="text-lg font-medium text-ink-900 mb-4">Additional Information</h2>
                
                <div>
                    <label for="notes" class="block text-sm font-medium text-ink-700 mb-1">Notes</label>
                    <textarea name="notes" id="notes" rows="3" class="form-textarea w-full"
                              placeholder="Any additional information about this request...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between">
                <a href="{{ route('projects.stock.issues', $project) }}" class="text-smoke-600 hover:text-ink-900">
                    ← Cancel
                </a>
                <div class="flex gap-3">
                    <button type="submit" name="status" value="draft" class="btn-secondary">
                        Save as Draft
                    </button>
                    <button type="submit" name="status" value="pending_approval" class="btn-primary">
                        Submit for Approval
                    </button>
                </div>
            </div>
        </div>
    </form>

    <script>
        function issueForm() {
            return {
                items: [{ stock_item_id: '', quantity: '', available: 0, unit: '' }],
                
                addItem() {
                    this.items.push({ stock_item_id: '', quantity: '', available: 0, unit: '' });
                },
                
                removeItem(index) {
                    this.items.splice(index, 1);
                },
                
                updateItemInfo(index, stockItemId) {
                    const select = event.target;
                    const option = select.options[select.selectedIndex];
                    if (option.value) {
                        this.items[index].available = option.dataset.available || 0;
                        this.items[index].unit = option.dataset.unit || '';
                    } else {
                        this.items[index].available = 0;
                        this.items[index].unit = '';
                    }
                }
            }
        }
    </script>
</x-workspace-layout>
