{{-- Material Management Modal --}}
<div 
    x-show="showMaterialModal" 
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
>
    {{-- Backdrop --}}
    <div 
        x-show="showMaterialModal"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black bg-opacity-30"
        @click="closeMaterialModal()"
    ></div>

    {{-- Modal --}}
    <div class="flex items-center justify-center min-h-screen px-4">
        <div 
            x-show="showMaterialModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
            class="relative w-full max-w-lg bg-white rounded-xl shadow-xl"
            @click.stop
        >
            {{-- Header --}}
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    <span x-text="currentMaterial ? 'Edit Material' : 'Tambah Material Baru'"></span>
                </h3>
                <button 
                    @click="closeMaterialModal()"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            {{-- Content --}}
            <div class="p-6">
                {{-- Client Info --}}
                <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                    <div class="flex items-center text-sm text-blue-800">
                        <i class="fas fa-building mr-2"></i>
                        <span x-text="currentKlien ? `${currentKlien.nama} - ${currentKlien.cabang}` : ''"></span>
                    </div>
                </div>

                {{-- Form --}}
                <form @submit.prevent="saveMaterial()">
                    {{-- Material Name --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Material <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text"
                            x-model="materialForm.nama"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Contoh: Tepung Terigu Premium"
                            required
                        >
                        <div x-show="errors.nama" class="mt-1 text-sm text-red-600" x-text="errors.nama?.[0]"></div>
                    </div>

                    {{-- Unit --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Satuan <span class="text-red-500">*</span>
                        </label>
                        <select 
                            x-model="materialForm.satuan"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required
                        >
                            <option value="kg">Kilogram (kg)</option>
                            <option value="liter">Liter</option>
                            <option value="gram">Gram</option>
                            <option value="ml">Milliliter (ml)</option>
                            <option value="pcs">Pieces (pcs)</option>
                            <option value="pack">Pack</option>
                            <option value="box">Box</option>
                            <option value="ton">Ton</option>
                        </select>
                        <div x-show="errors.satuan" class="mt-1 text-sm text-red-600" x-text="errors.satuan?.[0]"></div>
                    </div>

                    {{-- Approved Price --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Harga yang Disetujui
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 text-sm">Rp</span>
                            </div>
                            <input 
                                type="number"
                                x-model="materialForm.harga_approved"
                                class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="0"
                                min="0"
                                step="0.01"
                            >
                        </div>
                        <div x-show="errors.harga_approved" class="mt-1 text-sm text-red-600" x-text="errors.harga_approved?.[0]"></div>
                        <div class="mt-1 text-xs text-gray-500">
                            <span x-text="`Per ${materialForm.satuan || 'unit'}`"></span>
                        </div>
                    </div>

                    {{-- Specifications --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Spesifikasi Khusus
                        </label>
                        <textarea 
                            x-model="materialForm.spesifikasi"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Spesifikasi khusus untuk klien ini..."
                        ></textarea>
                        <div x-show="errors.spesifikasi" class="mt-1 text-sm text-red-600" x-text="errors.spesifikasi?.[0]"></div>
                    </div>

                    {{-- Status --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <select 
                            x-model="materialForm.status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                        <div x-show="errors.status" class="mt-1 text-sm text-red-600" x-text="errors.status?.[0]"></div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end space-x-3">
                        <button 
                            type="button"
                            @click="closeMaterialModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit"
                            :disabled="loading"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 rounded-lg transition-colors"
                        >
                            <span x-show="!loading" x-text="currentMaterial ? 'Update' : 'Simpan'"></span>
                            <span x-show="loading">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Menyimpan...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>