@section('title', 'Register Asset - ' . $project->name)

<x-workspace-layout :workspace="$project" :workspaceType="'projects'">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
            <a href="{{ route('projects.show', $project) }}" class="hover:text-ink-900 transition-colors">{{ $project->name }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('projects.assets.index', $project) }}" class="hover:text-ink-900 transition-colors">Assets</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">Register Asset</span>
        </div>
        <h1 class="text-2xl font-semibold text-ink-900">Register New Asset</h1>
        <p class="text-smoke-600 mt-1">Manually register a new asset for this project.</p>
    </div>

    <form action="{{ route('projects.assets.store', $project) }}" method="POST" class="max-w-4xl">
        @csrf

        <div class="space-y-6">
            <!-- Basic Information -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-ink-900 mb-4">Basic Information</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label for="name" class="block text-sm font-medium text-ink-700 mb-1">Asset Name *</label>
                        <input type="text" name="name" id="name" required value="{{ old('name') }}"
                               class="form-input w-full" placeholder="e.g., Dell Laptop XPS 15">
                        @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="category" class="block text-sm font-medium text-ink-700 mb-1">Category *</label>
                        <select name="category" id="category" required class="form-select w-full">
                            <option value="">Select category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                        @error('category') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="subcategory" class="block text-sm font-medium text-ink-700 mb-1">Subcategory</label>
                        <input type="text" name="subcategory" id="subcategory" value="{{ old('subcategory') }}"
                               class="form-input w-full" placeholder="e.g., Laptop">
                    </div>

                    <div class="col-span-2">
                        <label for="description" class="block text-sm font-medium text-ink-700 mb-1">Description</label>
                        <textarea name="description" id="description" rows="2" class="form-textarea w-full">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Identification -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-ink-900 mb-4">Identification</h3>
                
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label for="serial_number" class="block text-sm font-medium text-ink-700 mb-1">Serial Number</label>
                        <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number') }}"
                               class="form-input w-full font-mono">
                    </div>

                    <div>
                        <label for="model" class="block text-sm font-medium text-ink-700 mb-1">Model</label>
                        <input type="text" name="model" id="model" value="{{ old('model') }}"
                               class="form-input w-full">
                    </div>

                    <div>
                        <label for="manufacturer" class="block text-sm font-medium text-ink-700 mb-1">Manufacturer</label>
                        <input type="text" name="manufacturer" id="manufacturer" value="{{ old('manufacturer') }}"
                               class="form-input w-full">
                    </div>
                </div>
            </div>

            <!-- Acquisition & Value -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-ink-900 mb-4">Acquisition & Value</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="acquisition_cost" class="block text-sm font-medium text-ink-700 mb-1">Acquisition Cost</label>
                        <input type="number" name="acquisition_cost" id="acquisition_cost" step="0.01" min="0"
                               value="{{ old('acquisition_cost') }}" class="form-input w-full">
                    </div>

                    <div>
                        <label for="acquisition_date" class="block text-sm font-medium text-ink-700 mb-1">Acquisition Date</label>
                        <input type="date" name="acquisition_date" id="acquisition_date"
                               value="{{ old('acquisition_date', date('Y-m-d')) }}" class="form-input w-full">
                    </div>
                </div>
            </div>

            <!-- Location & Assignment -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-ink-900 mb-4">Location & Assignment</h3>
                
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label for="hub_id" class="block text-sm font-medium text-ink-700 mb-1">Hub *</label>
                        <select name="hub_id" id="hub_id" required class="form-select w-full">
                            <option value="">Select hub</option>
                            @foreach($hubs as $hub)
                                <option value="{{ $hub->id }}" {{ old('hub_id') == $hub->id ? 'selected' : '' }}>{{ $hub->name }}</option>
                            @endforeach
                        </select>
                        @error('hub_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="assigned_to" class="block text-sm font-medium text-ink-700 mb-1">Assigned To</label>
                        <select name="assigned_to" id="assigned_to" class="form-select w-full">
                            <option value="">Not assigned</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="location" class="block text-sm font-medium text-ink-700 mb-1">Physical Location</label>
                        <input type="text" name="location" id="location" value="{{ old('location') }}"
                               class="form-input w-full" placeholder="e.g., Office 201, Shelf B">
                    </div>
                </div>
            </div>

            <!-- Condition & Warranty -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-ink-900 mb-4">Condition & Warranty</h3>
                
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label for="condition" class="block text-sm font-medium text-ink-700 mb-1">Condition *</label>
                        <select name="condition" id="condition" required class="form-select w-full">
                            @foreach($conditions as $key => $label)
                                <option value="{{ $key }}" {{ old('condition', 'new') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="warranty_expiry" class="block text-sm font-medium text-ink-700 mb-1">Warranty Expiry</label>
                        <input type="date" name="warranty_expiry" id="warranty_expiry"
                               value="{{ old('warranty_expiry') }}" class="form-input w-full">
                    </div>

                    <div>
                        <label for="warranty_notes" class="block text-sm font-medium text-ink-700 mb-1">Warranty Notes</label>
                        <input type="text" name="warranty_notes" id="warranty_notes" value="{{ old('warranty_notes') }}"
                               class="form-input w-full" placeholder="e.g., 2-year manufacturer warranty">
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-ink-900 mb-4">Additional Notes</h3>
                <textarea name="notes" id="notes" rows="3" class="form-textarea w-full">{{ old('notes') }}</textarea>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between">
                <a href="{{ route('projects.assets.index', $project) }}" class="text-smoke-600 hover:text-ink-900">
                    ← Cancel
                </a>
                <button type="submit" class="btn-primary">
                    Register Asset
                </button>
            </div>
        </div>
    </form>
</x-workspace-layout>
