@props([
    'label' => null,
    'checked' => false,
    'description' => null,
])

<label class="flex items-start cursor-pointer group">
    <div class="flex items-center h-5">
        <input 
            type="checkbox" 
            {{ $checked ? 'checked' : '' }}
            {{ $attributes->merge(['class' => 'checkbox']) }}
        >
    </div>
    @if($label || $description)
        <div class="ml-3">
            @if($label)
                <span class="text-sm text-ink-700 group-hover:text-ink-900 transition-colors">{{ $label }}</span>
            @endif
            @if($description)
                <p class="text-sm text-smoke-500">{{ $description }}</p>
            @endif
        </div>
    @endif
</label>
