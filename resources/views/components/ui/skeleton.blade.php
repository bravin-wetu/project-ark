@props([
    'type' => 'text',
    'height' => 'md',
    'rounded' => 'md',
])

@php
    $heights = [
        'xs' => 'h-3',
        'sm' => 'h-4',
        'md' => 'h-6',
        'lg' => 'h-8',
        'xl' => 'h-10',
    ];
    
    $roundeds = [
        'none' => 'rounded-none',
        'sm' => 'rounded',
        'md' => 'rounded-lg',
        'lg' => 'rounded-xl',
        'full' => 'rounded-full',
    ];
    
    $types = [
        'text' => 'w-full',
        'title' => 'w-3/4 h-8',
        'avatar' => 'w-10 h-10 rounded-full',
        'thumbnail' => 'w-16 h-16',
        'button' => 'w-24 h-10',
    ];
@endphp

@if($type === 'avatar')
    <div {{ $attributes->merge(['class' => 'skeleton w-10 h-10 rounded-full']) }}></div>
@elseif($type === 'thumbnail')
    <div {{ $attributes->merge(['class' => 'skeleton w-16 h-16 ' . $roundeds[$rounded]]) }}></div>
@else
    <div {{ $attributes->merge(['class' => "skeleton {$heights[$height]} {$roundeds[$rounded]} {$types[$type]}"]) }}></div>
@endif
