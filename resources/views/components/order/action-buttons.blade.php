@props(['action' => route('orders.store'), 'method' => 'POST'])

{{-- Action Buttons Section --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        {{-- Left Side - Status & Info --}}
        <div class="flex items-center">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-save text-blue-600 text-sm"></i>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900">Simpan Order</h3>
                <p class="text-sm text-gray-600">Pastikan semua data sudah benar sebelum menyimpan</p>
            </div>
        </div>

        {{-- Right Side - Action Buttons --}}
        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
            {{-- Cancel Button --}}
            <a href="{{ route('orders.index') }}" 
               class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 bg-white text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                <i class="fas fa-times mr-2"></i>
                Batal
            </a>

            {{-- Save as Draft Button --}}
            <button type="button" 
                    class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 bg-white text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors save-draft-btn">
                <i class="fas fa-file-alt mr-2"></i>
                Simpan Draft
            </button>

            {{-- Save Order Button --}}
            <button type="submit" 
                    class="inline-flex items-center justify-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors save-order-btn">
                <i class="fas fa-check mr-2"></i>
                Simpan Order
            </button>
        </div>
    </div>

    {{-- Validation Messages --}}
    <div id="validation-summary" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg hidden">
        <div class="flex items-start">
            <i class="fas fa-exclamation-triangle text-red-500 mt-0.5 mr-3"></i>
            <div>
                <h4 class="font-medium text-red-800 mb-2">Mohon lengkapi data berikut:</h4>
                <ul id="validation-errors" class="text-sm text-red-700 space-y-1"></ul>
            </div>
        </div>
    </div>

    {{-- Success Message --}}
    <div id="success-message" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg hidden">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-green-500 mr-3"></i>
            <span class="text-green-800 font-medium">Order berhasil disimpan!</span>
        </div>
    </div>
</div>

{{-- Order Validation Summary --}}
<div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
    <h4 class="font-medium text-gray-900 mb-3 flex items-center">
        <i class="fas fa-clipboard-check text-gray-600 mr-2"></i>
        Checklist Order
    </h4>
    
    <div class="space-y-2 text-sm">
        <div class="flex items-center validation-item" data-check="client">
            <i class="fas fa-circle text-gray-400 mr-3 check-icon"></i>
            <span class="text-gray-600">Klien sudah dipilih</span>
        </div>
        <div class="flex items-center validation-item" data-check="order-info">
            <i class="fas fa-circle text-gray-400 mr-3 check-icon"></i>
            <span class="text-gray-600">Informasi order sudah lengkap</span>
        </div>
        <div class="flex items-center validation-item" data-check="order-details">
            <i class="fas fa-circle text-gray-400 mr-3 check-icon"></i>
            <span class="text-gray-600">Minimal 1 item order sudah ditambahkan</span>
        </div>
        <div class="flex items-center validation-item" data-check="pricing">
            <i class="fas fa-circle text-gray-400 mr-3 check-icon"></i>
            <span class="text-gray-600">Semua harga sudah diisi</span>
        </div>
    </div>
    
    <div class="mt-4 pt-3 border-t border-gray-200">
        <div class="flex items-center justify-between text-sm">
            <span class="text-gray-600">Progress:</span>
            <span class="font-medium" id="validation-progress">0/4 selesai</span>
        </div>
        <div class="mt-2 bg-gray-200 rounded-full h-2">
            <div class="bg-green-600 h-2 rounded-full transition-all duration-300" id="validation-bar" style="width: 0%"></div>
        </div>
    </div>
</div>

{{-- Hidden Form Data --}}
<input type="hidden" name="_token" value="{{ csrf_token() }}">
@if($method !== 'POST')
    <input type="hidden" name="_method" value="{{ $method }}">
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('order-form');
    const saveOrderBtn = document.querySelector('.save-order-btn');
    const saveDraftBtn = document.querySelector('.save-draft-btn');
    const validationSummary = document.getElementById('validation-summary');
    const validationErrors = document.getElementById('validation-errors');
    const successMessage = document.getElementById('success-message');
    
    // Form validation
    function validateForm() {
        const errors = [];
        const validationItems = document.querySelectorAll('.validation-item');
        let completedChecks = 0;
        
        // Check client selection
        const clientId = document.querySelector('input[name="klien_id"]')?.value;
        const clientCheck = document.querySelector('[data-check="client"]');
        if (clientId) {
            clientCheck.querySelector('.check-icon').className = 'fas fa-check-circle text-green-500 mr-3 check-icon';
            completedChecks++;
        } else {
            clientCheck.querySelector('.check-icon').className = 'fas fa-circle text-gray-400 mr-3 check-icon';
            errors.push('Klien belum dipilih');
        }
        
        // Check order info
        const tanggalOrder = document.querySelector('input[name="tanggal_order"]')?.value;
        const prioritas = document.querySelector('select[name="prioritas"]')?.value;
        const orderInfoCheck = document.querySelector('[data-check="order-info"]');
        if (tanggalOrder && prioritas) {
            orderInfoCheck.querySelector('.check-icon').className = 'fas fa-check-circle text-green-500 mr-3 check-icon';
            completedChecks++;
        } else {
            orderInfoCheck.querySelector('.check-icon').className = 'fas fa-circle text-gray-400 mr-3 check-icon';
            if (!tanggalOrder) errors.push('Tanggal order belum diisi');
            if (!prioritas) errors.push('Prioritas belum dipilih');
        }
        
        // Check order details
        const orderDetails = document.querySelectorAll('.order-detail-item');
        const orderDetailsCheck = document.querySelector('[data-check="order-details"]');
        if (orderDetails.length > 0) {
            orderDetailsCheck.querySelector('.check-icon').className = 'fas fa-check-circle text-green-500 mr-3 check-icon';
            completedChecks++;
        } else {
            orderDetailsCheck.querySelector('.check-icon').className = 'fas fa-circle text-gray-400 mr-3 check-icon';
            errors.push('Belum ada item order yang ditambahkan');
        }
        
        // Check pricing
        const pricingCheck = document.querySelector('[data-check="pricing"]');
        let allPricesFilled = true;
        orderDetails.forEach((detail, index) => {
            const materialSelect = detail.querySelector('.material-select');
            const supplierSelect = detail.querySelector('.supplier-select');
            const qtyInput = detail.querySelector('.qty-input');
            const supplierPrice = detail.querySelector('.supplier-price');
            const sellingPrice = detail.querySelector('.selling-price');
            
            if (!materialSelect?.value) {
                errors.push(`Item ${index + 1}: Material belum dipilih`);
                allPricesFilled = false;
            }
            if (!supplierSelect?.value) {
                errors.push(`Item ${index + 1}: Supplier belum dipilih`);
                allPricesFilled = false;
            }
            if (!qtyInput?.value || parseFloat(qtyInput.value) <= 0) {
                errors.push(`Item ${index + 1}: Qty harus lebih dari 0`);
                allPricesFilled = false;
            }
            if (!supplierPrice?.value || parseFloat(supplierPrice.value) <= 0) {
                errors.push(`Item ${index + 1}: Harga supplier harus lebih dari 0`);
                allPricesFilled = false;
            }
            if (!sellingPrice?.value || parseFloat(sellingPrice.value) <= 0) {
                errors.push(`Item ${index + 1}: Harga jual harus lebih dari 0`);
                allPricesFilled = false;
            }
        });
        
        if (allPricesFilled && orderDetails.length > 0) {
            pricingCheck.querySelector('.check-icon').className = 'fas fa-check-circle text-green-500 mr-3 check-icon';
            completedChecks++;
        } else {
            pricingCheck.querySelector('.check-icon').className = 'fas fa-circle text-gray-400 mr-3 check-icon';
        }
        
        // Update progress
        const progress = (completedChecks / 4) * 100;
        document.getElementById('validation-progress').textContent = `${completedChecks}/4 selesai`;
        document.getElementById('validation-bar').style.width = `${progress}%`;
        
        // Show/hide validation summary
        if (errors.length > 0) {
            validationErrors.innerHTML = errors.map(error => `<li>• ${error}</li>`).join('');
            validationSummary.classList.remove('hidden');
            successMessage.classList.add('hidden');
            return false;
        } else {
            validationSummary.classList.add('hidden');
            return true;
        }
    }
    
    // Save order
    saveOrderBtn?.addEventListener('click', function(e) {
        e.preventDefault();
        if (validateForm()) {
            // Add hidden input to indicate this is not a draft
            const isDraftInput = document.querySelector('input[name="is_draft"]');
            if (isDraftInput) {
                isDraftInput.value = '0';
            } else {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'is_draft';
                hiddenInput.value = '0';
                form.appendChild(hiddenInput);
            }
            form.submit();
        }
    });
    
    // Save draft
    saveDraftBtn?.addEventListener('click', function(e) {
        e.preventDefault();
        // Add hidden input to indicate this is a draft
        const isDraftInput = document.querySelector('input[name="is_draft"]');
        if (isDraftInput) {
            isDraftInput.value = '1';
        } else {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'is_draft';
            hiddenInput.value = '1';
            form.appendChild(hiddenInput);
        }
        form.submit();
    });
    
    // Real-time validation updates
    function setupValidationWatchers() {
        // Watch client selection
        const clientSelector = document.querySelector('input[name="klien_id"]');
        if (clientSelector) {
            clientSelector.addEventListener('change', validateForm);
        }
        
        // Watch order info
        const orderInfoInputs = document.querySelectorAll('input[name="tanggal_order"], select[name="prioritas"]');
        orderInfoInputs.forEach(input => {
            input.addEventListener('change', validateForm);
        });
        
        // Watch order details (will be called when details are added/removed)
        window.updateValidation = validateForm;
    }
    
    setupValidationWatchers();
    
    // Initial validation
    setTimeout(validateForm, 100);
});
</script>