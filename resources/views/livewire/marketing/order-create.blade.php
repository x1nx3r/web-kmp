<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-6 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-shopping-cart text-blue-600"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            {{ $isEditing ? 'Edit Order' : 'Buat Order Baru' }}
                            @if($isEditing && $editingOrderNumber)
                                <span class="text-base font-normal text-gray-500">&middot; {{ $editingOrderNumber }}</span>
                            @endif
                        </h1>
                        <p class="text-gray-600">
                            {{ $isEditing ? 'Perbarui detail order multi-supplier dengan data terbaru' : 'Buat order pembelian untuk klien dengan sistem multi-supplier' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Status</div>
                        <div class="text-lg font-semibold text-blue-600">
                            {{ $currentStatus ? ucwords(str_replace('_', ' ', $currentStatus)) : 'Draft' }}
                        </div>
                    </div>
                    <a href="{{ $isEditing && $editingOrderId ? route('orders.show', $editingOrderId) : route('orders.index') }}" class="px-4 py-2 text-gray-600 hover:text-gray-900 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="p-4 sm:p-6 space-y-6 max-w-7xl mx-auto">
        {{-- Main Content Layout - More Symmetrical --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
            {{-- Left Section - Client & Order Info --}}
            <div class="space-y-6 order-2 lg:order-1">
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
                    :poNumber="$poNumber"
                    :poStartDate="$poStartDate"
                    :poEndDate="$poEndDate"
                    :poDocument="$poDocument"
                    :isEditing="$isEditing"
                    :existingPoDocumentName="$existingPoDocumentName"
                    :existingPoDocumentUrl="$existingPoDocumentUrl"
                />

                {{-- Action Buttons --}}
                <div class="bg-white rounded-lg border border-gray-200 p-6 sticky top-4">
                    <div class="space-y-4">
                        <div class="text-center">
                            <h3 class="text-lg font-semibold text-gray-900">Review & Submit</h3>
                            <p class="text-sm text-gray-600">Pastikan semua informasi sudah benar sebelum membuat order</p>
                        </div>
                        
                        @if(count($selectedOrderItems) > 0)
                            <div class="bg-gray-50 rounded-lg p-4 text-center">
                                <div class="text-sm text-gray-500 mb-1">Total Estimasi Order</div>
                                <div class="text-2xl font-bold text-green-600">Rp {{ number_format($totalAmount, 0, ',', '.') }}</div>
                                <div class="text-sm text-gray-500 mt-1">{{ count($selectedOrderItems) }} item dipilih</div>
                            </div>
                        @endif
                        
                        <div class="flex flex-col space-y-3">
                            <button 
                                type="button"
                                wire:click="{{ $isEditing ? 'updateOrder' : 'createOrder' }}"
                                class="w-full px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                {{ $this->canSubmit ? '' : 'disabled' }}
                            >
                                <i class="fas fa-save mr-2"></i>
                                {{ $isEditing ? 'Update Order' : 'Buat Order' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Section - Items Management & Summary --}}
            <div class="space-y-6 order-1 lg:order-2">
                {{-- Selected Items List --}}
                <x-order.items-list 
                    :selectedOrderItems="$selectedOrderItems"
                    :selectedKlien="$selectedKlien"
                    :selectedKlienCabang="$selectedKlienCabang"
                />

                {{-- Order Summary Table --}}
                <x-order.summary-table 
                    :selectedOrderItems="$selectedOrderItems"
                    :totalAmount="$totalAmount"
                    :totalMargin="$totalMargin"
                />
            </div>
        </div>
    </div>

    {{-- Add Item Modal V2 - Multi-Supplier --}}
    @if($showAddItemModal)
        <x-order.add-item-modal-v2 
            :availableMaterials="$availableMaterials"
            :currentMaterial="$currentMaterial"
            :currentQuantity="$currentQuantity"
            :currentSatuan="$currentSatuan"
            :currentHargaJual="$currentHargaJual"
            :currentSpesifikasi="$currentSpesifikasi"
            :currentCatatan="$currentCatatan"
            :autoSuppliers="$autoSuppliers"
            :bestMargin="$bestMargin"
            :recommendedPrice="$recommendedPrice"
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
