@props([
    'value' => 0,
    'max' => 100,
    'size' => 'md',
    'animated' => false,
    'showLabel' => false,
    'label' => null,
])

@php
    $percentage = $max > 0 ? min(100, ($value / $max) * 100) : 0;
    
    $sizes = [
        'xs' => 'h-1',
        'sm' => 'h-1.5',
        'md' => 'h-2',
        'lg' => 'h-3',
    ];
    
    $trackSize = $sizes[$size] ?? $sizes['md'];
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    @if($showLabel || $label)
        <div class="flex items-center justify-between mb-1.5">
            <span class="text-xs font-medium text-smoke-600">{{ $label ?? 'Progress' }}</span>
            <span class="text-xs font-medium text-ink tabular-nums">{{ number_format($percentage, 0) }}%</span>
        </div>
    @endif
    
    <div class="w-full bg-smoke-100 rounded-full {{ $trackSize }} overflow-hidden">
        <div 
            class="h-full bg-ink rounded-full transition-all duration-500 ease-out {{ $animated ? 'animate-progress' : '' }}"
            style="width: {{ $percentage }}%; --progress-width: {{ $percentage }}%;"
        ></div>
    </div>
</div>
