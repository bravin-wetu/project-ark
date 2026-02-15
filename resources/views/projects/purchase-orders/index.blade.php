@section('title', 'Purchase Orders - ' . $project->name)

<x-workspace-layout :workspace="$project" :workspaceType="'projects'">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
            <a href="{{ route('projects.show', $project) }}" class="hover:text-ink-900 transition-colors">{{ $project->name }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">Purchase Orders</span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ink-900">Purchase Orders</h1>
                <p class="text-smoke-600 mt-1">Manage purchase orders and track order status with suppliers.</p>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
        <div class="bg-white rounded-lg border border-smoke-200 p-4">
            <div class="text-2xl font-bold text-ink-900">{{ $stats['total'] }}</div>
            <div class="text-sm text-smoke-600">Total POs</div>
        </div>
        <div class="bg-white rounded-lg border border-smoke-200 p-4">
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending_approval'] }}</div>
            <div class="text-sm text-smoke-600">Pending Approval</div>
        </div>
        <div class="bg-white rounded-lg border border-smoke-200 p-4">
            <div class="text-2xl font-bold text-green-600">{{ $stats['approved'] }}</div>
            <div class="text-sm text-smoke-600">Approved</div>
        </div>
        <div class="bg-white rounded-lg border border-smoke-200 p-4">
            <div class="text-2xl font-bold text-blue-600">{{ $stats['sent'] }}</div>
            <div class="text-sm text-smoke-600">Sent to Suppliers</div>
        </div>
        <div class="bg-white rounded-lg border border-smoke-200 p-4">
            <div class="text-2xl font-bold text-purple-600">{{ $stats['received'] }}</div>
            <div class="text-sm text-smoke-600">Receiving</div>
        </div>
        <div class="bg-white rounded-lg border border-smoke-200 p-4">
            <div class="text-2xl font-bold text-ink-900">K{{ number_format($stats['total_value'], 2) }}</div>
            <div class="text-sm text-smoke-600">Total Value</div>
        </div>
    </div>

    <!-- Purchase Orders Table -->
    <div class="bg-white rounded-lg border border-smoke-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-smoke-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-ink-900">All Purchase Orders</h2>
                <div class="flex items-center gap-2">
                    <input type="text" placeholder="Search POs..." class="px-3 py-2 border border-smoke-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ink-500">
                </div>
            </div>
        </div>

        @if($purchaseOrders->isEmpty())
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-smoke-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-ink-900">No purchase orders</h3>
                <p class="mt-1 text-sm text-smoke-500">Purchase orders are created from awarded quotes in RFQs.</p>
                <div class="mt-6">
                    <a href="{{ route('projects.rfqs.index', $project) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-ink-600 hover:bg-ink-700">
                        View RFQs
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-smoke-200">
                    <thead class="bg-smoke-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">PO Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Expected Delivery</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Receipt %</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-smoke-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-smoke-200">
                        @foreach($purchaseOrders as $po)
                            <tr class="hover:bg-smoke-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('projects.purchase-orders.show', [$project, $po]) }}" class="text-ink-600 hover:text-ink-900 font-medium">
                                        {{ $po->po_number }}
                                    </a>
                                    <div class="text-xs text-smoke-500">{{ $po->created_at->format('M d, Y') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-ink-900">{{ $po->supplier->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-smoke-500">{{ $po->supplier->code ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-ink-900">
                                    {{ $po->items->count() }} items
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-ink-900">
                                    K{{ number_format($po->total_amount, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @switch($po->status)
                                            @case('draft') bg-gray-100 text-gray-800 @break
                                            @case('pending_approval') bg-yellow-100 text-yellow-800 @break
                                            @case('approved') bg-green-100 text-green-800 @break
                                            @case('rejected') bg-red-100 text-red-800 @break
                                            @case('sent') bg-blue-100 text-blue-800 @break
                                            @case('acknowledged') bg-indigo-100 text-indigo-800 @break
                                            @case('partially_received') bg-purple-100 text-purple-800 @break
                                            @case('received') bg-teal-100 text-teal-800 @break
                                            @case('closed') bg-gray-100 text-gray-800 @break
                                            @case('cancelled') bg-red-100 text-red-800 @break
                                            @default bg-gray-100 text-gray-800
                                        @endswitch
                                    ">
                                        {{ str_replace('_', ' ', ucfirst($po->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-ink-900">
                                    @if($po->expected_delivery_date)
                                        {{ $po->expected_delivery_date->format('M d, Y') }}
                                        @if($po->expected_delivery_date->isPast() && !$po->isReceived())
                                            <span class="text-red-500 text-xs">(Overdue)</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $totalQty = $po->items->sum('quantity');
                                        $receivedQty = $po->items->sum('received_quantity');
                                        $pct = $totalQty > 0 ? round(($receivedQty / $totalQty) * 100) : 0;
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-smoke-200 rounded-full h-2 w-16">
                                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="text-xs text-smoke-600">{{ $pct }}%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('projects.purchase-orders.show', [$project, $po]) }}" class="text-ink-600 hover:text-ink-900">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($purchaseOrders->hasPages())
                <div class="px-6 py-4 border-t border-smoke-200">
                    {{ $purchaseOrders->links() }}
                </div>
            @endif
        @endif
    </div>
</x-workspace-layout>
