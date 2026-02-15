@section('title', 'Create Department Budget')

<x-app-layout>
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Dashboard
            </a>
            <h1 class="mt-2 text-2xl font-bold text-black">Create Department Budget</h1>
            <p class="text-gray-500">Set up an internal operational budget workspace</p>
        </div>

        <!-- Step Indicator -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div id="step1-indicator" class="flex items-center justify-center w-8 h-8 bg-black text-white rounded-full text-sm font-medium">1</div>
                    <span class="ml-2 text-sm font-medium text-black">Budget Details</span>
                </div>
                <div class="flex-1 mx-4 h-px bg-gray-200"></div>
                <div class="flex items-center">
                    <div id="step2-indicator" class="flex items-center justify-center w-8 h-8 bg-gray-200 text-gray-500 rounded-full text-sm font-medium">2</div>
                    <span class="ml-2 text-sm font-medium text-gray-500">Budget Lines</span>
                </div>
                <div class="flex-1 mx-4 h-px bg-gray-200"></div>
                <div class="flex items-center">
                    <div id="step3-indicator" class="flex items-center justify-center w-8 h-8 bg-gray-200 text-gray-500 rounded-full text-sm font-medium">3</div>
                    <span class="ml-2 text-sm font-medium text-gray-500">Review & Activate</span>
                </div>
            </div>
        </div>

        <form id="budget-form" action="{{ route('department-budgets.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Step 1: Budget Details -->
            <div id="step1" class="step-content">
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-black mb-4">Budget Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Department -->
                        <div>
                            <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">Department *</label>
                            <select id="department_id" name="department_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-black focus:border-black">
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fiscal Year -->
                        <div>
                            <label for="fiscal_year" class="block text-sm font-medium text-gray-700 mb-1">Fiscal Year *</label>
                            <select id="fiscal_year" name="fiscal_year" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-black focus:border-black">
                                @foreach($fiscalYears as $fy)
                                    <option value="{{ $fy }}" {{ old('fiscal_year', 'FY' . date('Y')) == $fy ? 'selected' : '' }}>
                                        {{ $fy }}
                                    </option>
                                @endforeach
                            </select>
                            @error('fiscal_year')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Budget Name (Optional) -->
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Budget Name (Optional)</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-black focus:border-black"
                                placeholder="e.g., Operations Q1 2026">
                            <p class="mt-1 text-xs text-gray-500">Leave blank to use "Department - Fiscal Year"</p>
                        </div>

                        <!-- Currency -->
                        <div>
                            <label for="currency" class="block text-sm font-medium text-gray-700 mb-1">Currency *</label>
                            <select id="currency" name="currency" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-black focus:border-black">
                                <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                <option value="KES" {{ old('currency') == 'KES' ? 'selected' : '' }}>KES - Kenyan Shilling</option>
                                <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                            </select>
                        </div>

                        <!-- Start Date -->
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-black focus:border-black">
                        </div>

                        <!-- End Date -->
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-black focus:border-black">
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea id="description" name="description" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-black focus:border-black"
                                placeholder="Brief description of this budget period">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <!-- Step 1 Actions -->
                    <div class="mt-6 flex justify-end">
                        <button type="button" onclick="goToStep(2)" 
                            class="inline-flex items-center px-6 py-2 bg-black text-white text-sm font-medium rounded-md hover:bg-gray-800 transition">
                            Continue to Budget Lines
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 2: Budget Lines -->
            <div id="step2" class="step-content hidden">
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-black mb-4">Budget Lines</h2>
                    <p class="text-sm text-gray-500 mb-6">Upload a CSV file or add budget lines manually.</p>

                    <!-- CSV Upload -->
                    <div class="mb-6 p-4 border-2 border-dashed border-gray-200 rounded-lg">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <div class="mt-2">
                                <label for="budget_csv" class="cursor-pointer">
                                    <span class="text-sm font-medium text-black hover:underline">Upload CSV file</span>
                                    <input type="file" id="budget_csv" name="budget_csv" accept=".csv" class="hidden" onchange="handleCsvUpload(this)">
                                </label>
                                <p class="text-xs text-gray-500 mt-1">CSV format: code, name, category, allocated</p>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mb-6">
                        <span class="text-sm text-gray-400">— OR —</span>
                    </div>

                    <!-- Manual Entry -->
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-gray-700">Manual Entry</h3>
                            <button type="button" onclick="addBudgetLine()"
                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Add Line
                            </button>
                        </div>

                        <div id="budget-lines-container" class="space-y-3">
                        </div>

                        <!-- Totals -->
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700">Total Budget:</span>
                                <span id="total-budget" class="text-lg font-bold text-black">$0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2 Actions -->
                    <div class="mt-6 flex justify-between">
                        <button type="button" onclick="goToStep(1)"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Back
                        </button>
                        <button type="button" onclick="goToStep(3)"
                            class="inline-flex items-center px-6 py-2 bg-black text-white text-sm font-medium rounded-md hover:bg-gray-800 transition">
                            Review & Confirm
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 3: Review & Activate -->
            <div id="step3" class="step-content hidden">
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-black mb-4">Review & Confirm</h2>

                    <!-- Budget Summary -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Budget Summary</h3>
                        <dl class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt class="text-gray-500">Department</dt>
                                <dd id="review-department" class="font-medium text-black">-</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Fiscal Year</dt>
                                <dd id="review-fiscal-year" class="font-medium text-black">-</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Currency</dt>
                                <dd id="review-currency" class="font-medium text-black">-</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Period</dt>
                                <dd id="review-dates" class="font-medium text-black">-</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Budget Lines Summary -->
                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Budget Lines (<span id="review-line-count">0</span>)</h3>
                        <div id="review-budget-lines" class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Allocated</th>
                                    </tr>
                                </thead>
                                <tbody id="review-lines-tbody" class="bg-white divide-y divide-gray-200">
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="3" class="px-4 py-2 text-sm font-medium text-gray-700">Total</td>
                                        <td id="review-total" class="px-4 py-2 text-right text-sm font-bold text-black">$0.00</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Activation Options -->
                    <div class="mb-6 p-4 border border-gray-200 rounded-lg">
                        <label class="flex items-start">
                            <input type="checkbox" name="activate_now" value="1" checked
                                class="mt-0.5 h-4 w-4 text-black border-gray-300 rounded focus:ring-black">
                            <span class="ml-3">
                                <span class="text-sm font-medium text-black">Activate workspace immediately</span>
                                <span class="block text-xs text-gray-500">Create the budget in active status, ready for procurement</span>
                            </span>
                        </label>
                    </div>

                    <!-- Step 3 Actions -->
                    <div class="mt-6 flex justify-between">
                        <button type="button" onclick="goToStep(2)"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Back to Budget Lines
                        </button>
                        <div class="flex space-x-3">
                            <button type="submit" name="action" value="draft"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition">
                                Save as Draft
                            </button>
                            <button type="submit" name="action" value="activate"
                                class="inline-flex items-center px-6 py-2 bg-black text-white text-sm font-medium rounded-md hover:bg-gray-800 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Create & Activate Budget
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Budget Line Template -->
    <template id="budget-line-template">
        <div class="budget-line flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
            <div class="flex-1 grid grid-cols-4 gap-3">
                <div>
                    <input type="text" name="budget_lines[INDEX][code]" placeholder="Code"
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-black">
                </div>
                <div>
                    <input type="text" name="budget_lines[INDEX][name]" placeholder="Line item name"
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-black">
                </div>
                <div>
                    <select name="budget_lines[INDEX][category_id]"
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-black budget-category-select">
                        <option value="">Category</option>
                        @foreach(\App\Models\BudgetCategory::active()->ordered()->get() as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <input type="number" name="budget_lines[INDEX][allocated]" placeholder="Amount" step="0.01" min="0"
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-black budget-amount"
                        onchange="updateTotals()">
                </div>
            </div>
            <button type="button" onclick="removeBudgetLine(this)" class="p-1 text-gray-400 hover:text-red-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </template>

    @push('scripts')
    <script>
        let currentStep = 1;
        let budgetLineIndex = 0;

        function goToStep(step) {
            if (step > currentStep && !validateStep(currentStep)) {
                return;
            }

            document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
            document.getElementById('step' + step).classList.remove('hidden');
            
            for (let i = 1; i <= 3; i++) {
                const indicator = document.getElementById('step' + i + '-indicator');
                const label = indicator.nextElementSibling;
                
                if (i < step) {
                    indicator.className = 'flex items-center justify-center w-8 h-8 bg-black text-white rounded-full text-sm font-medium';
                    indicator.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                    label.className = 'ml-2 text-sm font-medium text-black';
                } else if (i === step) {
                    indicator.className = 'flex items-center justify-center w-8 h-8 bg-black text-white rounded-full text-sm font-medium';
                    indicator.innerHTML = i;
                    label.className = 'ml-2 text-sm font-medium text-black';
                } else {
                    indicator.className = 'flex items-center justify-center w-8 h-8 bg-gray-200 text-gray-500 rounded-full text-sm font-medium';
                    indicator.innerHTML = i;
                    label.className = 'ml-2 text-sm font-medium text-gray-500';
                }
            }

            if (step === 3) {
                updateReview();
            }

            currentStep = step;
        }

        function validateStep(step) {
            if (step === 1) {
                const required = ['department_id', 'fiscal_year', 'currency'];
                for (const field of required) {
                    const el = document.getElementById(field);
                    if (!el || !el.value) {
                        el.focus();
                        el.classList.add('border-red-500');
                        return false;
                    }
                    el.classList.remove('border-red-500');
                }
            }
            return true;
        }

        function addBudgetLine() {
            const template = document.getElementById('budget-line-template');
            const container = document.getElementById('budget-lines-container');
            const clone = template.content.cloneNode(true);
            
            clone.querySelectorAll('[name*="INDEX"]').forEach(el => {
                el.name = el.name.replace('INDEX', budgetLineIndex);
            });
            
            container.appendChild(clone);
            budgetLineIndex++;
            updateTotals();
        }

        function removeBudgetLine(btn) {
            btn.closest('.budget-line').remove();
            updateTotals();
        }

        function updateTotals() {
            let total = 0;
            document.querySelectorAll('.budget-amount').forEach(el => {
                total += parseFloat(el.value) || 0;
            });
            document.getElementById('total-budget').textContent = '$' + total.toLocaleString('en-US', {minimumFractionDigits: 2});
        }

        function handleCsvUpload(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const csv = e.target.result;
                    const lines = csv.split('\n');
                    
                    document.getElementById('budget-lines-container').innerHTML = '';
                    budgetLineIndex = 0;
                    
                    for (let i = 1; i < lines.length; i++) {
                        const cols = lines[i].split(',');
                        if (cols.length >= 4 && cols[0].trim()) {
                            addBudgetLine();
                            const idx = budgetLineIndex - 1;
                            document.querySelector(`[name="budget_lines[${idx}][code]"]`).value = cols[0].trim();
                            document.querySelector(`[name="budget_lines[${idx}][name]"]`).value = cols[1].trim();
                            document.querySelector(`[name="budget_lines[${idx}][allocated]"]`).value = parseFloat(cols[3].trim()) || 0;
                        }
                    }
                    updateTotals();
                };
                
                reader.readAsText(file);
            }
        }

        function updateReview() {
            const deptSelect = document.getElementById('department_id');
            document.getElementById('review-department').textContent = deptSelect.options[deptSelect.selectedIndex]?.text || '-';
            document.getElementById('review-fiscal-year').textContent = document.getElementById('fiscal_year').value || '-';
            document.getElementById('review-currency').textContent = document.getElementById('currency').value || '-';
            
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            document.getElementById('review-dates').textContent = startDate && endDate ? `${startDate} to ${endDate}` : 'Not specified';

            const tbody = document.getElementById('review-lines-tbody');
            tbody.innerHTML = '';
            let total = 0;
            let lineCount = 0;

            document.querySelectorAll('.budget-line').forEach((line, idx) => {
                const code = line.querySelector(`[name*="[code]"]`)?.value || '';
                const name = line.querySelector(`[name*="[name]"]`)?.value || '';
                const categorySelect = line.querySelector(`[name*="[category_id]"]`);
                const category = categorySelect?.options[categorySelect.selectedIndex]?.text || '';
                const allocated = parseFloat(line.querySelector(`[name*="[allocated]"]`)?.value) || 0;

                if (code || name) {
                    lineCount++;
                    total += allocated;
                    
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="px-4 py-2 text-sm text-gray-900">${code}</td>
                        <td class="px-4 py-2 text-sm text-gray-900">${name}</td>
                        <td class="px-4 py-2 text-sm text-gray-500">${category}</td>
                        <td class="px-4 py-2 text-sm text-gray-900 text-right">$${allocated.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                    `;
                    tbody.appendChild(tr);
                }
            });

            document.getElementById('review-line-count').textContent = lineCount;
            document.getElementById('review-total').textContent = '$' + total.toLocaleString('en-US', {minimumFractionDigits: 2});
        }

        document.addEventListener('DOMContentLoaded', function() {
            addBudgetLine();
        });
    </script>
    @endpush
</x-app-layout>
