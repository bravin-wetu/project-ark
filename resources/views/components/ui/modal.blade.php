@props([
    'id' => null,
    'maxWidth' => 'md',
    'show' => false,
])

@php
    $maxWidths = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        'full' => 'max-w-full',
    ];
@endphp

<div 
    x-data="{ open: @js($show) }" 
    x-on:open-modal.window="$event.detail === '{{ $id }}' ? open = true : null"
    x-on:close-modal.window="$event.detail === '{{ $id }}' ? open = false : null"
    x-on:close.stop="open = false"
    x-on:keydown.escape.window="open = false"
    x-show="open"
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
>
    <!-- Backdrop -->
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-ink-900/60 backdrop-blur-sm"
        @click="open = false"
    ></div>
    
    <!-- Modal Panel -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div 
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            {{ $attributes->merge(['class' => "relative w-full {$maxWidths[$maxWidth]} bg-white rounded-2xl shadow-lifted overflow-hidden"]) }}
            @click.outside="open = false"
        >
            {{ $slot }}
        </div>
    </div>
</div>
