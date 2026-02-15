@section('title', $budget->display_name)

<x-workspace-layout :workspace="$budget" :workspaceType="'department-budgets'">
    <!-- Budget Header -->
    <div class="mb-6">
        <div class="text-sm text-gray-500 mb-1">
            Department Budgets &gt; {{ $budget->department->name ?? 'Unknown' }}
        </div>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-black">{{ $budget->department->name ?? 'Unknown Department' }}</h1>
                <p class="text-gray-500">{{ $budget->fiscal_year }} &middot; Internal Budget</p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-600">Budget:</span>
                <span class="font-semibold">{{ $budget->currency }} {{ number_format($budget->spent) }} / {{ number_format($budget->allocated) }}</span>
                <div class="w-24 bg-gray-100 rounded-full h-2">
                    <div class="bg-black rounded-full h-2" style="width: {{ min($budget->utilization, 100) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards Row 1 -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Allocated</p>
            <p class="mt-2 text-2xl font-semibold text-black">{{ $budget->currency }} {{ number_format($budget->allocated) }}</p>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Spent</p>
            <p class="mt-2 text-2xl font-semibold text-black">{{ $budget->currency }} {{ number_format($budget->spent) }}</p>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Remaining</p>
            <p class="mt-2 text-2xl font-semibold text-black">{{ $budget->currency }} {{ number_format($budget->remaining) }}</p>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Utilization</p>
            <p class="mt-2 text-2xl font-semibold text-black">{{ $budget->utilization }}%</p>
        </div>
    </div>

    <!-- Stats Cards Row 2 -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <div class="flex items-center">
                <div class="p-2 bg-gray-100 rounded-lg mr-3">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-semibold text-black">{{ $stats['requisitions'] }}</p>
                    <p class="text-sm text-gray-500">Requisitions</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <div class="flex items-center">
                <div class="p-2 bg-gray-100 rounded-lg mr-3">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-semibold text-black">{{ $stats['rfqs'] }}</p>
                    <p class="text-sm text-gray-500">RFQs</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <div class="flex items-center">
                <div class="p-2 bg-gray-100 rounded-lg mr-3">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-semibold text-black">{{ $stats['purchase_orders'] }}</p>
                    <p class="text-sm text-gray-500">Purchase Orders</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <div class="flex items-center">
                <div class="p-2 bg-gray-100 rounded-lg mr-3">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-semibold text-black">{{ $stats['receipts'] }}</p>
                    <p class="text-sm text-gray-500">Receipts</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Budget Lines -->
    <div class="bg-white rounded-lg border border-gray-200 mb-6">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="font-semibold text-black">Budget Lines</h2>
            <span class="text-sm text-gray-500">{{ $budget->budgetLines->count() }} lines</span>
        </div>

        @if($budget->budgetLines->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Allocated</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Spent</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilization</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($budget->budgetLines as $line)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-4 text-sm font-medium text-gray-900">{{ $line->code }}</td>
                        <td class="px-5 py-4 text-sm text-gray-600">{{ $line->name }}</td>
                        <td class="px-5 py-4 text-sm text-gray-500">{{ $line->category->name ?? '-' }}</td>
                        <td class="px-5 py-4 text-sm text-gray-900 text-right">{{ number_format($line->allocated, 2) }}</td>
                        <td class="px-5 py-4 text-sm text-gray-900 text-right">{{ number_format($line->spent, 2) }}</td>
                        <td class="px-5 py-4 text-sm text-gray-900 text-right">{{ number_format($line->allocated - $line->spent, 2) }}</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center">
                                <div class="w-16 bg-gray-100 rounded-full h-2 mr-2">
                                    @php $util = $line->allocated > 0 ? round(($line->spent / $line->allocated) * 100) : 0; @endphp
                                    <div class="bg-black rounded-full h-2" style="width: {{ min($util, 100) }}%"></div>
                                </div>
                                <span class="text-sm text-gray-600">{{ $util }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-5 py-12 text-center">
            <p class="text-sm text-gray-500">No budget lines defined.</p>
        </div>
        @endif
    </div>

    <!-- Recent Requisitions -->
    <div class="bg-white rounded-lg border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-200">
            <h2 class="font-semibold text-black">Recent Requisitions</h2>
        </div>

        @if($recentRequisitions->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request ID</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Budget Line</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($recentRequisitions as $requisition)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-4 text-sm text-gray-900">{{ $requisition->code }}</td>
                        <td class="px-5 py-4 text-sm text-gray-600">{{ $requisition->budgetLine->code ?? '-' }}</td>
                        <td class="px-5 py-4 text-sm text-gray-900 text-right">${{ number_format($requisition->amount, 2) }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $requisition->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $requisition->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                {{ ucfirst($requisition->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-5 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No requisitions yet</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by creating a new requisition.</p>
            <div class="mt-4">
                <a href="{{ route('department-budgets.requisitions.index', $budget) }}" class="inline-flex items-center px-4 py-2 bg-black text-white text-sm font-medium rounded-md hover:bg-gray-800 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create Requisition
                </a>
            </div>
        </div>
        @endif
    </div>
</x-workspace-layout>
