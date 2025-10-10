{{-- Price History Modal --}}
<div 
    x-show="showPriceHistoryModal" 
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
>
    {{-- Backdrop --}}
    <div 
        x-show="showPriceHistoryModal"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black bg-opacity-30"
        @click="closePriceHistoryModal()"
    ></div>

    {{-- Modal --}}
    <div class="flex items-center justify-center min-h-screen px-4">
        <div 
            x-show="showPriceHistoryModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
            class="relative w-full max-w-3xl bg-white rounded-xl shadow-xl"
            @click.stop
        >
            {{-- Header --}}
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">
                        Riwayat Harga Material
                    </h3>
                    <p class="text-sm text-gray-600 mt-1" x-text="currentMaterial ? currentMaterial.nama : ''"></p>
                </div>
                <button 
                    @click="closePriceHistoryModal()"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            {{-- Content --}}
            <div class="p-6">
                {{-- Current Price Info --}}
                <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="font-medium text-gray-900">Harga Saat Ini</h4>
                            <p class="text-sm text-gray-600 mt-1">
                                <span x-text="currentMaterial ? formatPrice(currentMaterial.harga_approved) : 'Belum disetujui'"></span>
                                <span x-text="currentMaterial ? `/${currentMaterial.satuan}` : ''"></span>
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-gray-500">Disetujui pada</div>
                            <div class="text-sm font-medium" x-text="currentMaterial?.approved_at ? formatDate(currentMaterial.approved_at) : 'Belum disetujui'"></div>
                        </div>
                    </div>
                </div>

                {{-- Loading State --}}
                <div x-show="loading" class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-3xl text-blue-500 mb-4"></i>
                    <p class="text-gray-600">Memuat riwayat harga...</p>
                </div>

                {{-- Price History Table --}}
                <div x-show="!loading && priceHistory.length > 0" class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Tanggal</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Harga Lama</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Harga Baru</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Perubahan</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Keterangan</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Diubah Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="history in priceHistory" :key="history.id">
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3 px-4 text-sm text-gray-900" x-text="formatDate(history.tanggal_perubahan)"></td>
                                    <td class="py-3 px-4 text-sm text-gray-600">
                                        <span x-text="history.harga_lama ? formatPrice(history.harga_lama) : 'N/A'"></span>
                                    </td>
                                    <td class="py-3 px-4 text-sm font-medium text-gray-900">
                                        <span x-text="formatPrice(history.harga_approved_baru)"></span>
                                    </td>
                                    <td class="py-3 px-4 text-sm">
                                        <div class="flex items-center">
                                            <span 
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                                :class="{
                                                    'bg-green-100 text-green-800': history.tipe_perubahan === 'turun',
                                                    'bg-red-100 text-red-800': history.tipe_perubahan === 'naik',
                                                    'bg-blue-100 text-blue-800': history.tipe_perubahan === 'tetap',
                                                    'bg-gray-100 text-gray-800': history.tipe_perubahan === 'awal'
                                                }"
                                            >
                                                <span x-text="history.change_icon || '➡️'"></span>
                                                <span class="ml-1" x-text="history.tipe_perubahan"></span>
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            <span x-text="history.formatted_selisih_harga"></span>
                                            <span x-text="`(${history.persentase_perubahan}%)`"></span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-600">
                                        <span x-text="history.keterangan || '-'"></span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-600">
                                        <span x-text="history.updated_by_marketing?.name || 'System'"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Empty State --}}
                <div x-show="!loading && priceHistory.length === 0" class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chart-line text-2xl text-gray-400"></i>
                    </div>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Riwayat Harga</h4>
                    <p class="text-sm text-gray-600">
                        Riwayat perubahan harga akan muncul di sini setelah ada update harga.
                    </p>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end px-6 py-4 bg-gray-50 rounded-b-xl">
                <button 
                    @click="closePriceHistoryModal()"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white hover:bg-gray-100 border border-gray-300 rounded-lg transition-colors"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>