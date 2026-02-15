@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
    'id' => null,
    'name' => null,
    'options' => [],
    'placeholder' => 'Select an option',
])

@php
    $inputId = $id ?? $name ?? Str::random(8);
    $selectClasses = 'w-full px-4 py-2.5 text-sm text-ink bg-paper border rounded-xl appearance-none cursor-pointer transition-all duration-200 ease-out hover:border-smoke-300 focus:border-ink focus:ring-0 focus:shadow-soft';
    
    if ($error) {
        $selectClasses .= ' border-accent-danger focus:border-accent-danger';
    } else {
        $selectClasses .= ' border-smoke-200';
    }
@endphp

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-ink">
            {{ $label }}
            @if($required)
                <span class="text-accent-danger">*</span>
            @endif
        </label>
    @endif
    
    <div class="relative">
        <select 
            id="{{ $inputId }}"
            @if($name) name="{{ $name }}" @endif
            {{ $attributes->merge(['class' => $selectClasses]) }}
        >
            @if($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            {{ $slot }}
        </select>
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-smoke-400">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </div>
    
    @if($hint && !$error)
        <p class="text-xs text-smoke-500">{{ $hint }}</p>
    @endif
    
    @if($error)
        <p class="text-xs text-accent-danger flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $error }}
        </p>
    @endif
</div>
