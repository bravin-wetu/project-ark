@props([
    'variant' => 'default',
    'size' => 'md',
    'dot' => false,
])

@php
    $variants = [
        'default' => 'bg-smoke-100 text-smoke-700',
        'dark' => 'bg-ink text-paper',
        'success' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
        'warning' => 'bg-amber-50 text-amber-700 border border-amber-100',
        'danger' => 'bg-red-50 text-red-700 border border-red-100',
        'info' => 'bg-blue-50 text-blue-700 border border-blue-100',
    ];
    
    $sizes = [
        'sm' => 'px-2 py-0.5 text-[10px]',
        'md' => 'px-2.5 py-1 text-xs',
        'lg' => 'px-3 py-1.5 text-sm',
    ];

    $dotColors = [
        'default' => 'bg-smoke-400',
        'dark' => 'bg-paper',
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-red-500',
        'info' => 'bg-blue-500',
    ];
    
    $classes = 'inline-flex items-center font-medium rounded-lg ' . ($variants[$variant] ?? $variants['default']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $dotColors[$variant] ?? $dotColors['default'] }}"></span>
    @endif
    {{ $slot }}
</span>
