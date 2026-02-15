@props([
    'items' => [],
    'separator' => null,
])

<nav {{ $attributes->merge(['class' => 'flex items-center text-sm text-smoke-600']) }} aria-label="Breadcrumb">
    <ol class="flex items-center space-x-2">
        @foreach($items as $index => $item)
            <li class="flex items-center">
                @if($index > 0)
                    @if($separator)
                        <span class="mx-2 text-smoke-400">{{ $separator }}</span>
                    @else
                        <svg class="w-4 h-4 mx-2 text-smoke-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                @endif
                
                @if(isset($item['href']) && $index < count($items) - 1)
                    <a 
                        href="{{ $item['href'] }}" 
                        class="hover:text-ink-900 transition-colors duration-150"
                    >
                        @if(isset($item['icon']))
                            <span class="flex items-center">
                                {!! $item['icon'] !!}
                                @if(isset($item['label']))
                                    <span class="ml-1">{{ $item['label'] }}</span>
                                @endif
                            </span>
                        @else
                            {{ $item['label'] }}
                        @endif
                    </a>
                @else
                    <span class="{{ $index === count($items) - 1 ? 'text-ink-900 font-medium' : '' }}">
                        @if(isset($item['icon']))
                            <span class="flex items-center">
                                {!! $item['icon'] !!}
                                @if(isset($item['label']))
                                    <span class="ml-1">{{ $item['label'] }}</span>
                                @endif
                            </span>
                        @else
                            {{ $item['label'] }}
                        @endif
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
