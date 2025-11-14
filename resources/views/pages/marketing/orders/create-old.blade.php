@extends('layouts.app')

@section('title', 'Buat Order Baru')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <form id="order-form" action="{{ route('marketing.orders.store') }}" method="POST" class="space-y-6">
        @csrf
        
        {{-- Header Component --}}
        <x-order.header />
        
        {{-- Client Selector Component --}}
        <x-order.client-selector :clients="$clients" />
        
        {{-- Order Info Section Component --}}
        <x-order.info-section />
        
        {{-- Order Details Component --}}
        <x-order.order-details :materials="$materials" :suppliers="$suppliers" />
        
        {{-- Action Buttons Component --}}
        <x-order.action-buttons />
    </form>
</div>

{{-- Order Create JavaScript Module --}}

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
                    <div class="flex items-center space-x-3">
                        <label class="inline-flex items-center space-x-2 text-sm text-gray-700">
                            <input type="checkbox" id="auto-populate-alternatives" class="form-checkbox h-4 w-4 text-green-600">
                            <span>Auto-populate alternatif (maks 5)</span>
                        </label>
                        <button type="button" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors" id="add-detail">
                            <i class="fas fa-plus mr-2"></i>
                            Tambah Item
                        </button>
                    </div>
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
                <!-- Alternatives placeholder: JS will populate supplier alternatives here when a material is selected -->
                <div class="mt-3">
                    <button type="button" class="text-sm text-blue-600 hover:underline show-alternatives-btn" style="display:none;">Tampilkan alternatif supplier</button>
                    <div class="alternatives-list mt-2 hidden bg-white border border-gray-100 rounded-lg p-3 text-sm"></div>
                </div>
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
    const autoPopulateToggle = document.getElementById('auto-populate-alternatives');

    // Add first detail on load
    addDetail();

    addDetailBtn.addEventListener('click', () => addDetail());

    function addDetail(prefill = null) {
        const node = template.content.cloneNode(true);
        const detailElement = node.querySelector('.order-detail-item');

        // Replace INDEX in all name attributes to current index
        detailElement.querySelectorAll('[name]').forEach(el => {
            const name = el.getAttribute('name');
            if (name && name.indexOf('INDEX') !== -1) {
                el.setAttribute('name', name.replace(/INDEX/g, detailIndex));
            }
        });

        // Set dataset and item number
        detailElement.querySelector('.item-number').textContent = detailIndex + 1;

        // Remove handler
        const removeBtn = detailElement.querySelector('.remove-detail');
        removeBtn.addEventListener('click', function() {
            if (orderDetailsContainer.children.length > 1) {
                detailElement.remove();
                updateItemNumbers();
            }
        });

        // Margin listeners
        const qtyInput = detailElement.querySelector('.qty-input');
        const supplierPrice = detailElement.querySelector('.supplier-price');
        const sellingPrice = detailElement.querySelector('.selling-price');
        [qtyInput, supplierPrice, sellingPrice].forEach(input => {
            input.addEventListener('input', function() { calculateMargin(detailElement); });
        });

        // Material change handler — show inline alternatives (collapsible), inspired by Penawaran UI
        const materialSelect = detailElement.querySelector('.material-select');
        const showAlternativesBtn = detailElement.querySelector('.show-alternatives-btn');
        const alternativesList = detailElement.querySelector('.alternatives-list');

        materialSelect.addEventListener('change', async function(e) {
            const materialId = e.target.value;
            // Clear previous alternatives
            alternativesList.innerHTML = '';
            alternativesList.classList.add('hidden');
            showAlternativesBtn.style.display = 'none';
            detailElement.dataset.populated = '';

            if (!materialId) return;

            try {
                const res = await fetch(`/orders/material/${materialId}/suppliers`);
                if (!res.ok) return;
                const payload = await res.json();
                const suppliers = Array.isArray(payload.data) ? payload.data : [];
                if (suppliers.length === 0) return;

                // Highlight cheapest supplier but don't auto-add rows — show alternatives collapsible
                const cheapest = suppliers[0];
                const supplierSelect = detailElement.querySelector('.supplier-select');
                if (cheapest.supplier_id) supplierSelect.value = cheapest.supplier_id;
                const supplierPriceInput = detailElement.querySelector('.supplier-price');
                supplierPriceInput.value = cheapest.price || '';
                const satuanInput = detailElement.querySelector('input[name$="[satuan]"]');
                if (satuanInput && cheapest.satuan) satuanInput.value = cheapest.satuan;

                // Build alternatives list UI
                suppliers.forEach((s, idx) => {
                    const item = document.createElement('div');
                    item.className = 'flex items-center justify-between py-2 border-b border-gray-100';

                    const left = document.createElement('div');
                    left.innerHTML = `<div class="font-semibold">${escapeHtml(s.supplier_name || '—')}</div>
                                      <div class="text-xs text-gray-500">PIC: ${escapeHtml(s.pic_name || '-')} · ${escapeHtml(s.satuan || '-')}</div>`;

                    const right = document.createElement('div');
                    right.className = 'flex items-center space-x-3';

                    const price = document.createElement('div');
                    price.className = 'text-sm text-gray-800 font-medium';
                    price.textContent = formatCurrency(s.price || 0);

                    const useBtn = document.createElement('button');
                    useBtn.type = 'button';
                    useBtn.className = 'px-3 py-1 bg-blue-600 text-white rounded text-xs';
                    useBtn.textContent = (idx === 0 ? 'Gunakan (termurah)' : 'Gunakan');
                    useBtn.addEventListener('click', function() {
                        // populate main row with this supplier
                        if (s.supplier_id) supplierSelect.value = s.supplier_id;
                        if (typeof s.price !== 'undefined') supplierPriceInput.value = s.price;
                        if (s.satuan && satuanInput) satuanInput.value = s.satuan;
                        // collapse alternatives after selection
                        alternativesList.classList.add('hidden');
                    });

                    right.appendChild(price);
                    right.appendChild(useBtn);

                    item.appendChild(left);
                    item.appendChild(right);

                    alternativesList.appendChild(item);
                });

                // Show alternatives toggle
                showAlternativesBtn.style.display = 'inline-block';
                showAlternativesBtn.onclick = function() {
                    alternativesList.classList.toggle('hidden');
                    showAlternativesBtn.textContent = alternativesList.classList.contains('hidden') ? 'Tampilkan alternatif supplier' : 'Sembunyikan alternatif supplier';
                };

                detailElement.dataset.populated = '1';
            } catch (err) {
                console.error('Failed to fetch suppliers for material', err);
            }
        });

        // Apply prefill values if provided
        if (prefill) {
            if (prefill.materialId) {
                detailElement.querySelector('.material-select').value = prefill.materialId;
            }
            if (prefill.supplierId) {
                detailElement.querySelector('.supplier-select').value = prefill.supplierId;
            }
            if (typeof prefill.harga_supplier !== 'undefined') {
                detailElement.querySelector('.supplier-price').value = prefill.harga_supplier;
            }
            if (prefill.satuan) {
                const s = detailElement.querySelector('input[name$="[satuan]"]');
                if (s) s.value = prefill.satuan;
            }
            detailElement.dataset.populated = '1';
        }

        orderDetailsContainer.appendChild(detailElement);
        detailIndex++;
    }

    function updateItemNumbers() {
        const items = orderDetailsContainer.querySelectorAll('.order-detail-item');
        items.forEach((item, index) => { item.querySelector('.item-number').textContent = index + 1; });
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

// helper functions used by the inline alternatives UI
function formatCurrency(value) {
    try {
        return 'Rp ' + Number(value).toLocaleString('id-ID');
    } catch (e) {
        return 'Rp ' + value;
    }
}

function escapeHtml(unsafe) {
    return String(unsafe)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
}
</script>
@endpush
@endsection