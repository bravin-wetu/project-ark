@props([
    'position' => 'top',
])

@php
    $positions = [
        'top' => 'bottom-full left-1/2 -translate-x-1/2 mb-2',
        'bottom' => 'top-full left-1/2 -translate-x-1/2 mt-2',
        'left' => 'right-full top-1/2 -translate-y-1/2 mr-2',
        'right' => 'left-full top-1/2 -translate-y-1/2 ml-2',
    ];
    
    $arrows = [
        'top' => 'top-full left-1/2 -translate-x-1/2 border-t-ink-900',
        'bottom' => 'bottom-full left-1/2 -translate-x-1/2 border-b-ink-900',
        'left' => 'left-full top-1/2 -translate-y-1/2 border-l-ink-900',
        'right' => 'right-full top-1/2 -translate-y-1/2 border-r-ink-900',
    ];
@endphp

<div 
    x-data="{ show: false }" 
    @mouseenter="show = true" 
    @mouseleave="show = false"
    class="relative inline-flex"
>
    {{ $trigger }}
    
    <div 
        x-show="show"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 {{ $positions[$position] }} pointer-events-none"
        style="display: none;"
    >
        <div {{ $attributes->merge(['class' => 'px-3 py-2 text-sm text-white bg-ink-900 rounded-lg shadow-lg whitespace-nowrap']) }}>
            {{ $slot }}
        </div>
    </div>
</div>
