@section('title', 'Create RFQ - ' . $project->name)

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
            <span class="text-ink-900 font-medium">Create</span>
        </div>
        <h1 class="text-2xl font-semibold text-ink-900">Create Request for Quotation</h1>
        <p class="text-smoke-600 mt-1">Select a requisition and invite suppliers to submit quotes.</p>
    </div>

    @if($availableRequisitions->isEmpty() && !$requisition)
        <div class="card p-6">
            <x-ui.empty-state
                title="No Approved Requisitions"
                description="You need an approved requisition to create an RFQ. Create and get a requisition approved first."
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'
            >
                <a href="{{ route('projects.requisitions.create', $project) }}" class="btn-primary mt-4">
                    Create Requisition
                </a>
            </x-ui.empty-state>
        </div>
    @else
        <form action="{{ route('projects.rfqs.store', $project) }}" method="POST" id="rfqForm">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Form -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Select Requisition -->
                    <div class="card p-6">
                        <h3 class="text-lg font-medium text-ink-900 mb-4">Source Requisition</h3>
                        
                        @if($requisition)
                            <input type="hidden" name="requisition_id" value="{{ $requisition->id }}">
                            <div class="p-4 bg-smoke-50 rounded-xl border border-smoke-200">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <span class="font-mono text-sm text-smoke-600">{{ $requisition->requisition_number }}</span>
                                        <h4 class="text-ink-900 font-medium mt-1">{{ $requisition->title }}</h4>
                                        <p class="text-sm text-smoke-600 mt-1">{{ Str::limit($requisition->description, 100) }}</p>
                                    </div>
                                    <span class="text-lg font-semibold text-ink-900">${{ number_format($requisition->estimated_total, 2) }}</span>
                                </div>
                                <div class="mt-4 pt-4 border-t border-smoke-200">
                                    <span class="text-sm text-smoke-600">Items:</span>
                                    <ul class="mt-2 space-y-1">
                                        @foreach($requisition->items as $item)
                                            <li class="text-sm text-ink-900">• {{ $item->name }} ({{ $item->quantity }} {{ $item->unit }})</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @else
                            <div>
                                <label for="requisition_id" class="block text-sm font-medium text-ink-700 mb-1">
                                    Select Requisition <span class="text-red-500">*</span>
                                </label>
                                <select name="requisition_id" 
                                        id="requisition_id" 
                                        class="input-field @error('requisition_id') border-red-500 @enderror"
                                        required
                                        onchange="updateRequisitionDetails()">
                                    <option value="">Select an approved requisition...</option>
                                    @foreach($availableRequisitions as $req)
                                        <option value="{{ $req->id }}" 
                                                data-title="{{ $req->title }}"
                                                data-description="{{ $req->description }}"
                                                data-total="{{ $req->estimated_total }}"
                                                data-items='@json($req->items)'
                                                {{ old('requisition_id') == $req->id ? 'selected' : '' }}>
                                            {{ $req->requisition_number }} - {{ $req->title }} (${{ number_format($req->estimated_total, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('requisition_id')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div id="requisitionDetails" class="mt-4 p-4 bg-smoke-50 rounded-xl border border-smoke-200 hidden">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h4 class="text-ink-900 font-medium" id="reqTitle"></h4>
                                        <p class="text-sm text-smoke-600 mt-1" id="reqDescription"></p>
                                    </div>
                                    <span class="text-lg font-semibold text-ink-900" id="reqTotal"></span>
                                </div>
                                <div class="mt-4 pt-4 border-t border-smoke-200">
                                    <span class="text-sm text-smoke-600">Items:</span>
                                    <ul class="mt-2 space-y-1" id="reqItems"></ul>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- RFQ Details -->
                    <div class="card p-6">
                        <h3 class="text-lg font-medium text-ink-900 mb-4">RFQ Details</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="title" class="block text-sm font-medium text-ink-700 mb-1">
                                    Title <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="title" 
                                       id="title" 
                                       value="{{ old('title', $requisition?->title ? 'RFQ - ' . $requisition->title : '') }}"
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
                                          class="input-field @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="closing_date" class="block text-sm font-medium text-ink-700 mb-1">
                                        Closing Date <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" 
                                           name="closing_date" 
                                           id="closing_date" 
                                           value="{{ old('closing_date', date('Y-m-d', strtotime('+7 days'))) }}"
                                           min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                           class="input-field @error('closing_date') border-red-500 @enderror"
                                           required>
                                    @error('closing_date')
                                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="delivery_date" class="block text-sm font-medium text-ink-700 mb-1">Expected Delivery Date</label>
                                    <input type="date" 
                                           name="delivery_date" 
                                           id="delivery_date" 
                                           value="{{ old('delivery_date') }}"
                                           class="input-field @error('delivery_date') border-red-500 @enderror">
                                    @error('delivery_date')
                                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Terms & Instructions -->
                    <div class="card p-6">
                        <h3 class="text-lg font-medium text-ink-900 mb-4">Terms & Instructions</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="submission_instructions" class="block text-sm font-medium text-ink-700 mb-1">Submission Instructions</label>
                                <textarea name="submission_instructions" 
                                          id="submission_instructions" 
                                          rows="3"
                                          class="input-field"
                                          placeholder="Instructions for suppliers on how to submit their quotes...">{{ old('submission_instructions') }}</textarea>
                            </div>

                            <div>
                                <label for="terms_and_conditions" class="block text-sm font-medium text-ink-700 mb-1">Terms & Conditions</label>
                                <textarea name="terms_and_conditions" 
                                          id="terms_and_conditions" 
                                          rows="3"
                                          class="input-field"
                                          placeholder="Payment terms, delivery terms, warranties, etc.">{{ old('terms_and_conditions') }}</textarea>
                            </div>

                            <div>
                                <label for="evaluation_criteria" class="block text-sm font-medium text-ink-700 mb-1">Evaluation Criteria</label>
                                <textarea name="evaluation_criteria" 
                                          id="evaluation_criteria" 
                                          rows="3"
                                          class="input-field"
                                          placeholder="Criteria for evaluating and comparing quotes...">{{ old('evaluation_criteria', "1. Price (40%)\n2. Delivery Time (20%)\n3. Quality/Specifications (25%)\n4. Supplier Track Record (15%)") }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Select Suppliers -->
                    <div class="card p-6">
                        <h3 class="text-lg font-medium text-ink-900 mb-4">Invite Suppliers</h3>
                        
                        <div>
                            <label for="min_quotes" class="block text-sm font-medium text-ink-700 mb-1">
                                Minimum Quotes Required <span class="text-red-500">*</span>
                            </label>
                            <select name="min_quotes" id="min_quotes" class="input-field mb-4" required>
                                <option value="1" {{ old('min_quotes') == 1 ? 'selected' : '' }}>1 Quote</option>
                                <option value="2" {{ old('min_quotes') == 2 ? 'selected' : '' }}>2 Quotes</option>
                                <option value="3" {{ old('min_quotes', 3) == 3 ? 'selected' : '' }}>3 Quotes</option>
                                <option value="4" {{ old('min_quotes') == 4 ? 'selected' : '' }}>4 Quotes</option>
                                <option value="5" {{ old('min_quotes') == 5 ? 'selected' : '' }}>5 Quotes</option>
                            </select>
                        </div>

                        <div class="space-y-2 max-h-96 overflow-y-auto">
                            @forelse($suppliers as $supplier)
                                <label class="flex items-start gap-3 p-3 rounded-lg border border-smoke-200 hover:bg-smoke-50 cursor-pointer transition-colors">
                                    <input type="checkbox" 
                                           name="supplier_ids[]" 
                                           value="{{ $supplier->id }}"
                                           class="mt-1 rounded border-smoke-300 text-ink-900 focus:ring-ink-500"
                                           {{ in_array($supplier->id, old('supplier_ids', [])) ? 'checked' : '' }}>
                                    <div class="flex-1">
                                        <div class="font-medium text-ink-900">{{ $supplier->name }}</div>
                                        <div class="text-sm text-smoke-600">{{ $supplier->code }}</div>
                                        @if($supplier->email)
                                            <div class="text-xs text-smoke-500 mt-1">{{ $supplier->email }}</div>
                                        @endif
                                    </div>
                                </label>
                            @empty
                                <div class="text-center py-4">
                                    <p class="text-sm text-smoke-500">No active suppliers found.</p>
                                    <p class="text-xs text-smoke-400 mt-1">Add suppliers in the admin panel first.</p>
                                </div>
                            @endforelse
                        </div>

                        @error('supplier_ids')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror

                        <p class="mt-3 text-xs text-smoke-500">
                            Selected: <span id="selectedCount">0</span> supplier(s)
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="card p-6">
                        <div class="space-y-3">
                            <button type="submit" class="btn-primary w-full justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Create RFQ
                            </button>
                            <a href="{{ route('projects.rfqs.index', $project) }}" class="btn-ghost w-full justify-center">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        @push('scripts')
        <script>
            function updateRequisitionDetails() {
                const select = document.getElementById('requisition_id');
                const details = document.getElementById('requisitionDetails');
                const selected = select.options[select.selectedIndex];
                
                if (!selected.value) {
                    details.classList.add('hidden');
                    return;
                }
                
                details.classList.remove('hidden');
                document.getElementById('reqTitle').textContent = selected.dataset.title;
                document.getElementById('reqDescription').textContent = selected.dataset.description || 'No description';
                document.getElementById('reqTotal').textContent = '$' + parseFloat(selected.dataset.total).toFixed(2);
                
                const items = JSON.parse(selected.dataset.items || '[]');
                const itemsList = document.getElementById('reqItems');
                itemsList.innerHTML = items.map(item => 
                    `<li class="text-sm text-ink-900">• ${item.name} (${item.quantity} ${item.unit})</li>`
                ).join('');
                
                // Auto-fill title
                const titleInput = document.getElementById('title');
                if (!titleInput.value) {
                    titleInput.value = 'RFQ - ' + selected.dataset.title;
                }
            }

            // Count selected suppliers
            document.querySelectorAll('input[name="supplier_ids[]"]').forEach(checkbox => {
                checkbox.addEventListener('change', updateSupplierCount);
            });

            function updateSupplierCount() {
                const count = document.querySelectorAll('input[name="supplier_ids[]"]:checked').length;
                document.getElementById('selectedCount').textContent = count;
            }

            // Initialize on page load
            document.addEventListener('DOMContentLoaded', function() {
                updateRequisitionDetails();
                updateSupplierCount();
            });
        </script>
        @endpush
    @endif
</x-workspace-layout>
