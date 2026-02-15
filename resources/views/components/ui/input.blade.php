@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
    'type' => 'text',
    'id' => null,
    'name' => null,
])

@php
    $inputId = $id ?? $name ?? Str::random(8);
    $inputClasses = 'w-full px-4 py-2.5 text-sm text-ink bg-paper border rounded-xl placeholder:text-smoke-400 transition-all duration-200 ease-out hover:border-smoke-300 focus:border-ink focus:ring-0 focus:shadow-soft';
    
    if ($error) {
        $inputClasses .= ' border-accent-danger focus:border-accent-danger';
    } else {
        $inputClasses .= ' border-smoke-200';
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
    
    <input 
        type="{{ $type }}"
        id="{{ $inputId }}"
        @if($name) name="{{ $name }}" @endif
        {{ $attributes->merge(['class' => $inputClasses]) }}
    />
    
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
