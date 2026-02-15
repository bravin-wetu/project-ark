@section('title', 'Transfer Asset - ' . $project->name)

<x-workspace-layout :workspace="$project" :workspaceType="'projects'">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
            <a href="{{ route('projects.assets.show', [$project, $asset]) }}" class="hover:text-ink-900 transition-colors">{{ $asset->asset_tag }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">Transfer</span>
        </div>
        <h1 class="text-2xl font-semibold text-ink-900">Transfer Asset</h1>
        <p class="text-smoke-600 mt-1">{{ $asset->name }}</p>
    </div>

    <form action="{{ route('projects.assets.transfer', [$project, $asset]) }}" method="POST" class="max-w-2xl">
        @csrf

        <div class="space-y-6">
            <!-- Current Location -->
            <div class="card p-6 bg-smoke-50">
                <h3 class="text-sm font-medium text-smoke-500 uppercase tracking-wider mb-3">Current Location</h3>
                <dl class="grid grid-cols-3 gap-4">
                    <div>
                        <dt class="text-sm text-smoke-500">Hub</dt>
                        <dd class="text-ink-900 font-medium">{{ $asset->hub?->name ?? 'Not assigned' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-smoke-500">Assigned To</dt>
                        <dd class="text-ink-900">{{ $asset->assignedUser?->name ?? 'Not assigned' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-smoke-500">Location</dt>
                        <dd class="text-ink-900">{{ $asset->location ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- New Location -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-ink-900 mb-4">Transfer To</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="to_hub_id" class="block text-sm font-medium text-ink-700 mb-1">Destination Hub</label>
                        <select name="to_hub_id" id="to_hub_id" class="form-select w-full">
                            <option value="">Same hub</option>
                            @foreach($hubs as $hub)
                                <option value="{{ $hub->id }}" {{ old('to_hub_id') == $hub->id ? 'selected' : '' }}>{{ $hub->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="to_user_id" class="block text-sm font-medium text-ink-700 mb-1">Assign To</label>
                        <select name="to_user_id" id="to_user_id" class="form-select w-full">
                            <option value="">Not assigned</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('to_user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-span-2">
                        <label for="to_location" class="block text-sm font-medium text-ink-700 mb-1">Physical Location</label>
                        <input type="text" name="to_location" id="to_location" value="{{ old('to_location') }}"
                               class="form-input w-full" placeholder="e.g., Office 201, Shelf B">
                    </div>
                </div>
            </div>

            <!-- Transfer Details -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-ink-900 mb-4">Transfer Details</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="transfer_date" class="block text-sm font-medium text-ink-700 mb-1">Transfer Date *</label>
                        <input type="date" name="transfer_date" id="transfer_date" required
                               value="{{ old('transfer_date', date('Y-m-d')) }}" class="form-input w-full">
                    </div>

                    <div>
                        <label for="expected_arrival" class="block text-sm font-medium text-ink-700 mb-1">Expected Arrival</label>
                        <input type="date" name="expected_arrival" id="expected_arrival"
                               value="{{ old('expected_arrival') }}" class="form-input w-full">
                    </div>

                    <div>
                        <label for="condition_on_transfer" class="block text-sm font-medium text-ink-700 mb-1">Condition *</label>
                        <select name="condition_on_transfer" id="condition_on_transfer" required class="form-select w-full">
                            @foreach($conditions as $cond)
                                <option value="{{ $cond }}" {{ old('condition_on_transfer', $asset->condition) == $cond ? 'selected' : '' }}>
                                    {{ ucfirst($cond) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-span-2">
                        <label for="reason" class="block text-sm font-medium text-ink-700 mb-1">Reason for Transfer</label>
                        <textarea name="reason" id="reason" rows="2" class="form-textarea w-full">{{ old('reason') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between">
                <a href="{{ route('projects.assets.show', [$project, $asset]) }}" class="text-smoke-600 hover:text-ink-900">
                    ← Cancel
                </a>
                <button type="submit" class="btn-primary">
                    Initiate Transfer
                </button>
            </div>
        </div>
    </form>
</x-workspace-layout>
