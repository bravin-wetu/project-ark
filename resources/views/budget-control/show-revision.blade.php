<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Budget Revision Details
                </h2>
                <p class="text-sm text-gray-500 mt-1">{{ $revision->reference_number }}</p>
            </div>
            <a href="{{ route('budget-control.pending-revisions') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                &larr; Back to Pending Revisions
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <!-- Status Banner -->
                <div class="px-6 py-4 
                    @if($revision->status === 'approved') bg-green-50 border-b border-green-200
                    @elseif($revision->status === 'rejected') bg-red-50 border-b border-red-200
                    @else bg-yellow-50 border-b border-yellow-200
                    @endif">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            @if($revision->status === 'pending')
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-yellow-800 font-medium">Pending Approval</span>
                            @elseif($revision->status === 'approved')
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-green-800 font-medium">Approved</span>
                            @else
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span class="text-red-800 font-medium">Rejected</span>
                            @endif
                        </div>
                        <span class="px-3 py-1 text-sm font-medium rounded-full 
                            @if($revision->revision_type === 'reallocation') bg-purple-100 text-purple-800
                            @elseif($revision->revision_type === 'correction') bg-orange-100 text-orange-800
                            @else bg-blue-100 text-blue-800
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $revision->revision_type)) }}
                        </span>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Budget Line Info -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">{{ $revision->budgetLine->name }}</h3>
                        <p class="text-sm text-gray-500">Code: {{ $revision->budgetLine->code }}</p>
                        <p class="mt-1 text-sm text-gray-500">
                            @if($revision->budgetLine->budgetable_type === 'App\\Models\\Project')
                                Project: {{ $revision->budgetLine->budgetable->name ?? 'N/A' }}
                            @else
                                Department Budget: {{ $revision->budgetLine->budgetable->display_name ?? 'N/A' }}
                            @endif
                        </p>
                    </div>

                    <!-- Financial Details -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm font-medium text-gray-500">Previous Allocation</p>
                            <p class="mt-1 text-2xl font-semibold text-gray-900">
                                ${{ number_format($revision->previous_allocated, 2) }}
                            </p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm font-medium text-gray-500">New Allocation</p>
                            <p class="mt-1 text-2xl font-semibold text-gray-900">
                                ${{ number_format($revision->new_allocated, 2) }}
                            </p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm font-medium text-gray-500">Change Amount</p>
                            <p class="mt-1 text-2xl font-semibold {{ $revision->change_amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $revision->formatted_change }}
                            </p>
                            <p class="text-sm {{ $revision->change_amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                ({{ $revision->change_percentage }}%)
                            </p>
                        </div>
                    </div>

                    <!-- Visual Change Bar -->
                    <div class="mb-6">
                        <div class="h-4 bg-gray-200 rounded-full overflow-hidden">
                            @php
                                $maxValue = max($revision->previous_allocated, $revision->new_allocated);
                                $prevPercent = $maxValue > 0 ? ($revision->previous_allocated / $maxValue) * 100 : 0;
                                $newPercent = $maxValue > 0 ? ($revision->new_allocated / $maxValue) * 100 : 0;
                            @endphp
                            <div class="flex h-full">
                                <div class="bg-gray-400 h-full" style="width: {{ $prevPercent }}%"></div>
                            </div>
                        </div>
                        <div class="h-4 bg-gray-200 rounded-full overflow-hidden mt-1">
                            <div class="bg-indigo-500 h-full" style="width: {{ $newPercent }}%"></div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500 mt-1">
                            <span>Previous</span>
                            <span>New</span>
                        </div>
                    </div>

                    <!-- Reason -->
                    @if($revision->reason)
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Reason for Change</h4>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-gray-600">{{ $revision->reason }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Rejection Reason -->
                    @if($revision->rejection_reason)
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-red-700 mb-2">Rejection Reason</h4>
                            <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                                <p class="text-red-600">{{ $revision->rejection_reason }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Timeline -->
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-sm font-medium text-gray-700 mb-4">Timeline</h4>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100">
                                        <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </span>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">Revision Requested</p>
                                    <p class="text-sm text-gray-500">
                                        By {{ $revision->user->name }} on {{ $revision->created_at->format('M d, Y \a\t H:i') }}
                                    </p>
                                </div>
                            </div>

                            @if($revision->approved_at)
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-full 
                                            {{ $revision->isApproved() ? 'bg-green-100' : 'bg-red-100' }}">
                                            @if($revision->isApproved())
                                                <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @else
                                                <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $revision->isApproved() ? 'Approved' : 'Rejected' }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            By {{ $revision->approver->name ?? 'System' }} on {{ $revision->approved_at->format('M d, Y \a\t H:i') }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    @if($revision->isPending())
                        <div class="border-t border-gray-200 pt-6 mt-6">
                            <div class="flex items-center space-x-3">
                                <form action="{{ route('budget-control.approve-revision', $revision) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Approve Revision
                                    </button>
                                </form>

                                <button type="button" 
                                        onclick="document.getElementById('rejectSection').classList.toggle('hidden')"
                                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Reject Revision
                                </button>
                            </div>

                            <div id="rejectSection" class="hidden mt-4">
                                <form action="{{ route('budget-control.reject-revision', $revision) }}" method="POST">
                                    @csrf
                                    <div class="mb-4">
                                        <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Rejection Reason *</label>
                                        <textarea name="rejection_reason" id="rejection_reason" rows="3" required
                                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                                  placeholder="Please provide a reason for rejection..."></textarea>
                                    </div>
                                    <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700">
                                        Confirm Rejection
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
