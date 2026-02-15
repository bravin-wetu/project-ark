<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Pending Budget Revisions
            </h2>
            <span class="px-3 py-1 text-sm font-medium rounded-full bg-yellow-100 text-yellow-800">
                {{ $revisions->count() }} Pending
            </span>
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

            @if($revisions->isEmpty())
                <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No Pending Revisions</h3>
                    <p class="mt-1 text-sm text-gray-500">All budget revisions have been processed.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($revisions as $revision)
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                            <div class="p-6">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3">
                                            <span class="text-sm font-mono text-gray-500">{{ $revision->reference_number }}</span>
                                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                                @if($revision->revision_type === 'reallocation') bg-purple-100 text-purple-800
                                                @elseif($revision->revision_type === 'correction') bg-orange-100 text-orange-800
                                                @else bg-blue-100 text-blue-800
                                                @endif">
                                                {{ ucfirst(str_replace('_', ' ', $revision->revision_type)) }}
                                            </span>
                                            <span class="text-sm text-gray-500">{{ $revision->created_at->diffForHumans() }}</span>
                                        </div>
                                        
                                        <h3 class="mt-2 text-lg font-medium text-gray-900">
                                            {{ $revision->budgetLine->name }}
                                        </h3>
                                        
                                        <p class="mt-1 text-sm text-gray-500">
                                            @if($revision->budgetLine->budgetable_type === 'App\\Models\\Project')
                                                Project: {{ $revision->budgetLine->budgetable->name ?? 'N/A' }}
                                            @else
                                                Department Budget: {{ $revision->budgetLine->budgetable->display_name ?? 'N/A' }}
                                            @endif
                                        </p>

                                        <div class="mt-4 grid grid-cols-3 gap-4 text-sm">
                                            <div>
                                                <span class="text-gray-500">Previous Amount:</span>
                                                <span class="ml-2 font-medium">${{ number_format($revision->previous_allocated, 2) }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">New Amount:</span>
                                                <span class="ml-2 font-medium">${{ number_format($revision->new_allocated, 2) }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Change:</span>
                                                <span class="ml-2 font-medium {{ $revision->change_amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $revision->formatted_change }} ({{ $revision->change_percentage }}%)
                                                </span>
                                            </div>
                                        </div>

                                        @if($revision->reason)
                                            <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                                                <span class="text-sm font-medium text-gray-700">Reason:</span>
                                                <p class="mt-1 text-sm text-gray-600">{{ $revision->reason }}</p>
                                            </div>
                                        @endif

                                        <p class="mt-3 text-sm text-gray-500">
                                            Requested by: <span class="font-medium">{{ $revision->user->name }}</span>
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-6 flex items-center space-x-3">
                                    <form action="{{ route('budget-control.approve-revision', $revision) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Approve
                                        </button>
                                    </form>

                                    <button type="button" 
                                            onclick="showRejectModal({{ $revision->id }})"
                                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Reject
                                    </button>

                                    <a href="{{ route('budget-control.show-revision', $revision) }}" 
                                       class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Budget Revision</h3>
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Rejection Reason *</label>
                        <textarea name="rejection_reason" id="rejection_reason" rows="3" required
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                  placeholder="Please provide a reason for rejection..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideRejectModal()"
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showRejectModal(revisionId) {
            const modal = document.getElementById('rejectModal');
            const form = document.getElementById('rejectForm');
            form.action = `/budget-control/revisions/${revisionId}/reject`;
            modal.classList.remove('hidden');
        }

        function hideRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
            document.getElementById('rejection_reason').value = '';
        }
    </script>
</x-app-layout>
