@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'iconPosition' => 'left',
    'loading' => false,
    'disabled' => false,
    'type' => 'button',
    'href' => null,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-xl transition-all duration-200 ease-out focus:outline-none focus-visible:ring-2 focus-visible:ring-ink focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
    
    $variants = [
        'primary' => 'bg-ink text-paper hover:bg-ink-light hover:shadow-medium hover:-translate-y-0.5 active:translate-y-0 active:shadow-soft',
        'secondary' => 'bg-paper text-ink border border-smoke-200 hover:bg-smoke-50 hover:border-smoke-300 hover:shadow-soft active:bg-smoke-100',
        'ghost' => 'bg-transparent text-smoke-600 hover:bg-smoke-100 hover:text-ink active:bg-smoke-200',
        'danger' => 'bg-accent-danger text-paper hover:bg-red-600 hover:shadow-medium active:bg-red-700',
        'success' => 'bg-accent-success text-paper hover:bg-emerald-600 hover:shadow-medium active:bg-emerald-700',
    ];
    
    $sizes = [
        'xs' => 'px-2.5 py-1.5 text-xs gap-1.5 rounded-lg',
        'sm' => 'px-3 py-2 text-sm gap-2 rounded-lg',
        'md' => 'px-4 py-2.5 text-sm gap-2',
        'lg' => 'px-6 py-3 text-base gap-2.5 rounded-2xl',
        'xl' => 'px-8 py-4 text-lg gap-3 rounded-2xl',
    ];

    $iconSizes = [
        'xs' => 'w-3.5 h-3.5',
        'sm' => 'w-4 h-4',
        'md' => 'w-4 h-4',
        'lg' => 'w-5 h-5',
        'xl' => 'w-6 h-6',
    ];
    
    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($loading)
            <svg class="animate-spin {{ $iconSizes[$size] ?? 'w-4 h-4' }}" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @elseif($icon && $iconPosition === 'left')
            <x-dynamic-component :component="'icons.' . $icon" class="{{ $iconSizes[$size] ?? 'w-4 h-4' }}" />
        @endif
        
        {{ $slot }}
        
        @if($icon && $iconPosition === 'right' && !$loading)
            <x-dynamic-component :component="'icons.' . $icon" class="{{ $iconSizes[$size] ?? 'w-4 h-4' }}" />
        @endif
    </a>
@else
    <button 
        type="{{ $type }}" 
        {{ $disabled || $loading ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if($loading)
            <svg class="animate-spin {{ $iconSizes[$size] ?? 'w-4 h-4' }}" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @elseif($icon && $iconPosition === 'left')
            <x-dynamic-component :component="'icons.' . $icon" class="{{ $iconSizes[$size] ?? 'w-4 h-4' }}" />
        @endif
        
        {{ $slot }}
        
        @if($icon && $iconPosition === 'right' && !$loading)
            <x-dynamic-component :component="'icons.' . $icon" class="{{ $iconSizes[$size] ?? 'w-4 h-4' }}" />
        @endif
    </button>
@endif
