<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Budget Control - {{ $departmentBudget->display_name }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Manage budget locks, thresholds, and revisions</p>
            </div>
            <a href="{{ route('department-budgets.show', $departmentBudget) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                &larr; Back to Department Budget
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Control Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <!-- Lock Status -->
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Lock Status</p>
                            <p class="mt-1 text-lg font-semibold {{ $summary['is_locked'] ? 'text-red-600' : 'text-green-600' }}">
                                {{ $summary['is_locked'] ? 'Locked' : 'Unlocked' }}
                            </p>
                            @if($summary['is_locked'])
                                <p class="text-xs text-gray-500">{{ ucfirst($summary['lock_type']) }} lock</p>
                            @endif
                        </div>
                        @if($summary['is_locked'])
                            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        @else
                            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                            </svg>
                        @endif
                    </div>
                </div>

                <!-- Utilization -->
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Budget Utilization</p>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $summary['utilization'] }}%</p>
                            <p class="text-xs {{ App\Models\BudgetThreshold::getLevelClass($summary['threshold_level']) }} px-2 py-0.5 rounded-full inline-block mt-1">
                                {{ ucfirst($summary['threshold_level']) }}
                            </p>
                        </div>
                        <div class="w-16 h-16">
                            <svg viewBox="0 0 36 36" class="w-full h-full">
                                <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                      fill="none" stroke="#e5e7eb" stroke-width="3"/>
                                <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                      fill="none" 
                                      stroke="{{ $summary['utilization'] >= 100 ? '#ef4444' : ($summary['utilization'] >= 80 ? '#f59e0b' : '#22c55e') }}"
                                      stroke-width="3"
                                      stroke-dasharray="{{ min($summary['utilization'], 100) }}, 100"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Pending Revisions -->
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Pending Revisions</p>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $summary['pending_revisions_count'] }}</p>
                        </div>
                        <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>

                <!-- Block Status -->
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Spending Status</p>
                            <p class="mt-1 text-lg font-semibold {{ $summary['is_spending_blocked'] ? 'text-red-600' : 'text-green-600' }}">
                                {{ $summary['is_spending_blocked'] ? 'Blocked' : 'Allowed' }}
                            </p>
                        </div>
                        @if($summary['is_spending_blocked'])
                            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                        @else
                            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Lock Management -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Budget Lock Management</h3>
                    </div>
                    <div class="p-6">
                        @if($summary['is_locked'])
                            <div class="bg-red-50 rounded-lg p-4 mb-4">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-red-800">Budget is currently locked</p>
                                        <p class="mt-1 text-sm text-red-700">
                                            <strong>Type:</strong> {{ ucfirst($summary['lock_type']) }} lock<br>
                                            @if($summary['lock_reason'])
                                                <strong>Reason:</strong> {{ $summary['lock_reason'] }}<br>
                                            @endif
                                            <strong>Locked by:</strong> {{ $summary['locked_by'] }}<br>
                                            <strong>Locked at:</strong> {{ $summary['locked_at']->format('M d, Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <form action="{{ route('budget-control.unlock-department', $departmentBudget) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                    </svg>
                                    Unlock Budget
                                </button>
                            </form>
                        @else
                            <form action="{{ route('budget-control.lock-department', $departmentBudget) }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label for="lock_type" class="block text-sm font-medium text-gray-700">Lock Type</label>
                                    <select name="lock_type" id="lock_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="soft">Soft Lock (changes require approval)</option>
                                        <option value="hard">Hard Lock (no changes allowed)</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label for="reason" class="block text-sm font-medium text-gray-700">Reason (optional)</label>
                                    <textarea name="reason" id="reason" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Why is this budget being locked?"></textarea>
                                </div>
                                <div class="mb-4">
                                    <label for="lock_until" class="block text-sm font-medium text-gray-700">Lock Until (optional)</label>
                                    <input type="date" name="lock_until" id="lock_until" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Lock Budget
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Threshold Settings -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Alert Thresholds</h3>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('budget-control.department-thresholds', $departmentBudget) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="grid grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label for="warning_percentage" class="block text-sm font-medium text-gray-700">Warning (%)</label>
                                    <input type="number" name="warning_percentage" id="warning_percentage" 
                                           value="{{ $summary['warning_percentage'] }}" min="0" max="100" step="0.1"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="critical_percentage" class="block text-sm font-medium text-gray-700">Critical (%)</label>
                                    <input type="number" name="critical_percentage" id="critical_percentage" 
                                           value="{{ $summary['critical_percentage'] }}" min="0" max="100" step="0.1"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="block_percentage" class="block text-sm font-medium text-gray-700">Block (%)</label>
                                    <input type="number" name="block_percentage" id="block_percentage" 
                                           value="{{ $summary['block_percentage'] }}" min="0" max="150" step="0.1"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                            </div>

                            <div class="space-y-3 mb-4">
                                <label class="flex items-center">
                                    <input type="checkbox" name="send_warning_alert" value="1" checked
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700">Send warning alerts</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="send_critical_alert" value="1" checked
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700">Send critical alerts</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="block_on_exceed" value="1"
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700">Block spending when threshold exceeded</span>
                                </label>
                            </div>

                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                                Save Threshold Settings
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Recent Revisions -->
            <div class="mt-6 bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Recent Budget Revisions</h3>
                    <a href="{{ route('budget-control.pending-revisions') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                        View All Pending
                    </a>
                </div>
                <div class="divide-y divide-gray-200">
                    @forelse($revisions as $revision)
                        <div class="px-6 py-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $revision->budgetLine->name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $revision->reference_number }} &middot; 
                                    {{ ucfirst(str_replace('_', ' ', $revision->revision_type)) }} &middot;
                                    {{ $revision->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <div class="flex items-center space-x-4">
                                <span class="{{ $revision->change_amount >= 0 ? 'text-green-600' : 'text-red-600' }} text-sm font-medium">
                                    {{ $revision->formatted_change }}
                                </span>
                                <span class="px-2 py-1 text-xs font-medium rounded-full 
                                    @if($revision->status === 'approved') bg-green-100 text-green-800
                                    @elseif($revision->status === 'rejected') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ ucfirst($revision->status) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-gray-500">
                            No budget revisions yet
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Lock History -->
            <div class="mt-6 bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Lock History</h3>
                </div>
                <div class="divide-y divide-gray-200">
                    @forelse($locks as $lock)
                        <div class="px-6 py-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ ucfirst($lock->lock_type) }} Lock by {{ $lock->locker->name }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $lock->locked_at->format('M d, Y H:i') }}
                                    @if($lock->reason)
                                        &middot; {{ Str::limit($lock->reason, 50) }}
                                    @endif
                                </p>
                            </div>
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                {{ $lock->is_active ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $lock->is_active ? 'Active' : 'Released' }}
                            </span>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-gray-500">
                            No lock history
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
