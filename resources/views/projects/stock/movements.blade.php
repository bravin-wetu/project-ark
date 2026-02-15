@section('title', 'Stock Movements - ' . $project->name)

<x-workspace-layout :workspace="$project" :workspaceType="'projects'">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
            <a href="{{ route('projects.stock.index', $project) }}" class="hover:text-ink-900 transition-colors">Stock</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">Movements</span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ink-900">Stock Movements</h1>
                <p class="text-smoke-600 mt-1">History of all stock adjustments, receipts, and issues.</p>
            </div>
            <a href="{{ route('projects.stock.index', $project) }}" class="btn-secondary text-sm">
                Back to Stock
            </a>
        </div>
    </div>

    <!-- Movements Table -->
    <div class="card overflow-hidden">
        @if($movements->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-smoke-200">
                <thead class="bg-smoke-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Reference</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Item</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">Qty</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Hub</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">User</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-smoke-100">
                    @foreach($movements as $movement)
                    @php
                        $typeColors = [
                            'adjustment' => [
                                'add' => 'bg-green-100 text-green-700',
                                'subtract' => 'bg-red-100 text-red-700',
                                'correction' => 'bg-amber-100 text-amber-700',
                                'transfer' => 'bg-blue-100 text-blue-700',
                            ],
                            'issue' => [
                                'issued' => 'bg-purple-100 text-purple-700',
                            ],
                            'receipt' => [
                                'received' => 'bg-emerald-100 text-emerald-700',
                            ],
                        ];
                        $colorClass = $typeColors[$movement['type']][$movement['subtype']] ?? 'bg-smoke-100 text-smoke-700';
                    @endphp
                    <tr class="hover:bg-smoke-50">
                        <td class="px-4 py-3 text-sm text-smoke-600">
                            {{ \Carbon\Carbon::parse($movement['date'])->format('M d, Y') }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 text-xs rounded {{ $colorClass }}">
                                {{ ucfirst($movement['subtype']) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm font-medium text-ink-900">
                            {{ $movement['reference'] }}
                        </td>
                        <td class="px-4 py-3 text-sm text-ink-900">
                            {{ $movement['item'] }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-semibold {{ $movement['quantity'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $movement['quantity'] >= 0 ? '+' : '' }}{{ number_format($movement['quantity']) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-smoke-600">
                            {{ $movement['hub'] }}
                        </td>
                        <td class="px-4 py-3 text-sm text-smoke-600">
                            {{ $movement['user'] }}
                        </td>
                        <td class="px-4 py-3 text-sm text-smoke-500">
                            {{ Str::limit($movement['notes'], 40) ?: '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-12 text-center text-smoke-500">
            <svg class="w-12 h-12 mx-auto text-smoke-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
            </svg>
            <p>No stock movements recorded yet.</p>
        </div>
        @endif
    </div>

    <!-- Legend -->
    <div class="mt-6 card p-4">
        <h3 class="text-sm font-medium text-ink-900 mb-3">Movement Types</h3>
        <div class="flex flex-wrap gap-4 text-sm">
            <span class="inline-flex items-center gap-1.5">
                <span class="px-2 py-0.5 text-xs rounded bg-green-100 text-green-700">Add</span>
                Stock increase (adjustment)
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="px-2 py-0.5 text-xs rounded bg-red-100 text-red-700">Subtract</span>
                Stock decrease (adjustment)
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="px-2 py-0.5 text-xs rounded bg-amber-100 text-amber-700">Correction</span>
                Count correction
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="px-2 py-0.5 text-xs rounded bg-blue-100 text-blue-700">Transfer</span>
                Inter-hub transfer
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="px-2 py-0.5 text-xs rounded bg-purple-100 text-purple-700">Issued</span>
                Stock issued out
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="px-2 py-0.5 text-xs rounded bg-emerald-100 text-emerald-700">Received</span>
                Goods received
            </span>
        </div>
    </div>
</x-workspace-layout>
