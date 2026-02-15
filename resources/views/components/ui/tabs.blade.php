@props([
    'tabs' => [],
    'active' => null,
])

<div 
    x-data="{ activeTab: '{{ $active ?? (count($tabs) ? array_keys($tabs)[0] : '') }}' }"
    {{ $attributes }}
>
    <!-- Tab Headers -->
    <div class="border-b border-smoke-200">
        <nav class="flex -mb-px space-x-6" aria-label="Tabs">
            @foreach($tabs as $key => $label)
                <button 
                    @click="activeTab = '{{ $key }}'"
                    :class="activeTab === '{{ $key }}' ? 'border-ink-900 text-ink-900' : 'border-transparent text-smoke-500 hover:text-smoke-700 hover:border-smoke-300'"
                    class="px-1 py-4 text-sm font-medium border-b-2 transition-colors duration-200 whitespace-nowrap"
                    type="button"
                >
                    {{ $label }}
                </button>
            @endforeach
        </nav>
    </div>
    
    <!-- Tab Panels -->
    <div class="mt-4">
        {{ $slot }}
    </div>
</div>
