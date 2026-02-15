@props([
    'href' => null,
    'active' => false,
])

@php
    $classes = 'block px-4 py-2.5 text-sm transition-colors duration-150 ';
    $classes .= $active 
        ? 'bg-smoke-100 text-ink-900 font-medium' 
        : 'text-smoke-700 hover:bg-smoke-50 hover:text-ink-900';
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes . ' w-full text-left', 'type' => 'button']) }}>
        {{ $slot }}
    </button>
@endif
