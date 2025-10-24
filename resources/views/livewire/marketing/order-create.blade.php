<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-200">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-shopping-cart text-blue-600"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Buat Order Baru</h1>
                        <p class="text-gray-600">Buat order pembelian untuk klien</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    @if(count($selectedOrderItems) > 0)
                        <div class="text-right">
                            <div class="text-sm text-gray-500">Total Estimasi</div>
                            <div class="text-xl font-bold text-green-600">{{ number_format($totalAmount, 0, ',', '.') }}</div>
                        </div>
                    @endif
                    <a href="{{ route('orders.index') }}" class="px-4 py-2 text-gray-600 hover:text-gray-900">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="p-6 space-y-6">
        {{-- Main Content Layout --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            {{-- Left Section - Client & Order Info --}}
            <div class="xl:col-span-1 space-y-6">
                {{-- Client Selection --}}
                <x-order.client-selector-livewire 
                    :kliens="$kliens"
                    :selectedKlien="$selectedKlien"
                    :selectedKlienCabang="$selectedKlienCabang"
                    :klienSearch="$klienSearch"
                    :selectedKota="$selectedKota"
                    :klienSort="$klienSort"
                    :availableCities="$availableCities"
                />

                {{-- Order Information --}}
                <x-order.info-section-livewire 
                    :tanggalOrder="$tanggalOrder"
                    :priority="$priority"
                    :catatan="$catatan"
                />

                {{-- Selected Items Summary --}}
                <x-order.items-list 
                    :selectedOrderItems="$selectedOrderItems"
                    :selectedKlien="$selectedKlien"
                    :selectedKlienCabang="$selectedKlienCabang"
                />
            </div>

            {{-- Right Section - Order Summary & Actions --}}
            <div class="xl:col-span-2">
                {{-- Order Summary Table --}}
                <x-order.summary-table 
                    :selectedOrderItems="$selectedOrderItems"
                    :totalAmount="$totalAmount"
                    :totalMargin="$totalMargin"
                />

                {{-- Action Buttons --}}
                <div class="mt-6 bg-white rounded-lg border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Review & Submit</h3>
                            <p class="text-sm text-gray-600">Pastikan semua informasi sudah benar sebelum membuat order</p>
                        </div>
                        <div class="flex space-x-3">
                            <button 
                                type="button"
                                wire:click="createOrder"
                                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                {{ (!$selectedKlienId || count($selectedOrderItems) === 0) ? 'disabled' : '' }}
                            >
                                <i class="fas fa-save mr-2"></i>
                                Buat Order
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Item Modal --}}
    @if($showAddItemModal)
        <x-order.add-item-modal 
            :availableMaterials="$availableMaterials"
            :suppliers="$this->getSuppliers()"
            :currentMaterial="$currentMaterial"
            :currentSupplier="$currentSupplier"
            :currentQuantity="$currentQuantity"
            :currentSatuan="$currentSatuan"
            :currentHargaSupplier="$currentHargaSupplier"
            :currentHargaJual="$currentHargaJual"
            :currentSpesifikasi="$currentSpesifikasi"
            :currentCatatan="$currentCatatan"
        />
    @endif

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" 
             class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
             class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif
</div>
