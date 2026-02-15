@props([
    'links' => [],
    'total' => 0,
    'perPage' => 10,
    'currentPage' => 1,
])

@php
    $lastPage = ceil($total / $perPage);
    $from = ($currentPage - 1) * $perPage + 1;
    $to = min($currentPage * $perPage, $total);
@endphp

@if($lastPage > 1)
<nav {{ $attributes->merge(['class' => 'flex items-center justify-between px-4 py-3 bg-white border border-smoke-200 rounded-xl']) }}>
    <div class="hidden sm:block">
        <p class="text-sm text-smoke-600">
            Showing <span class="font-medium text-ink-900">{{ $from }}</span> to <span class="font-medium text-ink-900">{{ $to }}</span> of <span class="font-medium text-ink-900">{{ $total }}</span> results
        </p>
    </div>
    
    <div class="flex items-center space-x-2">
        <!-- Previous -->
        @if($currentPage > 1)
            <a href="{{ $links['prev'] ?? '#' }}" class="p-2 text-smoke-600 hover:text-ink-900 hover:bg-smoke-100 rounded-lg transition-colors duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        @else
            <span class="p-2 text-smoke-300 cursor-not-allowed">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </span>
        @endif
        
        <!-- Page Numbers -->
        @for($page = max(1, $currentPage - 2); $page <= min($lastPage, $currentPage + 2); $page++)
            @if($page === $currentPage)
                <span class="px-3 py-1.5 text-sm font-medium text-white bg-ink-900 rounded-lg">{{ $page }}</span>
            @else
                <a href="{{ $links[$page] ?? '#' }}" class="px-3 py-1.5 text-sm text-smoke-600 hover:text-ink-900 hover:bg-smoke-100 rounded-lg transition-colors duration-150">{{ $page }}</a>
            @endif
        @endfor
        
        <!-- Next -->
        @if($currentPage < $lastPage)
            <a href="{{ $links['next'] ?? '#' }}" class="p-2 text-smoke-600 hover:text-ink-900 hover:bg-smoke-100 rounded-lg transition-colors duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        @else
            <span class="p-2 text-smoke-300 cursor-not-allowed">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        @endif
    </div>
</nav>
@endif
