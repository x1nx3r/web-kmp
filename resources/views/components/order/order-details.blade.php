@props(['orderDetails' => [], 'materials' => [], 'suppliers' => []])

{{-- Order Details --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="border-b border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-shopping-cart text-green-600 text-sm"></i>
                </div>
                <h3 class="font-semibold text-gray-900">Detail Order</h3>
                <span class="ml-3 px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-full" id="items-count">
                    0 items
                </span>
            </div>
            <button type="button" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors" id="add-detail">
                <i class="fas fa-plus mr-2"></i>
                Tambah Item
            </button>
        </div>
    </div>

    {{-- Order Items Container --}}
    <div class="p-4">
        <div id="order-details" class="space-y-4">
            {{-- Items will be added here dynamically --}}
        </div>
        
        {{-- Empty State --}}
        <div id="empty-state" class="text-center py-12 text-gray-500">
            <i class="fas fa-shopping-cart text-4xl mb-4"></i>
            <h4 class="text-lg font-medium mb-2">Belum ada item order</h4>
            <p class="text-sm mb-4">Klik tombol "Tambah Item" untuk menambahkan material</p>
            <button type="button" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors" onclick="document.getElementById('add-detail').click()">
                <i class="fas fa-plus mr-2"></i>
                Tambah Item Pertama
            </button>
        </div>

        {{-- Order Summary --}}
        <div id="order-summary" class="mt-6 pt-6 border-t border-gray-200 hidden">
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-3">Ringkasan Order</h4>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Total Items:</span>
                        <span class="font-medium ml-2" id="summary-items">0</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Estimasi Total:</span>
                        <span class="font-medium ml-2" id="summary-total">Rp 0</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Total Cost:</span>
                        <span class="font-medium ml-2" id="summary-cost">Rp 0</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Estimasi Margin:</span>
                        <span class="font-medium ml-2" id="summary-margin">Rp 0 (0%)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Order Detail Template --}}
<template id="order-detail-template">
    <div class="order-detail-item bg-gray-50 border border-gray-200 rounded-lg p-4">
        <div class="flex justify-between items-start mb-4">
            <h4 class="text-lg font-semibold text-gray-900 flex items-center">
                <span class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-sm font-bold mr-2 item-number">1</span>
                Item Order
            </h4>
            <button type="button" class="px-3 py-1 text-red-600 border border-red-300 hover:bg-red-50 rounded-lg transition-colors remove-detail">
                <i class="fas fa-trash text-sm"></i>
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            {{-- Material Selection --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Material <span class="text-red-500">*</span>
                </label>
                <select name="order_details[INDEX][bahan_baku_klien_id]" class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent material-select" required>
                    <option value="">Pilih Material</option>
                    @foreach($materials as $material)
                        <option value="{{ $material->id }}">
                            {{ $material->nama }} - {{ $material->klien->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Supplier Selection --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Supplier <span class="text-red-500">*</span>
                </label>
                <select name="order_details[INDEX][supplier_id]" class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent supplier-select" required>
                    <option value="">Pilih Supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->nama }}</option>
                    @endforeach
                </select>
                
                {{-- Supplier Alternatives --}}
                <div class="mt-3">
                    <button type="button" class="text-sm text-blue-600 hover:underline show-alternatives-btn" style="display:none;">
                        <i class="fas fa-eye mr-1"></i>
                        Lihat alternatif supplier
                    </button>
                    <div class="alternatives-list mt-2 hidden bg-white border border-gray-100 rounded-lg p-3 text-sm shadow-sm"></div>
                </div>
            </div>
        </div>
        
        {{-- Quantity and Pricing --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Qty <span class="text-red-500">*</span>
                </label>
                <input type="number" name="order_details[INDEX][qty]" 
                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent qty-input" 
                       step="0.01" min="0.01" placeholder="0" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Satuan <span class="text-red-500">*</span>
                </label>
                <input type="text" name="order_details[INDEX][satuan]" 
                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                       placeholder="kg, ton, box, dll" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Harga Supplier <span class="text-red-500">*</span>
                </label>
                <input type="number" name="order_details[INDEX][harga_supplier]" 
                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent supplier-price" 
                       step="0.01" min="0" placeholder="0" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Harga Jual <span class="text-red-500">*</span>
                </label>
                <input type="number" name="order_details[INDEX][harga_jual]" 
                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent selling-price" 
                       step="0.01" min="0" placeholder="0" required>
            </div>
        </div>
        
        {{-- Additional Info --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Spesifikasi Khusus</label>
                <textarea name="order_details[INDEX][spesifikasi_khusus]" 
                          class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                          rows="2" placeholder="Spesifikasi khusus untuk item ini"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                <textarea name="order_details[INDEX][catatan]" 
                          class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                          rows="2" placeholder="Catatan untuk item ini"></textarea>
            </div>
        </div>
        
        {{-- Margin Info --}}
        <div class="margin-info p-3 bg-gray-100 rounded-lg">
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700">Margin:</span>
                <div class="text-right">
                    <span class="margin-amount font-semibold text-gray-900">Rp 0</span>
                    <span class="margin-percentage text-sm text-gray-600 ml-2">(0%)</span>
                </div>
            </div>
        </div>
    </div>
</template>