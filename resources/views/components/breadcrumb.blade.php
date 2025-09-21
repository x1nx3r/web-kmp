{{-- Breadcrumb Component --}}
@props(['items' => []])

<nav class="flex mb-4 lg:mb-6" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        {{-- Home Icon untuk item pertama --}}
        <li class="inline-flex items-center">
            <i class="fas fa-home w-4 h-4 text-gray-500"></i>
        </li>

        {{-- Dynamic Breadcrumb Items --}}
        @foreach($items as $index => $item)
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right w-3 h-3 text-gray-400 mx-1"></i>
                    @if($loop->last)
                        {{-- Current page (not clickable) --}}
                        <span class="ms-1 text-sm font-medium text-gray-500 md:ms-2">
                            {{ $item['title'] ?? $item }}
                        </span>
                    @else
                        {{-- Clickable breadcrumb item --}}
                        <a href="{{ $item['url'] ?? '#' }}" class="ms-1 text-sm font-medium text-gray-700 hover:text-green-600 md:ms-2 transition-colors duration-200">
                            {{ $item['title'] ?? $item }}
                        </a>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
</nav>
