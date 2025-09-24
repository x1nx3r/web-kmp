{{-- Pagination Component --}}
@props(['paginator'])

@if($paginator->hasPages())
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
        <div class="flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0">
            {{-- Pagination Info --}}
            <div class="text-sm text-gray-700">
                @php
                    $currentPage = $paginator->currentPage();
                    $perPage = $paginator->perPage();
                    $total = $paginator->total();
                    $from = ($currentPage - 1) * $perPage + 1;
                    $to = min($currentPage * $perPage, $total);
                @endphp
                Menampilkan 
                <span class="font-medium text-green-600">{{ $from }}</span>
                sampai 
                <span class="font-medium text-green-600">{{ $to }}</span>
                dari 
                <span class="font-medium text-green-600">{{ $total }}</span> nama klien
            </div>

            {{-- Pagination Controls --}}
            <div class="flex items-center space-x-2">
                {{-- Previous Button --}}
                @if ($paginator->onFirstPage())
                    <span class="px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                        <i class="fas fa-chevron-left mr-1"></i>Sebelumnya
                    </span>
                @else
                    <a 
                        href="{{ $paginator->previousPageUrl() }}" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-green-50 hover:text-green-700 hover:border-green-300 transition-colors duration-200"
                    >
                        <i class="fas fa-chevron-left mr-1"></i>Sebelumnya
                    </a>
                @endif

                {{-- Page Numbers --}}
                @if($paginator->lastPage() > 1)
                    <div class="hidden sm:flex items-center space-x-1">
                        @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="px-3 py-2 text-sm font-medium text-white bg-green-600 border border-green-600 rounded-lg">
                                    {{ $page }}
                                </span>
                            @else
                                <a 
                                    href="{{ $url }}" 
                                    class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-green-50 hover:text-green-700 hover:border-green-300 transition-colors duration-200"
                                >
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                    <div class="sm:hidden px-3 py-2 text-sm font-medium text-gray-700 bg-gray-50 border border-gray-300 rounded-lg">
                        {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
                    </div>
                @endif

                {{-- Next Button --}}
                @if ($paginator->hasMorePages())
                    <a 
                        href="{{ $paginator->nextPageUrl() }}" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-green-50 hover:text-green-700 hover:border-green-300 transition-colors duration-200"
                    >
                        Selanjutnya<i class="fas fa-chevron-right ml-1"></i>
                    </a>
                @else
                    <span class="px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                        Selanjutnya<i class="fas fa-chevron-right ml-1"></i>
                    </span>
                @endif
            </div>
        </div>
    </div>
@endif