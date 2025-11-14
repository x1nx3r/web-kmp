@extends('layouts.app')

@section('title', 'Buat Order Baru')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <form id="order-form" action="{{ route('orders.store') }}" method="POST" class="space-y-6">
        @csrf
        
        {{-- Header Component --}}
        <x-order.header />
        
        {{-- Client Selector Component --}}
        <x-order.client-selector :kliens="$kliens" />
        
        {{-- Order Info Section Component --}}
        <x-order.info-section />
        
        {{-- Order Details Component --}}
        <x-order.order-details :materials="$materials" :suppliers="$suppliers" />
        
        {{-- Action Buttons Component --}}
        <x-order.action-buttons />
    </form>
</div>

{{-- Order Create JavaScript Module --}}
<script>
class OrderCreateManager {
    constructor() {
        this.detailCount = 0;
        this.materials = @json($materials);
        this.suppliers = @json($suppliers);
        console.log('OrderCreateManager initialized');
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.updateItemsCount();
        this.updateSummary();
    }
    
    setupEventListeners() {
        // Add detail button
        document.getElementById('add-detail')?.addEventListener('click', () => this.addOrderDetail());
        
        // Client selection handling
        document.addEventListener('click', (e) => {
            if (e.target.closest('.client-button')) {
                this.handleClientSelection(e.target.closest('.client-button'));
            }
        });
        
        // Client search handling
        document.getElementById('client-search')?.addEventListener('input', (e) => {
            this.handleClientSearch(e.target.value);
        });
        
        // Form submission validation
        document.getElementById('order-form')?.addEventListener('submit', (e) => {
            if (!this.validateForm()) {
                e.preventDefault();
                return false;
            }
        });
        
        // Material change handlers will be attached to each new detail
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('material-select')) {
                this.handleMaterialChange(e.target);
            }
            if (e.target.classList.contains('qty-input') || 
                e.target.classList.contains('supplier-price') || 
                e.target.classList.contains('selling-price')) {
                this.updateMarginCalculation(e.target.closest('.order-detail-item'));
                this.updateSummary();
            }
        });
        
        // Remove detail handlers
        document.addEventListener('click', (e) => {
            if (e.target.closest('.remove-detail')) {
                this.removeOrderDetail(e.target.closest('.order-detail-item'));
            }
            if (e.target.closest('.show-alternatives-btn')) {
                this.toggleAlternatives(e.target.closest('.order-detail-item'));
            }
        });
    }
    
    addOrderDetail() {
        this.detailCount++;
        const template = document.getElementById('order-detail-template');
        const clone = template.content.cloneNode(true);
        
        // Update indexes and IDs
        this.updateDetailIndexes(clone, this.detailCount - 1);
        
        // Add to container
        const container = document.getElementById('order-details');
        container.appendChild(clone);
        
        // Update UI
        this.updateItemsCount();
        this.updateVisibility();
        
        // Trigger validation update
        if (window.updateValidation) {
            window.updateValidation();
        }
    }
    
    removeOrderDetail(detailElement) {
        if (confirm('Hapus item ini dari order?')) {
            detailElement.remove();
            this.updateItemNumbers();
            this.updateItemsCount();
            this.updateVisibility();
            this.updateSummary();
            
            if (window.updateValidation) {
                window.updateValidation();
            }
        }
    }
    
    updateDetailIndexes(element, index) {
        // Update all name attributes
        const inputs = element.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            if (input.name) {
                input.name = input.name.replace('INDEX', index);
            }
        });
        
        // Update item number
        const itemNumber = element.querySelector('.item-number');
        if (itemNumber) {
            itemNumber.textContent = index + 1;
        }
    }
    
    updateItemNumbers() {
        const details = document.querySelectorAll('.order-detail-item');
        details.forEach((detail, index) => {
            const itemNumber = detail.querySelector('.item-number');
            if (itemNumber) {
                itemNumber.textContent = index + 1;
            }
            
            // Update form indexes
            const inputs = detail.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (input.name && input.name.includes('[')) {
                    const baseName = input.name.substring(0, input.name.indexOf('['));
                    const fieldName = input.name.substring(input.name.lastIndexOf('['));
                    input.name = `${baseName}[${index}]${fieldName}`;
                }
            });
        });
    }
    
    updateItemsCount() {
        const count = document.querySelectorAll('.order-detail-item').length;
        const counter = document.getElementById('items-count');
        if (counter) {
            counter.textContent = `${count} items`;
        }
    }
    
    updateVisibility() {
        const details = document.querySelectorAll('.order-detail-item');
        const emptyState = document.getElementById('empty-state');
        const summary = document.getElementById('order-summary');
        
        if (details.length === 0) {
            emptyState?.classList.remove('hidden');
            summary?.classList.add('hidden');
        } else {
            emptyState?.classList.add('hidden');
            summary?.classList.remove('hidden');
        }
    }
    
    async handleMaterialChange(materialSelect) {
        const materialId = materialSelect.value;
        const detailItem = materialSelect.closest('.order-detail-item');
        
        if (!materialId) {
            this.clearSupplierOptions(detailItem);
            return;
        }
        
        try {
            const response = await fetch(`/orders/material/${materialId}/suppliers`);
            const data = await response.json();
            
            if (data.success) {
                this.updateSupplierOptions(detailItem, data.suppliers);
                this.updateAlternatives(detailItem, data.suppliers);
            }
        } catch (error) {
            console.error('Error fetching suppliers:', error);
            this.clearSupplierOptions(detailItem);
        }
    }
    
    updateSupplierOptions(detailItem, suppliers) {
        const supplierSelect = detailItem.querySelector('.supplier-select');
        if (!supplierSelect) return;
        
        // Clear existing options except the first one
        supplierSelect.innerHTML = '<option value="">Pilih Supplier</option>';
        
        // Add supplier options
        suppliers.forEach(supplier => {
            const option = document.createElement('option');
            option.value = supplier.supplier_id;
            option.textContent = `${supplier.supplier_name} - ${this.formatCurrency(supplier.harga_per_unit)}`;
            option.dataset.price = supplier.harga_per_unit;
            supplierSelect.appendChild(option);
        });
        
        // Auto-select first supplier if available
        if (suppliers.length > 0) {
            supplierSelect.value = suppliers[0].supplier_id;
            this.updateSupplierPrice(detailItem, suppliers[0].harga_per_unit);
        }
    }
    
    updateSupplierPrice(detailItem, price) {
        const supplierPriceInput = detailItem.querySelector('.supplier-price');
        if (supplierPriceInput) {
            supplierPriceInput.value = price;
            this.updateMarginCalculation(detailItem);
        }
    }
    
    updateAlternatives(detailItem, suppliers) {
        const alternativesList = detailItem.querySelector('.alternatives-list');
        const showBtn = detailItem.querySelector('.show-alternatives-btn');
        
        if (!alternativesList || suppliers.length <= 1) {
            showBtn?.style.setProperty('display', 'none');
            return;
        }
        
        showBtn?.style.setProperty('display', 'inline-block');
        
        alternativesList.innerHTML = suppliers.map((supplier, index) => `
            <div class="flex justify-between items-center py-2 ${index > 0 ? 'border-t border-gray-100' : ''}">
                <div>
                    <span class="font-medium">${this.escapeHtml(supplier.supplier_name)}</span>
                    ${index === 0 ? '<span class="ml-2 px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">Terpilih</span>' : ''}
                </div>
                <div class="text-right">
                    <div class="font-semibold">${this.formatCurrency(supplier.harga_per_unit)}</div>
                    ${supplier.margin_percentage ? '<div class="text-xs text-gray-500">Margin: ' + supplier.margin_percentage + '%</div>' : ''}
                </div>
            </div>
        `).join('');
    }
    
    toggleAlternatives(detailItem) {
        const alternativesList = detailItem.querySelector('.alternatives-list');
        const btn = detailItem.querySelector('.show-alternatives-btn');
        
        if (alternativesList.classList.contains('hidden')) {
            alternativesList.classList.remove('hidden');
            btn.innerHTML = '<i class="fas fa-eye-slash mr-1"></i> Sembunyikan alternatif';
        } else {
            alternativesList.classList.add('hidden');
            btn.innerHTML = '<i class="fas fa-eye mr-1"></i> Lihat alternatif supplier';
        }
    }
    
    clearSupplierOptions(detailItem) {
        const supplierSelect = detailItem.querySelector('.supplier-select');
        const alternativesList = detailItem.querySelector('.alternatives-list');
        const showBtn = detailItem.querySelector('.show-alternatives-btn');
        
        if (supplierSelect) {
            supplierSelect.innerHTML = '<option value="">Pilih Supplier</option>';
        }
        if (alternativesList) {
            alternativesList.innerHTML = '';
            alternativesList.classList.add('hidden');
        }
        if (showBtn) {
            showBtn.style.display = 'none';
        }
    }
    
    updateMarginCalculation(detailItem) {
        const qty = parseFloat(detailItem.querySelector('.qty-input')?.value) || 0;
        const supplierPrice = parseFloat(detailItem.querySelector('.supplier-price')?.value) || 0;
        const sellingPrice = parseFloat(detailItem.querySelector('.selling-price')?.value) || 0;
        
        const totalCost = qty * supplierPrice;
        const totalSelling = qty * sellingPrice;
        const margin = totalSelling - totalCost;
        const marginPercentage = totalCost > 0 ? (margin / totalCost) * 100 : 0;
        
        const marginAmountEl = detailItem.querySelector('.margin-amount');
        const marginPercentageEl = detailItem.querySelector('.margin-percentage');
        
        if (marginAmountEl) {
            marginAmountEl.textContent = this.formatCurrency(margin);
            marginAmountEl.className = `margin-amount font-semibold ${margin >= 0 ? 'text-green-600' : 'text-red-600'}`;
        }
        
        if (marginPercentageEl) {
            marginPercentageEl.textContent = `(${marginPercentage.toFixed(1)}%)`;
            marginPercentageEl.className = `margin-percentage text-sm ml-2 ${margin >= 0 ? 'text-green-600' : 'text-red-600'}`;
        }
    }
    
    updateSummary() {
        const details = document.querySelectorAll('.order-detail-item');
        let totalItems = 0;
        let totalCost = 0;
        let totalSelling = 0;
        
        details.forEach(detail => {
            const qty = parseFloat(detail.querySelector('.qty-input')?.value) || 0;
            const supplierPrice = parseFloat(detail.querySelector('.supplier-price')?.value) || 0;
            const sellingPrice = parseFloat(detail.querySelector('.selling-price')?.value) || 0;
            
            if (qty > 0) {
                totalItems += qty;
                totalCost += qty * supplierPrice;
                totalSelling += qty * sellingPrice;
            }
        });
        
        const margin = totalSelling - totalCost;
        const marginPercentage = totalCost > 0 ? (margin / totalCost) * 100 : 0;
        
        // Update summary elements
        document.getElementById('summary-items')?.textContent = totalItems.toFixed(2);
        document.getElementById('summary-total')?.textContent = this.formatCurrency(totalSelling);
        document.getElementById('summary-cost')?.textContent = this.formatCurrency(totalCost);
        document.getElementById('summary-margin')?.textContent = 
            `${this.formatCurrency(margin)} (${marginPercentage.toFixed(1)}%)`;
    }
    
    formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount || 0);
    }
    
    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
    
    handleClientSelection(clientButton) {
        const clientId = clientButton.dataset.clientId;
        const clientName = clientButton.dataset.clientName;
        
        // Update hidden input
        const klienIdInput = document.getElementById('klien_id');
        if (klienIdInput) {
            klienIdInput.value = clientId;
        }
        
        // Update visual selection - remove all selections first
        document.querySelectorAll('.client-button').forEach(button => {
            button.classList.remove('border-blue-500', 'bg-blue-50');
            button.classList.add('border-gray-200');
            
            // Hide selected icon, show unselected icon
            button.querySelector('.client-selected-icon')?.classList.add('hidden');
            button.querySelector('.client-unselected-icon')?.classList.remove('hidden');
        });
        
        // Highlight selected client
        clientButton.classList.remove('border-gray-200');
        clientButton.classList.add('border-blue-500', 'bg-blue-50');
        
        // Show selected icon, hide unselected icon
        clientButton.querySelector('.client-selected-icon')?.classList.remove('hidden');
        clientButton.querySelector('.client-unselected-icon')?.classList.add('hidden');
        
        // Update header indicator
        const indicator = document.getElementById('selected-client-indicator');
        const nameSpan = document.getElementById('selected-client-name');
        if (indicator && nameSpan) {
            indicator.classList.remove('hidden');
            nameSpan.textContent = clientName;
        }
        
        console.log('Client selected:', clientName, 'ID:', clientId);
    }
    
    handleClientSearch(searchTerm) {
        const buttons = document.querySelectorAll('.client-button');
        const grid = document.getElementById('client-grid');
        const noResults = document.getElementById('no-search-results');
        let visibleCount = 0;
        
        const search = searchTerm.toLowerCase().trim();
        
        buttons.forEach(button => {
            const searchData = button.dataset.clientSearch;
            const isVisible = !search || searchData.includes(search);
            
            button.style.display = isVisible ? 'block' : 'none';
            if (isVisible) visibleCount++;
        });
        
        // Show/hide no results message
        if (visibleCount === 0 && search) {
            grid.classList.add('hidden');
            noResults.classList.remove('hidden');
        } else {
            grid.classList.remove('hidden');
            noResults.classList.add('hidden');
        }
    }
    
    validateForm() {
        const errors = [];
        
        // Check if client is selected
        const klienId = document.getElementById('klien_id')?.value;
        if (!klienId) {
            errors.push('Silakan pilih klien terlebih dahulu');
        }
        
        // Check if there are order details
        const orderDetails = document.querySelectorAll('.order-detail-item');
        if (orderDetails.length === 0) {
            errors.push('Tambahkan minimal satu item order');
        }
        
        // Check each order detail
        orderDetails.forEach((detail, index) => {
            const material = detail.querySelector('.material-select')?.value;
            const supplier = detail.querySelector('.supplier-select')?.value;
            const qty = detail.querySelector('.qty-input')?.value;
            const satuan = detail.querySelector('input[name*="[satuan]"]')?.value;
            const hargaSupplier = detail.querySelector('.supplier-price')?.value;
            const hargaJual = detail.querySelector('.selling-price')?.value;
            
            if (!material) errors.push(`Item ${index + 1}: Pilih material`);
            if (!supplier) errors.push(`Item ${index + 1}: Pilih supplier`);
            if (!qty || parseFloat(qty) <= 0) errors.push(`Item ${index + 1}: Masukkan quantity yang valid`);
            if (!satuan) errors.push(`Item ${index + 1}: Masukkan satuan`);
            if (!hargaSupplier || parseFloat(hargaSupplier) < 0) errors.push(`Item ${index + 1}: Masukkan harga supplier`);
            if (!hargaJual || parseFloat(hargaJual) < 0) errors.push(`Item ${index + 1}: Masukkan harga jual`);
        });
        
        if (errors.length > 0) {
            alert('Mohon perbaiki kesalahan berikut:\n\n' + errors.join('\n'));
            return false;
        }
        
        return true;
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    new OrderCreateManager();
});
</script>

@endsection