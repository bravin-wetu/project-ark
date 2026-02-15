@section('title', $requisition->requisition_number . ' - ' . $project->name)

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
            <span class="text-ink-900 font-medium">{{ $requisition->requisition_number }}</span>
        </div>
        
        <div class="flex items-start justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-semibold text-ink-900">{{ $requisition->title }}</h1>
                    @php
                        $statusColors = [
                            'draft' => 'bg-smoke-100 text-smoke-700',
                            'pending_approval' => 'bg-amber-100 text-amber-700',
                            'approved' => 'bg-emerald-100 text-emerald-700',
                            'rejected' => 'bg-red-100 text-red-700',
                            'cancelled' => 'bg-smoke-100 text-smoke-700',
                            'in_progress' => 'bg-blue-100 text-blue-700',
                            'completed' => 'bg-emerald-100 text-emerald-700',
                        ];
                        $statusLabels = [
                            'draft' => 'Draft',
                            'pending_approval' => 'Pending Approval',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                            'cancelled' => 'Cancelled',
                            'in_progress' => 'In Progress',
                            'completed' => 'Completed',
                        ];
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$requisition->status] ?? 'bg-smoke-100 text-smoke-700' }}">
                        {{ $statusLabels[$requisition->status] ?? ucfirst($requisition->status) }}
                    </span>
                </div>
                <p class="text-smoke-600 mt-1 font-mono">{{ $requisition->requisition_number }}</p>
            </div>
            
            <div class="flex items-center gap-2">
                @if($requisition->canEdit())
                    <a href="{{ route('projects.requisitions.edit', [$project, $requisition]) }}" class="btn-secondary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                        Edit
                    </a>
                @endif
                
                @if($requisition->canSubmit())
                    <form action="{{ route('projects.requisitions.submit', [$project, $requisition]) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Submit for Approval
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Description -->
            @if($requisition->description || $requisition->justification)
                <div class="card p-6">
                    @if($requisition->description)
                        <div class="mb-4">
                            <h3 class="text-sm font-medium text-smoke-500 uppercase tracking-wide mb-2">Description</h3>
                            <p class="text-ink-700 whitespace-pre-wrap">{{ $requisition->description }}</p>
                        </div>
                    @endif
                    
                    @if($requisition->justification)
                        <div>
                            <h3 class="text-sm font-medium text-smoke-500 uppercase tracking-wide mb-2">Justification</h3>
                            <p class="text-ink-700 whitespace-pre-wrap">{{ $requisition->justification }}</p>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Line Items -->
            <div class="card">
                <div class="p-6 border-b border-smoke-100">
                    <h3 class="text-lg font-medium text-ink-900">Line Items ({{ $requisition->items->count() }})</h3>
                </div>
                
                <div class="divide-y divide-smoke-100">
                    @foreach($requisition->items as $item)
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-medium text-ink-900">{{ $item->name }}</h4>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-smoke-100 text-smoke-600">
                                            {{ ucfirst($item->item_type) }}
                                        </span>
                                    </div>
                                    @if($item->description)
                                        <p class="text-sm text-smoke-600 mt-1">{{ $item->description }}</p>
                                    @endif
                                    @if($item->specifications)
                                        <p class="text-xs text-smoke-500 mt-2 bg-smoke-50 p-2 rounded font-mono">{{ $item->specifications }}</p>
                                    @endif
                                </div>
                                <div class="text-right ml-4">
                                    <p class="text-lg font-semibold text-ink-900">${{ number_format($item->estimated_total, 2) }}</p>
                                    <p class="text-sm text-smoke-500">
                                        {{ number_format($item->quantity, 2) }} {{ $item->unit }} × ${{ number_format($item->estimated_unit_price, 2) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="p-6 bg-smoke-50 border-t border-smoke-200">
                    <div class="flex justify-end">
                        <div class="text-right">
                            <span class="text-sm text-smoke-600">Estimated Total:</span>
                            <span class="ml-2 text-2xl font-bold text-ink-900">${{ number_format($requisition->estimated_total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rejection Reason -->
            @if($requisition->status === 'rejected' && $requisition->rejection_reason)
                <div class="card p-6 bg-red-50 border-red-200">
                    <h3 class="text-sm font-medium text-red-700 uppercase tracking-wide mb-2">Rejection Reason</h3>
                    <p class="text-red-700">{{ $requisition->rejection_reason }}</p>
                    <p class="text-sm text-red-600 mt-2">
                        Rejected by {{ $requisition->rejecter->name ?? 'Unknown' }} on {{ $requisition->rejected_at?->format('M d, Y \a\t h:i A') }}
                    </p>
                </div>
            @endif

            <!-- Approval Actions -->
            @if($requisition->canApprove())
                <div class="card p-6">
                    <h3 class="text-lg font-medium text-ink-900 mb-4">Approval Actions</h3>
                    
                    <div class="p-4 bg-amber-50 rounded-xl border border-amber-200 mb-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-amber-800">Budget Impact</p>
                                <p class="text-sm text-amber-700 mt-1">
                                    Approving will commit <strong>${{ number_format($requisition->estimated_total, 2) }}</strong> 
                                    from budget line <strong>{{ $requisition->budgetLine->name }}</strong>.
                                    <br>Available: ${{ number_format($requisition->budgetLine->available, 2) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <form action="{{ route('projects.requisitions.approve', [$project, $requisition]) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn-primary bg-emerald-600 hover:bg-emerald-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Approve
                            </button>
                        </form>
                        
                        <button type="button" 
                                onclick="document.getElementById('rejectModal').classList.remove('hidden')"
                                class="btn-secondary text-red-600 border-red-300 hover:bg-red-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Reject
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Details -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-ink-900 mb-4">Details</h3>
                
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm text-smoke-500">Budget Line</dt>
                        <dd class="text-sm font-medium text-ink-900 mt-1">
                            {{ $requisition->budgetLine->code }} - {{ $requisition->budgetLine->name }}
                        </dd>
                        @if($requisition->budgetLine->category)
                            <dd class="text-xs text-smoke-500">{{ $requisition->budgetLine->category->name }}</dd>
                        @endif
                    </div>
                    
                    <div>
                        <dt class="text-sm text-smoke-500">Requested By</dt>
                        <dd class="text-sm font-medium text-ink-900 mt-1">{{ $requisition->requester->name ?? '—' }}</dd>
                        <dd class="text-xs text-smoke-500">{{ $requisition->requested_at?->format('M d, Y \a\t h:i A') }}</dd>
                    </div>
                    
                    @if($requisition->approved_at)
                        <div>
                            <dt class="text-sm text-smoke-500">Approved By</dt>
                            <dd class="text-sm font-medium text-ink-900 mt-1">{{ $requisition->approver->name ?? '—' }}</dd>
                            <dd class="text-xs text-smoke-500">{{ $requisition->approved_at?->format('M d, Y \a\t h:i A') }}</dd>
                        </div>
                    @endif
                    
                    <div>
                        <dt class="text-sm text-smoke-500">Priority</dt>
                        <dd class="mt-1">
                            @php
                                $priorityColors = [
                                    'low' => 'bg-smoke-100 text-smoke-700',
                                    'normal' => 'bg-blue-100 text-blue-700',
                                    'high' => 'bg-amber-100 text-amber-700',
                                    'urgent' => 'bg-red-100 text-red-700',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $priorityColors[$requisition->priority] ?? 'bg-smoke-100 text-smoke-700' }}">
                                {{ ucfirst($requisition->priority) }}
                            </span>
                        </dd>
                    </div>
                    
                    @if($requisition->required_date)
                        <div>
                            <dt class="text-sm text-smoke-500">Required By</dt>
                            <dd class="text-sm font-medium text-ink-900 mt-1">{{ $requisition->required_date->format('M d, Y') }}</dd>
                        </div>
                    @endif
                    
                    @if($requisition->deliveryHub)
                        <div>
                            <dt class="text-sm text-smoke-500">Delivery Location</dt>
                            <dd class="text-sm font-medium text-ink-900 mt-1">{{ $requisition->deliveryHub->name }}</dd>
                        </div>
                    @endif
                    
                    <div>
                        <dt class="text-sm text-smoke-500">Currency</dt>
                        <dd class="text-sm font-medium text-ink-900 mt-1">{{ $requisition->currency }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Notes -->
            @if($requisition->notes)
                <div class="card p-6">
                    <h3 class="text-lg font-medium text-ink-900 mb-4">Notes</h3>
                    <p class="text-sm text-smoke-600 whitespace-pre-wrap">{{ $requisition->notes }}</p>
                </div>
            @endif

            <!-- Actions -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-ink-900 mb-4">Actions</h3>
                
                <div class="space-y-2">
                    @if($requisition->canEdit())
                        <a href="{{ route('projects.requisitions.edit', [$project, $requisition]) }}" 
                           class="btn-secondary w-full justify-center">
                            Edit Requisition
                        </a>
                    @endif
                    
                    @if(!in_array($requisition->status, ['completed', 'cancelled']))
                        <form action="{{ route('projects.requisitions.cancel', [$project, $requisition]) }}" 
                              method="POST"
                              onsubmit="return confirm('Are you sure you want to cancel this requisition?')">
                            @csrf
                            <button type="submit" class="btn-ghost w-full justify-center text-red-600 hover:bg-red-50">
                                Cancel Requisition
                            </button>
                        </form>
                    @endif
                    
                    @if(in_array($requisition->status, ['draft', 'cancelled']))
                        <form action="{{ route('projects.requisitions.destroy', [$project, $requisition]) }}" 
                              method="POST"
                              onsubmit="return confirm('Are you sure you want to delete this requisition? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-ghost w-full justify-center text-red-600 hover:bg-red-50">
                                Delete Requisition
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
            <div class="fixed inset-0 bg-ink-900/50 transition-opacity" onclick="document.getElementById('rejectModal').classList.add('hidden')"></div>
            
            <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl">
                <h3 class="text-lg font-medium text-ink-900 mb-4">Reject Requisition</h3>
                
                <form action="{{ route('projects.requisitions.reject', [$project, $requisition]) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="rejection_reason" class="block text-sm font-medium text-ink-700 mb-1">
                            Reason for Rejection <span class="text-red-500">*</span>
                        </label>
                        <textarea name="rejection_reason" 
                                  id="rejection_reason" 
                                  rows="4"
                                  class="input-field"
                                  placeholder="Please explain why this requisition is being rejected..."
                                  required
                                  minlength="10"></textarea>
                        <p class="mt-1 text-xs text-smoke-500">Minimum 10 characters</p>
                    </div>
                    
                    <div class="flex justify-end gap-3">
                        <button type="button" 
                                onclick="document.getElementById('rejectModal').classList.add('hidden')"
                                class="btn-secondary">
                            Cancel
                        </button>
                        <button type="submit" class="btn-primary bg-red-600 hover:bg-red-700">
                            Reject Requisition
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-workspace-layout>
