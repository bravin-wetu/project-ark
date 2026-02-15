@props([
    'open' => false,
])

<div 
    x-data="{ open: @js($open) }" 
    {{ $attributes->merge(['class' => 'border border-smoke-200 rounded-xl overflow-hidden']) }}
>
    <!-- Accordion Header -->
    <button 
        @click="open = !open"
        class="w-full flex items-center justify-between px-5 py-4 text-left bg-white hover:bg-smoke-50 transition-colors duration-150"
        type="button"
    >
        <span class="font-medium text-ink-900">{{ $trigger }}</span>
        <svg 
            :class="open ? 'rotate-180' : ''"
            class="w-5 h-5 text-smoke-500 transition-transform duration-200"
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    
    <!-- Accordion Content -->
    <div 
        x-show="open"
        x-collapse
        class="border-t border-smoke-200"
    >
        <div class="px-5 py-4 bg-smoke-50/50">
            {{ $slot }}
        </div>
    </div>
</div>
