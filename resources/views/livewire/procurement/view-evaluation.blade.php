<div>
    <div class="min-h-screen bg-gray-50 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Detail Evaluasi Supplier</h1>
                        <p class="mt-1 text-sm text-gray-600">
                            Pengiriman: <span class="font-semibold">{{ $pengiriman->no_pengiriman }}</span>
                        </p>
                    </div>
                    <a href="{{ route('orders.show', $pengiriman->purchase_order_id) }}" 
                       class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors text-sm">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                </div>
            </div>

            @if(!$evaluation)
                <!-- No Evaluation Message -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-4xl mb-3"></i>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Evaluasi Belum Tersedia</h3>
                    <p class="text-gray-600 mb-4">Pengiriman ini belum dievaluasi.</p>
                    <a href="{{ route('pengiriman.evaluasi', $pengiriman->id) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                        <i class="fas fa-star mr-2"></i>
                        Lakukan Evaluasi Sekarang
                    </a>
                </div>
            @else
                <!-- Evaluation Summary Card -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 mb-6 text-white">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <!-- Supplier Info -->
                        <div>
                            <div class="text-blue-100 text-sm font-medium mb-1">Supplier</div>
                            <div class="text-2xl font-bold">{{ $evaluation->supplier?->nama ?? '-' }}</div>
                        </div>
                        
                        <!-- Total Score -->
                        <div>
                            <div class="text-blue-100 text-sm font-medium mb-1">Skor Total</div>
                            <div class="text-4xl font-bold">{{ number_format($evaluation->total_score, 2) }}</div>
                            <div class="text-blue-100 text-xs">dari 5.00</div>
                        </div>
                        
                        <!-- Rating Stars -->
                        <div>
                            <div class="text-blue-100 text-sm font-medium mb-1">Rating</div>
                            <div class="flex items-center space-x-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star text-yellow-300 {{ $i <= $evaluation->rating ? '' : 'opacity-30' }} text-2xl"></i>
                                @endfor
                            </div>
                            <div class="text-blue-100 text-xs mt-1">{{ $evaluation->rating }} dari 5 bintang</div>
                        </div>
                        
                        <!-- Evaluator -->
                        <div>
                            <div class="text-blue-100 text-sm font-medium mb-1">Dievaluasi Oleh</div>
                            <div class="text-lg font-semibold">{{ $evaluation->evaluator?->name ?? '-' }}</div>
                            <div class="text-blue-100 text-xs">{{ $evaluation->evaluated_at?->format('d M Y, H:i') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Overall Review -->
                @if($evaluation->ulasan)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-quote-left text-blue-500 mr-2"></i>
                            Kesimpulan Ulasan
                        </h3>
                        <p class="text-gray-700 text-base leading-relaxed italic">
                            "{{ $evaluation->ulasan }}"
                        </p>
                    </div>
                @endif

                <!-- Additional Notes -->
                @if($evaluation->catatan_tambahan)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-comment-dots text-gray-500 mr-2"></i>
                            Catatan Tambahan
                        </h3>
                        <p class="text-gray-700 text-base leading-relaxed">
                            {{ $evaluation->catatan_tambahan }}
                        </p>
                    </div>
                @endif

                <!-- Detailed Evaluation Table -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-lg font-bold text-gray-900">
                            <i class="fas fa-clipboard-list mr-2"></i>
                            Detail Penilaian Per Kriteria
                        </h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-100 border-b-2 border-gray-300">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-700 w-12">No</th>
                                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-700">Kriteria</th>
                                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-700">Sub-Kriteria</th>
                                    <th class="px-4 py-3 text-center text-sm font-bold text-gray-700 w-32">Penilaian</th>
                                    <th class="px-4 py-3 text-center text-sm font-bold text-gray-700 w-24">Skor</th>
                                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-700">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @php $no = 1; @endphp
                                @foreach($this->criteriaStructure as $kriteria => $subKriterias)
                                    @php
                                        $kriteriaDetails = $evaluationDetails->get($kriteria, collect());
                                    @endphp
                                    @foreach($subKriterias as $index => $subKriteria)
                                        @php
                                            $detail = $kriteriaDetails->firstWhere('sub_kriteria', $subKriteria);
                                        @endphp
                                        <tr class="hover:bg-gray-50 {{ $detail && $detail->penilaian >= 4 ? 'bg-green-50' : ($detail && $detail->penilaian <= 2 ? 'bg-red-50' : '') }}">
                                            <!-- No -->
                                            <td class="px-4 py-3 text-sm text-gray-900">
                                                @if($index === 0)
                                                    <span class="font-semibold">{{ $no++ }}</span>
                                                @endif
                                            </td>
                                            
                                            <!-- Kriteria -->
                                            <td class="px-4 py-3 text-sm">
                                                @if($index === 0)
                                                    <span class="font-bold text-gray-900">{{ $kriteria }}</span>
                                                @endif
                                            </td>
                                            
                                            <!-- Sub-Kriteria -->
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                {{ chr(97 + $index) }}. {{ $subKriteria }}
                                            </td>
                                            
                                            <!-- Rating Stars -->
                                            <td class="px-4 py-3">
                                                @if($detail)
                                                    <div class="flex items-center justify-center space-x-0.5">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <i class="fas fa-star {{ $i <= $detail->penilaian ? 'text-yellow-500' : 'text-gray-300' }} text-sm"></i>
                                                        @endfor
                                                    </div>
                                                @else
                                                    <span class="text-xs text-gray-400 text-center block">N/A</span>
                                                @endif
                                            </td>
                                            
                                            <!-- Score Number -->
                                            <td class="px-4 py-3 text-center">
                                                @if($detail)
                                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full text-sm font-bold
                                                        {{ $detail->penilaian >= 4 ? 'bg-green-100 text-green-700' : 
                                                           ($detail->penilaian == 3 ? 'bg-yellow-100 text-yellow-700' : 
                                                            'bg-red-100 text-red-700') }}">
                                                        {{ $detail->penilaian }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            
                                            <!-- Keterangan -->
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                @if($detail && $detail->keterangan)
                                                    <i class="fas fa-comment-alt text-blue-400 mr-1"></i>
                                                    {{ $detail->keterangan }}
                                                @else
                                                    <span class="text-gray-400 italic">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary Footer -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-600">
                                <i class="fas fa-info-circle mr-1"></i>
                                Total {{ $evaluation->details->count() }} kriteria dievaluasi
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="text-sm">
                                    <span class="text-gray-600">Rata-rata:</span>
                                    <span class="font-bold text-gray-900 text-lg ml-2">{{ number_format($evaluation->total_score, 2) }}</span>
                                    <span class="text-gray-600">/5.00</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star text-yellow-500 {{ $i <= $evaluation->rating ? '' : 'opacity-30' }} text-lg"></i>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pengiriman Info -->
                <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">
                        <i class="fas fa-truck mr-2"></i>
                        Informasi Pengiriman
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-sm font-medium text-gray-500">No. Pengiriman:</span>
                            <p class="text-base font-semibold text-gray-900">{{ $pengiriman->no_pengiriman }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Tanggal Kirim:</span>
                            <p class="text-base font-semibold text-gray-900">{{ $pengiriman->tanggal_kirim->format('d M Y') }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Total Nilai:</span>
                            <p class="text-base font-semibold text-gray-900">Rp {{ number_format($pengiriman->total_harga_kirim, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
