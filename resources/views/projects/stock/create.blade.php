@section('title', 'Add Stock Item - ' . $project->name)

<x-workspace-layout :workspace="$project" :workspaceType="'projects'">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
            <a href="{{ route('projects.stock.index', $project) }}" class="hover:text-ink-900 transition-colors">Stock Items</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">Add New</span>
        </div>
        <h1 class="text-2xl font-semibold text-ink-900">Add Stock Item</h1>
        <p class="text-smoke-600 mt-1">Create a new inventory item to track stock levels</p>
    </div>

    <form action="{{ route('projects.stock.store', $project) }}" method="POST" class="max-w-3xl">
        @csrf

        @if($goodsReceiptItem)
        <input type="hidden" name="goods_receipt_item_id" value="{{ $goodsReceiptItem->id }}">
        @endif

        <div class="space-y-6">
            <!-- Basic Information -->
            <div class="card p-6">
                <h2 class="text-lg font-medium text-ink-900 mb-4">Basic Information</h2>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 md:col-span-1">
                        <label for="sku" class="block text-sm font-medium text-ink-700 mb-1">SKU *</label>
                        <input type="text" name="sku" id="sku" required
                               value="{{ old('sku', $suggestedSku ?? '') }}"
                               class="form-input w-full font-mono" placeholder="e.g., STK-001">
                        <p class="text-xs text-smoke-500 mt-1">Stock keeping unit - unique identifier</p>
                        @error('sku')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="category" class="block text-sm font-medium text-ink-700 mb-1">Category</label>
                        <select name="category" id="category" class="form-select w-full">
                            <option value="">Select category...</option>
                            <option value="Office Supplies" {{ old('category', $goodsReceiptItem?->purchaseOrderItem?->description) == 'Office Supplies' ? 'selected' : '' }}>Office Supplies</option>
                            <option value="Medical Supplies" {{ old('category') == 'Medical Supplies' ? 'selected' : '' }}>Medical Supplies</option>
                            <option value="Cleaning Materials" {{ old('category') == 'Cleaning Materials' ? 'selected' : '' }}>Cleaning Materials</option>
                            <option value="Food Items" {{ old('category') == 'Food Items' ? 'selected' : '' }}>Food Items</option>
                            <option value="Maintenance" {{ old('category') == 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                            <option value="IT Equipment" {{ old('category') == 'IT Equipment' ? 'selected' : '' }}>IT Equipment</option>
                            <option value="Other" {{ old('category') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div class="col-span-2">
                        <label for="name" class="block text-sm font-medium text-ink-700 mb-1">Item Name *</label>
                        <input type="text" name="name" id="name" required
                               value="{{ old('name', $goodsReceiptItem?->purchaseOrderItem?->description ?? '') }}"
                               class="form-input w-full" placeholder="e.g., A4 Printing Paper">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="col-span-2">
                        <label for="description" class="block text-sm font-medium text-ink-700 mb-1">Description</label>
                        <textarea name="description" id="description" rows="2" class="form-textarea w-full"
                                  placeholder="Detailed description of the item...">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Inventory Settings -->
            <div class="card p-6">
                <h2 class="text-lg font-medium text-ink-900 mb-4">Inventory Settings</h2>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="unit" class="block text-sm font-medium text-ink-700 mb-1">Unit of Measure *</label>
                        <select name="unit" id="unit" required class="form-select w-full">
                            <option value="pcs" {{ old('unit', $goodsReceiptItem?->purchaseOrderItem?->unit ?? 'pcs') == 'pcs' ? 'selected' : '' }}>Pieces (pcs)</option>
                            <option value="boxes" {{ old('unit') == 'boxes' ? 'selected' : '' }}>Boxes</option>
                            <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>Kilograms (kg)</option>
                            <option value="liters" {{ old('unit') == 'liters' ? 'selected' : '' }}>Liters</option>
                            <option value="packs" {{ old('unit') == 'packs' ? 'selected' : '' }}>Packs</option>
                            <option value="reams" {{ old('unit') == 'reams' ? 'selected' : '' }}>Reams</option>
                            <option value="sets" {{ old('unit') == 'sets' ? 'selected' : '' }}>Sets</option>
                            <option value="cartons" {{ old('unit') == 'cartons' ? 'selected' : '' }}>Cartons</option>
                        </select>
                    </div>

                    <div>
                        <label for="location" class="block text-sm font-medium text-ink-700 mb-1">Storage Location</label>
                        <input type="text" name="location" id="location" value="{{ old('location') }}"
                               class="form-input w-full" placeholder="e.g., Warehouse A, Shelf 3">
                    </div>

                    <div>
                        <label for="reorder_level" class="block text-sm font-medium text-ink-700 mb-1">Reorder Level</label>
                        <input type="number" name="reorder_level" id="reorder_level" min="0"
                               value="{{ old('reorder_level', 10) }}" class="form-input w-full">
                        <p class="text-xs text-smoke-500 mt-1">Alert when stock falls below this level</p>
                    </div>

                    <div>
                        <label for="minimum_order_quantity" class="block text-sm font-medium text-ink-700 mb-1">Min Order Quantity</label>
                        <input type="number" name="minimum_order_quantity" id="minimum_order_quantity" min="1"
                               value="{{ old('minimum_order_quantity', 1) }}" class="form-input w-full">
                        <p class="text-xs text-smoke-500 mt-1">Minimum quantity when ordering</p>
                    </div>
                </div>
            </div>

            <!-- Initial Batch (if from goods receipt) -->
            @if($goodsReceiptItem)
            <div class="card p-6 border-green-200 bg-green-50">
                <h2 class="text-lg font-medium text-ink-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Initial Batch from Goods Receipt
                </h2>
                <p class="text-sm text-smoke-600 mb-4">
                    This stock item will be created with an initial batch from 
                    <strong>{{ $goodsReceiptItem->goodsReceipt->grn_number }}</strong>
                </p>
                
                <div class="grid grid-cols-3 gap-4 bg-white p-4 rounded-lg">
                    <div>
                        <span class="text-sm text-smoke-500">Quantity Received</span>
                        <p class="font-medium text-ink-900">{{ number_format($goodsReceiptItem->quantity_received) }} {{ $goodsReceiptItem->purchaseOrderItem?->unit ?? 'pcs' }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-smoke-500">Unit Cost</span>
                        <p class="font-medium text-ink-900">KES {{ number_format($goodsReceiptItem->purchaseOrderItem?->unit_price ?? 0, 2) }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-smoke-500">Total Value</span>
                        <p class="font-medium text-ink-900">KES {{ number_format($goodsReceiptItem->quantity_received * ($goodsReceiptItem->purchaseOrderItem?->unit_price ?? 0), 2) }}</p>
                    </div>
                </div>

                <input type="hidden" name="initial_quantity" value="{{ $goodsReceiptItem->quantity_received }}">
                <input type="hidden" name="initial_unit_cost" value="{{ $goodsReceiptItem->purchaseOrderItem?->unit_price ?? 0 }}">
            </div>
            @else
            <!-- Manual Initial Batch -->
            <div class="card p-6">
                <h2 class="text-lg font-medium text-ink-900 mb-4">Initial Stock (Optional)</h2>
                <p class="text-sm text-smoke-600 mb-4">If you have existing stock, enter the opening quantity here.</p>
                
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label for="initial_quantity" class="block text-sm font-medium text-ink-700 mb-1">Opening Quantity</label>
                        <input type="number" name="initial_quantity" id="initial_quantity" min="0"
                               value="{{ old('initial_quantity', 0) }}" class="form-input w-full">
                    </div>
                    <div>
                        <label for="initial_unit_cost" class="block text-sm font-medium text-ink-700 mb-1">Unit Cost (KES)</label>
                        <input type="number" name="initial_unit_cost" id="initial_unit_cost" min="0" step="0.01"
                               value="{{ old('initial_unit_cost', 0) }}" class="form-input w-full">
                    </div>
                    <div>
                        <label for="initial_expiry_date" class="block text-sm font-medium text-ink-700 mb-1">Expiry Date</label>
                        <input type="date" name="initial_expiry_date" id="initial_expiry_date"
                               value="{{ old('initial_expiry_date') }}" class="form-input w-full">
                    </div>
                </div>
            </div>
            @endif

            <!-- Supplier Information -->
            <div class="card p-6">
                <h2 class="text-lg font-medium text-ink-900 mb-4">Supplier Information (Optional)</h2>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="preferred_supplier_id" class="block text-sm font-medium text-ink-700 mb-1">Preferred Supplier</label>
                        <select name="preferred_supplier_id" id="preferred_supplier_id" class="form-select w-full">
                            <option value="">Select supplier...</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('preferred_supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="supplier_sku" class="block text-sm font-medium text-ink-700 mb-1">Supplier SKU</label>
                        <input type="text" name="supplier_sku" id="supplier_sku" value="{{ old('supplier_sku') }}"
                               class="form-input w-full" placeholder="Supplier's product code">
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between">
                <a href="{{ route('projects.stock.index', $project) }}" class="text-smoke-600 hover:text-ink-900">
                    ← Cancel
                </a>
                <div class="flex gap-3">
                    <button type="submit" name="action" value="save_and_new" class="btn-secondary">
                        Save & Add Another
                    </button>
                    <button type="submit" class="btn-primary">
                        Create Stock Item
                    </button>
                </div>
            </div>
        </div>
    </form>
</x-workspace-layout>
