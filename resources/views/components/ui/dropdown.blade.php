@props([
    'align' => 'left',
    'width' => '48',
    'contentClasses' => 'py-1',
])

@php
    $alignments = [
        'left' => 'origin-top-left left-0',
        'right' => 'origin-top-right right-0',
        'center' => 'origin-top left-1/2 -translate-x-1/2',
    ];
    
    $widths = [
        '36' => 'w-36',
        '48' => 'w-48',
        '56' => 'w-56',
        '64' => 'w-64',
        '72' => 'w-72',
    ];
@endphp

<div 
    x-data="{ open: false }" 
    @click.outside="open = false" 
    @close.stop="open = false"
    class="relative"
>
    <!-- Trigger -->
    <div @click="open = !open">
        {{ $trigger }}
    </div>

    <!-- Dropdown Menu -->
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-2 {{ $widths[$width] }} {{ $alignments[$align] }}"
        style="display: none;"
    >
        <div class="bg-white border border-smoke-200 rounded-xl shadow-lifted overflow-hidden {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
