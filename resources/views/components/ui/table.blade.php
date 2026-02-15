@props([
    'striped' => false,
    'hoverable' => true,
])

@php
    $classes = 'table-container';
    $tableClasses = 'table w-full';
    if ($striped) $tableClasses .= ' table-striped';
    if ($hoverable) $tableClasses .= ' table-hover';
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    <table class="{{ $tableClasses }}">
        @if(isset($head))
            <thead>
                {{ $head }}
            </thead>
        @endif
        <tbody>
            {{ $slot }}
        </tbody>
        @if(isset($foot))
            <tfoot>
                {{ $foot }}
            </tfoot>
        @endif
    </table>
</div>
