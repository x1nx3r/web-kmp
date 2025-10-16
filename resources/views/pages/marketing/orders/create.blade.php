@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="px-6 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-plus text-blue-600 text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Buat Order Baru</h1>
                        <nav class="text-sm text-gray-600">
                            <a href="{{ route('orders.index') }}" class="hover:text-blue-600">Order</a>
                            <span class="mx-2">/</span>
                            <span>Buat Baru</span>
                        </nav>
                    </div>
                </div>
                <a href="{{ route('orders.index') }}" class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="p-6">

        <!-- Form -->
        <form action="{{ route('orders.store') }}" method="POST" id="order-form" class="space-y-6">
            @csrf
            
            <!-- Basic Info Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-3"></i>
                        Informasi Umum
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="klien_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Klien <span class="text-red-500">*</span>
                            </label>
                            <select name="klien_id" id="klien_id" 
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('klien_id') border-red-500 @enderror" 
                                    required>
                                <option value="">Pilih Klien</option>
                                @foreach($kliens as $klien)
                                    <option value="{{ $klien->id }}" {{ old('klien_id') == $klien->id ? 'selected' : '' }}>
                                        {{ $klien->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('klien_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="tanggal_order" class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Order <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggal_order" id="tanggal_order" 
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('tanggal_order') border-red-500 @enderror" 
                                   value="{{ old('tanggal_order', date('Y-m-d')) }}" required>
                            @error('tanggal_order')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                                Prioritas <span class="text-red-500">*</span>
                            </label>
                            <select name="priority" id="priority" 
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('priority') border-red-500 @enderror" 
                                    required>
                                <option value="">Pilih Prioritas</option>
                                <option value="rendah" {{ old('priority') == 'rendah' ? 'selected' : '' }}>Rendah</option>
                                <option value="normal" {{ old('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="tinggi" {{ old('priority') == 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                                <option value="mendesak" {{ old('priority') == 'mendesak' ? 'selected' : '' }}>Mendesak</option>
                            </select>
                            @error('priority')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="catatan" class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                            <textarea name="catatan" id="catatan" rows="3" 
                                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('catatan') border-red-500 @enderror" 
                                      placeholder="Catatan tambahan untuk order ini">{{ old('catatan') }}</textarea>
                            @error('catatan')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Details Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-list text-blue-600 mr-3"></i>
                        Detail Order
                    </h3>
                    <button type="button" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors" id="add-detail">
                        <i class="fas fa-plus mr-2"></i>
                        Tambah Item
                    </button>
                </div>
                <div class="p-6">
                    <div id="order-details">
                        <!-- Dynamic order details will be added here -->
                    </div>
                    
                    @error('order_details')
                        <div class="mt-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Submit Button -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6">
                    <div class="flex space-x-3">
                        <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                            <i class="fas fa-save mr-2"></i>
                            Simpan Order
                        </button>
                        <a href="{{ route('orders.index') }}" class="px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition-colors">
                            Batal
                        </a>
                    </div>
                </div>
            </div>
    </form>
</div>

<!-- Order Detail Template -->
<template id="order-detail-template">
    <div class="order-detail-item border border-gray-200 rounded-lg p-4 mb-4 bg-gray-50">
        <div class="flex justify-between items-start mb-4">
            <h4 class="text-lg font-semibold text-gray-900">Item Order #<span class="item-number">1</span></h4>
            <button type="button" class="px-3 py-1 text-red-600 border border-red-300 hover:bg-red-50 rounded-lg transition-colors remove-detail">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Material <span class="text-red-500">*</span></label>
                <select name="order_details[INDEX][bahan_baku_klien_id]" class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent material-select" required>
                    <option value="">Pilih Material</option>
                    @foreach($materials as $material)
                        <option value="{{ $material->id }}">
                            {{ $material->nama }} - {{ $material->klien->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Supplier <span class="text-red-500">*</span></label>
                <select name="order_details[INDEX][supplier_id]" class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent supplier-select" required>
                    <option value="">Pilih Supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Qty <span class="text-red-500">*</span></label>
                <input type="number" name="order_details[INDEX][qty]" class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent qty-input" 
                       step="0.01" min="0.01" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Satuan <span class="text-red-500">*</span></label>
                <input type="text" name="order_details[INDEX][satuan]" class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                       placeholder="kg, ton, box, dll" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Harga Supplier <span class="text-red-500">*</span></label>
                <input type="number" name="order_details[INDEX][harga_supplier]" class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent supplier-price" 
                       step="0.01" min="0" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Harga Jual <span class="text-red-500">*</span></label>
                <input type="number" name="order_details[INDEX][harga_jual]" class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent selling-price" 
                       step="0.01" min="0" required>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Spesifikasi Khusus</label>
                <textarea name="order_details[INDEX][spesifikasi_khusus]" class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" rows="2" 
                          placeholder="Spesifikasi khusus untuk item ini"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                <textarea name="order_details[INDEX][catatan]" class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" rows="2" 
                          placeholder="Catatan untuk item ini"></textarea>
            </div>
        </div>
        
        <!-- Margin Info -->
        <div class="mt-4">
            <div class="margin-info p-3 bg-gray-100 rounded-lg">
                <p class="text-sm text-gray-600">
                    <strong>Margin:</strong> 
                    <span class="margin-amount font-semibold">Rp 0</span> 
                    (<span class="margin-percentage font-semibold">0%</span>)
                </p>
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let detailIndex = 0;
    
    const addDetailBtn = document.getElementById('add-detail');
    const orderDetailsContainer = document.getElementById('order-details');
    const template = document.getElementById('order-detail-template');
    
    // Add first detail on load
    addDetail();
    
    addDetailBtn.addEventListener('click', addDetail);
    
    function addDetail() {
        const clone = template.content.cloneNode(true);
        
        // Replace INDEX placeholder with actual index
        const html = clone.firstElementChild.outerHTML.replace(/INDEX/g, detailIndex);
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        const detailElement = tempDiv.firstElementChild;
        
        // Update item number
        detailElement.querySelector('.item-number').textContent = detailIndex + 1;
        
        // Add event listeners
        const removeBtn = detailElement.querySelector('.remove-detail');
        removeBtn.addEventListener('click', function() {
            if (orderDetailsContainer.children.length > 1) {
                detailElement.remove();
                updateItemNumbers();
            }
        });
        
        // Add margin calculation listeners
        const qtyInput = detailElement.querySelector('.qty-input');
        const supplierPrice = detailElement.querySelector('.supplier-price');
        const sellingPrice = detailElement.querySelector('.selling-price');
        
        [qtyInput, supplierPrice, sellingPrice].forEach(input => {
            input.addEventListener('input', function() {
                calculateMargin(detailElement);
            });
        });
        
        orderDetailsContainer.appendChild(detailElement);
        detailIndex++;
    }
    
    function updateItemNumbers() {
        const items = orderDetailsContainer.querySelectorAll('.order-detail-item');
        items.forEach((item, index) => {
            item.querySelector('.item-number').textContent = index + 1;
        });
    }
    
    function calculateMargin(element) {
        const qty = parseFloat(element.querySelector('.qty-input').value) || 0;
        const supplierPrice = parseFloat(element.querySelector('.supplier-price').value) || 0;
        const sellingPrice = parseFloat(element.querySelector('.selling-price').value) || 0;
        
        const totalCost = qty * supplierPrice;
        const totalRevenue = qty * sellingPrice;
        const margin = totalRevenue - totalCost;
        const marginPercentage = totalCost > 0 ? (margin / totalCost * 100) : 0;
        
        const marginAmount = element.querySelector('.margin-amount');
        const marginPercentageSpan = element.querySelector('.margin-percentage');
        
        marginAmount.textContent = 'Rp ' + margin.toLocaleString('id-ID');
        marginPercentageSpan.textContent = marginPercentage.toFixed(1) + '%';
        
        // Color coding
        const marginInfo = element.querySelector('.margin-info');
        marginInfo.className = 'margin-info p-2 rounded ';
        
        if (marginPercentage >= 20) {
            marginInfo.classList.add('bg-success-subtle', 'text-success');
        } else if (marginPercentage >= 10) {
            marginInfo.classList.add('bg-warning-subtle', 'text-warning');
        } else if (marginPercentage >= 0) {
            marginInfo.classList.add('bg-info-subtle', 'text-info');
        } else {
            marginInfo.classList.add('bg-danger-subtle', 'text-danger');
        }
    }
});
</script>
@endpush
@endsection