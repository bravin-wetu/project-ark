@props([
    'label' => null,
    'error' => null,
    'hint' => null,
])

<div class="w-full">
    @if($label)
        <label class="block text-sm font-medium text-ink-700 mb-1.5">
            {{ $label }}
        </label>
    @endif
    
    <textarea {{ $attributes->merge([
        'class' => 'input w-full resize-none min-h-[120px]' . ($error ? ' border-red-300 focus:border-red-500 focus:ring-red-500/20' : ''),
        'rows' => 4
    ]) }}>{{ $slot }}</textarea>
    
    @if($error)
        <p class="mt-1.5 text-sm text-red-600 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $error }}
        </p>
    @elseif($hint)
        <p class="mt-1.5 text-sm text-smoke-500">{{ $hint }}</p>
    @endif
</div>
