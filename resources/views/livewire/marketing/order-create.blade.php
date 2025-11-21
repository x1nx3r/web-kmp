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
                        
                        @if($selectedMaterial && $quantity > 0 && $hargaJual > 0)
                            <div class="bg-gray-50 rounded-lg p-4 text-center">
                                <div class="text-sm text-gray-500 mb-1">Total Estimasi Order</div>
                                <div class="text-2xl font-bold text-green-600">Rp {{ number_format($totalAmount, 0, ',', '.') }}</div>
                                <div class="text-sm text-gray-500 mt-1">{{ number_format($quantity, 2) }} {{ $satuan }}</div>
                                @if($totalMargin > 0)
                                    <div class="text-sm text-green-600 mt-1">Margin: Rp {{ number_format($totalMargin, 0, ',', '.') }}</div>
                                @endif
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

            {{-- Right Section - Material Selection & Summary --}}
            <div class="space-y-6 order-1 lg:order-2">
                {{-- Material Selection --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="border-b border-gray-200 p-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-cube text-purple-600 text-sm"></i>
                            </div>
                            <h3 class="font-semibold text-gray-900">Pilih Material</h3>
                        </div>
                    </div>
                    <div class="p-4 space-y-4">
                        {{-- Material Dropdown --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Material <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.live="selectedMaterial" wire:change="selectMaterial($event.target.value)"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                    {{ (!$selectedKlien || !$selectedKlienCabang) ? 'disabled' : '' }}>
                                <option value="">{{ (!$selectedKlien || !$selectedKlienCabang) ? 'Pilih klien terlebih dahulu' : 'Pilih Material' }}</option>
                                @if($selectedKlien && $selectedKlienCabang)
                                    @foreach($availableMaterials as $material)
                                        <option value="{{ $material['id'] }}">{{ $material['nama'] }} ({{ $material['satuan'] }})</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        {{-- PO Material Name --}}
                        @if($selectedMaterial)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Nama Raw Material Sesuai Surat PO
                                    <span class="text-xs text-gray-500 font-normal ml-1">(opsional)</span>
                                </label>
                                <input 
                                    type="text" 
                                    wire:model="namaMaterialPO"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                    placeholder="Contoh: Gula Pasir Premium Grade A"
                                >
                                <p class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Tulis nama material persis seperti tertera di dokumen PO klien
                                </p>
                            </div>
                        @endif

                        {{-- Quantity and Price --}}
                        @if($selectedMaterial)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Quantity <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" wire:model.live="quantity" step="0.01" min="0.01"
                                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                           placeholder="0">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Harga Jual <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" wire:model.live="hargaJual" step="0.01" min="0"
                                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                           placeholder="0">
                                </div>
                            </div>

                            {{-- Specifications and Notes --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Spesifikasi Khusus</label>
                                    <textarea wire:model="spesifikasiKhusus" rows="2"
                                              class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                              placeholder="Spesifikasi tambahan (opsional)"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                                    <textarea wire:model="catatanMaterial" rows="2"
                                              class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                              placeholder="Catatan tambahan (opsional)"></textarea>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Supplier Information --}}
                @if($selectedMaterial && !empty($autoSuppliers))
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="border-b border-gray-200 p-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-truck text-green-600 text-sm"></i>
                                </div>
                                <h3 class="font-semibold text-gray-900">Supplier Tersedia ({{ count($autoSuppliers) }})</h3>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-60 overflow-y-auto">
                                @foreach($autoSuppliers as $index => $supplier)
                                    <div class="border rounded-lg p-3 {{ $supplier['is_recommended'] ? 'border-green-500 bg-green-50' : 'border-gray-200 bg-white' }}">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="font-medium text-sm {{ $supplier['is_recommended'] ? 'text-green-900' : 'text-gray-900' }}">
                                                {{ $supplier['supplier_name'] }}
                                                @if($supplier['is_recommended'])
                                                    <span class="ml-1 px-2 py-0.5 bg-green-200 text-green-800 text-xs rounded-full">Best</span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-600">#{{{ $index + 1 }}}</div>
                                        </div>
                                        <div class="text-xs text-gray-600 mb-1">
                                            <i class="fas fa-map-marker-alt mr-1"></i>
                                            {{ $supplier['supplier_location'] ?: 'Location not specified' }}
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <div class="text-sm font-semibold">Rp {{ number_format($supplier['harga_supplier'], 0, ',', '.') }}</div>
                                                <div class="text-xs text-gray-500">per {{ $supplier['satuan'] }}</div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-xs {{ $supplier['margin_percentage'] >= 20 ? 'text-green-600' : ($supplier['margin_percentage'] >= 10 ? 'text-yellow-600' : 'text-red-600') }}">
                                                    {{ number_format($supplier['margin_percentage'], 1) }}%
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    Stock: {{ number_format($supplier['stok'], 0) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- No modal needed for single material selection --}}

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
