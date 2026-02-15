@props([
    'hover' => false,
    'padding' => true,
])

@php
    $classes = 'bg-paper rounded-2xl border border-smoke-100 overflow-hidden transition-all duration-300 ease-out';
    
    if ($hover) {
        $classes .= ' hover:border-smoke-200 hover:shadow-soft hover:-translate-y-0.5 cursor-pointer active:translate-y-0 active:shadow-none';
    }
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @if(isset($header))
        <div class="px-5 py-4 border-b border-smoke-100">
            {{ $header }}
        </div>
    @endif

    <div class="{{ $padding ? 'p-5' : '' }}">
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="px-5 py-4 border-t border-smoke-100 bg-smoke-50/50">
            {{ $footer }}
        </div>
    @endif
</div>
