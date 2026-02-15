@section('title', 'Goods Receipts - ' . $project->name)

<x-workspace-layout :workspace="$project" :workspaceType="'projects'">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
            <a href="{{ route('projects.show', $project) }}" class="hover:text-ink-900 transition-colors">{{ $project->name }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">Goods Receipts</span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ink-900">Goods Receipts</h1>
                <p class="text-smoke-600 mt-1">Track and manage received goods for purchase orders.</p>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-lg border border-smoke-200 p-4">
            <div class="text-2xl font-bold text-ink-900">{{ $stats['total'] }}</div>
            <div class="text-sm text-smoke-600">Total Receipts</div>
        </div>
        <div class="bg-white rounded-lg border border-smoke-200 p-4">
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['draft'] }}</div>
            <div class="text-sm text-smoke-600">Pending Confirmation</div>
        </div>
        <div class="bg-white rounded-lg border border-smoke-200 p-4">
            <div class="text-2xl font-bold text-green-600">{{ $stats['confirmed'] }}</div>
            <div class="text-sm text-smoke-600">Confirmed</div>
        </div>
    </div>

    <!-- Receipts Table -->
    <div class="bg-white rounded-lg border border-smoke-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-smoke-200">
            <h2 class="text-lg font-semibold text-ink-900">All Goods Receipts</h2>
        </div>

        @if($receipts->isEmpty())
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-smoke-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-ink-900">No goods receipts</h3>
                <p class="mt-1 text-sm text-smoke-500">Goods receipts are recorded when orders are delivered.</p>
                <div class="mt-6">
                    <a href="{{ route('projects.purchase-orders.index', $project) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-ink-600 hover:bg-ink-700">
                        View Purchase Orders
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-smoke-200">
                    <thead class="bg-smoke-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Receipt #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">PO Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Received</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Condition</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Items</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-smoke-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-smoke-200">
                        @foreach($receipts as $receipt)
                            <tr class="hover:bg-smoke-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('projects.purchase-orders.receipts.show', [$project, $receipt->purchaseOrder, $receipt]) }}" class="text-ink-600 hover:text-ink-900 font-medium">
                                        {{ $receipt->receipt_number }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('projects.purchase-orders.show', [$project, $receipt->purchaseOrder]) }}" class="text-smoke-600 hover:text-ink-900">
                                        {{ $receipt->purchaseOrder->po_number }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-ink-900">{{ $receipt->purchaseOrder->supplier->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-ink-900">
                                    {{ $receipt->received_at->format('M d, Y') }}
                                    <div class="text-xs text-smoke-500">by {{ $receipt->received_by }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        @switch($receipt->overall_condition)
                                            @case('excellent') bg-green-100 text-green-800 @break
                                            @case('good') bg-blue-100 text-blue-800 @break
                                            @case('acceptable') bg-yellow-100 text-yellow-800 @break
                                            @case('damaged') bg-orange-100 text-orange-800 @break
                                            @case('rejected') bg-red-100 text-red-800 @break
                                            @default bg-gray-100 text-gray-800
                                        @endswitch
                                    ">
                                        {{ ucfirst($receipt->overall_condition) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @switch($receipt->status)
                                            @case('draft') bg-gray-100 text-gray-800 @break
                                            @case('confirmed') bg-green-100 text-green-800 @break
                                            @case('partial') bg-yellow-100 text-yellow-800 @break
                                            @case('complete') bg-blue-100 text-blue-800 @break
                                            @case('cancelled') bg-red-100 text-red-800 @break
                                            @default bg-gray-100 text-gray-800
                                        @endswitch
                                    ">
                                        {{ ucfirst($receipt->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-ink-900">
                                    {{ $receipt->items->count() }} items
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('projects.purchase-orders.receipts.show', [$project, $receipt->purchaseOrder, $receipt]) }}" class="text-ink-600 hover:text-ink-900">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($receipts->hasPages())
                <div class="px-6 py-4 border-t border-smoke-200">
                    {{ $receipts->links() }}
                </div>
            @endif
        @endif
    </div>
</x-workspace-layout>
