@section('title', 'Supplier Performance - ' . $project->name)

<x-workspace-layout :workspace="$project" :workspaceType="'projects'">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-smoke-500 mb-2">
            <a href="{{ route('projects.reports.index', $project) }}" class="hover:text-ink-900 transition-colors">Reports</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink-900 font-medium">Supplier Performance</span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ink-900">Supplier Performance Report</h1>
                <p class="text-smoke-600 mt-1">Delivery metrics, spending, and supplier rankings.</p>
            </div>
            <form method="GET" class="flex items-center gap-2">
                <input type="date" name="start_date" value="{{ $startDate instanceof \Carbon\Carbon ? $startDate->format('Y-m-d') : $startDate }}" 
                       class="form-input text-sm">
                <span class="text-smoke-400">to</span>
                <input type="date" name="end_date" value="{{ $endDate instanceof \Carbon\Carbon ? $endDate->format('Y-m-d') : $endDate }}" 
                       class="form-input text-sm">
                <button type="submit" class="btn-secondary text-sm">Apply</button>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Total Suppliers</div>
            <div class="text-3xl font-semibold text-ink-900">{{ $suppliers->count() }}</div>
        </div>
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Total PO Value</div>
            <div class="text-3xl font-semibold text-ink-900">KES {{ number_format($suppliers->sum('total_value'), 0) }}</div>
        </div>
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Total POs</div>
            <div class="text-3xl font-semibold text-ink-900">{{ $suppliers->sum('total_pos') }}</div>
        </div>
        <div class="card p-4">
            <div class="text-sm text-smoke-500">Avg. On-Time Rate</div>
            @php
                $avgOnTime = $suppliers->whereNotNull('on_time_rate')->avg('on_time_rate');
            @endphp
            <div class="text-3xl font-semibold {{ $avgOnTime >= 90 ? 'text-green-600' : ($avgOnTime >= 70 ? 'text-amber-600' : 'text-red-600') }}">
                {{ $avgOnTime ? round($avgOnTime, 1) . '%' : '—' }}
            </div>
        </div>
    </div>

    <!-- Suppliers Table -->
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-smoke-200">
            <h2 class="font-medium text-ink-900">Supplier Performance Metrics</h2>
        </div>
        @if($suppliers->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-smoke-200">
                <thead class="bg-smoke-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-smoke-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">Category</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">Total POs</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">Completed</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-smoke-500 uppercase tracking-wider">Total Spend</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">On-Time Rate</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">Avg. Delivery</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-smoke-500 uppercase tracking-wider">Rating</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-smoke-100">
                    @foreach($suppliers as $supplier)
                    @php
                        // Calculate a simple rating based on on-time delivery
                        $rating = null;
                        if ($supplier['on_time_rate'] !== null) {
                            if ($supplier['on_time_rate'] >= 90) $rating = 5;
                            elseif ($supplier['on_time_rate'] >= 80) $rating = 4;
                            elseif ($supplier['on_time_rate'] >= 70) $rating = 3;
                            elseif ($supplier['on_time_rate'] >= 50) $rating = 2;
                            else $rating = 1;
                        }
                    @endphp
                    <tr class="hover:bg-smoke-50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-ink-900">{{ $supplier['name'] }}</div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex px-2 py-0.5 text-xs bg-smoke-100 text-smoke-700 rounded">
                                {{ $supplier['category'] ?? 'General' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-ink-900">{{ $supplier['total_pos'] }}</td>
                        <td class="px-4 py-3 text-center text-smoke-600">{{ $supplier['completed_pos'] }}</td>
                        <td class="px-4 py-3 text-right font-medium text-ink-900">KES {{ number_format($supplier['total_value'], 0) }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($supplier['on_time_rate'] !== null)
                            <span class="font-medium {{ $supplier['on_time_rate'] >= 90 ? 'text-green-600' : ($supplier['on_time_rate'] >= 70 ? 'text-amber-600' : 'text-red-600') }}">
                                {{ $supplier['on_time_rate'] }}%
                            </span>
                            @else
                            <span class="text-smoke-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($supplier['avg_delivery_days'] !== null)
                            <span class="text-ink-900">{{ $supplier['avg_delivery_days'] }} days</span>
                            @else
                            <span class="text-smoke-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($rating)
                            <div class="flex justify-center gap-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= $rating ? 'text-amber-400' : 'text-smoke-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                @endfor
                            </div>
                            @else
                            <span class="text-smoke-400">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-12 text-center text-smoke-500">
            <svg class="w-12 h-12 mx-auto text-smoke-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p>No supplier data available for this period.</p>
        </div>
        @endif
    </div>

    <!-- Legend -->
    <div class="mt-6 card p-4">
        <h3 class="text-sm font-medium text-ink-900 mb-2">Performance Rating Legend</h3>
        <div class="flex flex-wrap gap-6 text-sm text-smoke-600">
            <div class="flex items-center gap-2">
                <span class="text-green-600 font-medium">90%+</span>
                <span>= 5 stars (Excellent)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-green-600 font-medium">80-89%</span>
                <span>= 4 stars (Good)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-amber-600 font-medium">70-79%</span>
                <span>= 3 stars (Average)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-amber-600 font-medium">50-69%</span>
                <span>= 2 stars (Below Average)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-red-600 font-medium">&lt;50%</span>
                <span>= 1 star (Poor)</span>
            </div>
        </div>
    </div>
</x-workspace-layout>
