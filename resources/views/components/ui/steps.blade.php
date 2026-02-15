@props([
    'steps' => [],
    'currentStep' => 1,
])

<div {{ $attributes->merge(['class' => 'flex items-center']) }}>
    @foreach($steps as $index => $step)
        @php
            $stepNumber = $index + 1;
            $isActive = $stepNumber === $currentStep;
            $isComplete = $stepNumber < $currentStep;
        @endphp
        
        <div class="flex items-center {{ $loop->last ? '' : 'flex-1' }}">
            {{-- Step indicator --}}
            <div class="flex items-center">
                <div class="step-indicator {{ $isComplete ? 'step-indicator-complete' : ($isActive ? 'step-indicator-active' : 'step-indicator-pending') }}">
                    @if($isComplete)
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    @else
                        {{ $stepNumber }}
                    @endif
                </div>
                <span class="ml-2.5 text-sm font-medium {{ $isComplete || $isActive ? 'text-ink' : 'text-smoke-400' }} hidden sm:block">
                    {{ $step }}
                </span>
            </div>
            
            {{-- Connector --}}
            @if(!$loop->last)
                <div class="step-connector {{ $isComplete ? 'step-connector-complete' : '' }} mx-4"></div>
            @endif
        </div>
    @endforeach
</div>
