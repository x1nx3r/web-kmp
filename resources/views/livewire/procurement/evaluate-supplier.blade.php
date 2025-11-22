<div>
    <div class="min-h-screen bg-gray-50 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Form Evaluasi Supplier</h1>
                        <p class="mt-1 text-sm text-gray-600">
                            Evaluasi untuk Pengiriman: <span class="font-semibold">{{ $pengiriman->no_pengiriman }}</span>
                        </p>
                    </div>
                    <a href="{{ route('orders.show', $pengiriman->purchase_order_id) }}" 
                       class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors text-sm">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                </div>
            </div>

            <!-- Pengiriman Info -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
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
                    <span class="text-sm font-medium text-gray-500">Supplier:</span>
                    @php
                        $supplier = $pengiriman->details->first()?->bahanBakuSupplier?->supplier;
                    @endphp
                    <p class="text-base font-semibold text-gray-900">{{ $supplier?->nama ?? '-' }}</p>
                </div>
            </div>
        </div>

        @if (session()->has('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i>
                {{ session('error') }}
            </div>
        @endif

        <!-- Evaluation Form -->
        <form wire:submit.prevent="simpanEvaluasi">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-blue-50 border-b border-blue-200">
                    <h2 class="text-lg font-bold text-blue-900">
                        <i class="fas fa-clipboard-check mr-2"></i>
                        FORM EVALUASI SUPPLIER
                    </h2>
                    <p class="text-sm text-blue-700 mt-1">Berikan penilaian 1-5 untuk setiap kriteria (1=Sangat Buruk, 5=Sangat Baik)</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b-2 border-gray-300">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-bold text-gray-700 w-12">No</th>
                                <th class="px-4 py-3 text-left text-sm font-bold text-gray-700">Kriteria</th>
                                <th class="px-4 py-3 text-left text-sm font-bold text-gray-700">Sub-Kriteria</th>
                                <th class="px-4 py-3 text-center text-sm font-bold text-gray-700 w-40">Penilaian (1–5)</th>
                                <th class="px-4 py-3 text-left text-sm font-bold text-gray-700">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @php $no = 1; @endphp
                            @foreach($this->criteriaStructure as $kriteria => $subKriterias)
                                @foreach($subKriterias as $index => $subKriteria)
                                    <tr class="hover:bg-gray-50">
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
                                        
                                        <!-- Penilaian (1-5 Radio Buttons) -->
                                        <td class="px-4 py-3">
                                            @php
                                                $radioName = 'penilaian_' . str_replace(' ', '_', $kriteria) . '_' . str_replace(' ', '_', $subKriteria);
                                            @endphp
                                            <div class="flex items-center justify-center space-x-3">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <label class="flex items-center cursor-pointer group">
                                                        <input type="radio" 
                                                               name="{{ $radioName }}"
                                                               wire:model="evaluasi.{{ $kriteria }}.{{ $subKriteria }}.penilaian" 
                                                               value="{{ $i }}"
                                                               class="w-4 h-4 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                                        <span class="ml-1 text-sm font-medium text-gray-700 group-hover:text-blue-600">{{ $i }}</span>
                                                    </label>
                                                @endfor
                                            </div>
                                            @error("evaluasi.{$kriteria}.{$subKriteria}.penilaian")
                                                <p class="text-xs text-red-600 mt-1 text-center">{{ $message }}</p>
                                            @enderror
                                        </td>
                                        
                                        <!-- Keterangan -->
                                        <td class="px-4 py-3">
                                            <input type="text" 
                                                   wire:model="evaluasi.{{ $kriteria }}.{{ $subKriteria }}.keterangan"
                                                   placeholder="Catatan (opsional)"
                                                   class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Catatan Tambahan -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-comment-dots mr-1"></i>
                        Catatan Tambahan (Opsional)
                    </label>
                    <textarea wire:model="catatanTambahan" 
                              rows="3"
                              placeholder="Tuliskan catatan atau komentar tambahan mengenai evaluasi ini..."
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        Pastikan semua penilaian telah diisi dengan benar
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('orders.show', $pengiriman->purchase_order_id) }}" 
                           class="px-6 py-2.5 text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg transition-colors font-medium">
                            Batal
                        </a>
                        <button type="submit" 
                                class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors font-medium shadow-sm">
                            <i class="fas fa-save mr-2"></i>
                            Simpan Evaluasi
                        </button>
                    </div>
                </div>
            </div>
        </form>

        
    </div>
</div>
