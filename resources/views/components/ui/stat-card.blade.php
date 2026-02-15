@props([
    'title' => 'Label',
    'value' => '0',
    'subtitle' => null,
    'change' => null,
    'changeType' => null, // 'positive', 'negative', or null
    'icon' => null,
    'href' => null,
])

@php
    $Tag = $href ? 'a' : 'div';
    $extraClasses = $href ? 'hover:border-smoke-200 hover:shadow-soft hover:-translate-y-0.5 cursor-pointer' : '';
@endphp

<{{ $Tag }} 
    @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => "stat-card animate-fade-in-up {$extraClasses}"]) }}
>
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <p class="stat-label">{{ $title }}</p>
            <p class="stat-value">{{ $value }}</p>
            
            @if($subtitle)
                <p class="mt-1 text-sm text-smoke-500">{{ $subtitle }}</p>
            @elseif($change)
                <p class="mt-1 flex items-center gap-1 text-xs font-medium {{ $changeType === 'positive' ? 'text-emerald-600' : ($changeType === 'negative' ? 'text-red-600' : 'text-smoke-500') }}">
                    @if($changeType === 'positive')
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                        </svg>
                    @elseif($changeType === 'negative')
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                    @endif
                    {{ $change }}
                </p>
            @endif
        </div>
        
        @if($icon)
            <div class="p-2.5 bg-smoke-50 rounded-xl">
                <svg class="w-5 h-5 text-smoke-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    {!! $icon !!}
                </svg>
            </div>
        @endif
    </div>
</{{ $Tag }}>
