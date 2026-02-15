@section('title', 'Department Budgets')

<x-app-layout>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-black">Department Budgets</h1>
            <p class="text-gray-500">Manage internal operational budgets</p>
        </div>
        <a href="{{ route('department-budgets.create') }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create Department Budget
        </a>
    </div>

    <!-- Filters -->
    <div class="mb-6 flex items-center space-x-4">
        <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-500">Status:</span>
            <a href="{{ route('department-budgets.index') }}" 
               class="px-3 py-1 text-sm rounded-full {{ !request('status') ? 'bg-black text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                All
            </a>
            <a href="{{ route('department-budgets.index', ['status' => 'active']) }}" 
               class="px-3 py-1 text-sm rounded-full {{ request('status') === 'active' ? 'bg-black text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Active
            </a>
            <a href="{{ route('department-budgets.index', ['status' => 'draft']) }}" 
               class="px-3 py-1 text-sm rounded-full {{ request('status') === 'draft' ? 'bg-black text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Draft
            </a>
            <a href="{{ route('department-budgets.index', ['status' => 'closed']) }}" 
               class="px-3 py-1 text-sm rounded-full {{ request('status') === 'closed' ? 'bg-black text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Closed
            </a>
        </div>
    </div>

    <!-- Budgets Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($budgets as $budget)
        <a href="{{ route('department-budgets.show', $budget) }}" 
           class="block bg-white rounded-lg border border-gray-200 p-5 hover:border-gray-300 hover:shadow-sm transition">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h3 class="font-semibold text-black">{{ $budget->department->name ?? 'Unknown' }}</h3>
                    <p class="text-sm text-gray-500">{{ $budget->fiscal_year }}</p>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $budget->status === 'active' ? 'bg-black text-white' : '' }}
                    {{ $budget->status === 'draft' ? 'bg-gray-100 text-gray-700' : '' }}
                    {{ $budget->status === 'closed' ? 'bg-gray-200 text-gray-600' : '' }}">
                    {{ ucfirst($budget->status) }}
                </span>
            </div>

            @if($budget->name)
            <p class="mt-2 text-sm text-gray-600">{{ $budget->name }}</p>
            @endif

            <div class="mt-4">
                <div class="flex items-center justify-between text-sm mb-1">
                    <span class="text-gray-500">Budget Utilization</span>
                    <span class="font-medium text-black">{{ $budget->utilization }}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="bg-black rounded-full h-2 transition-all" style="width: {{ min($budget->utilization, 100) }}%"></div>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between text-sm">
                <span class="text-gray-600">
                    {{ $budget->currency }} {{ number_format($budget->spent, 0) }} / {{ number_format($budget->allocated, 0) }}
                </span>
                <span class="text-gray-500">
                    {{ $budget->budgetLines->count() }} budget lines
                </span>
            </div>
        </a>
        @empty
        <div class="col-span-3 bg-white rounded-lg border border-gray-200 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No department budgets yet</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by creating a new department budget.</p>
            <div class="mt-6">
                <a href="{{ route('department-budgets.create') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create Department Budget
                </a>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($budgets->hasPages())
    <div class="mt-6">
        {{ $budgets->links() }}
    </div>
    @endif
</x-app-layout>
