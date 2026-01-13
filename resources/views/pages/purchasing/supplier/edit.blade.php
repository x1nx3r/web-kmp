@extends('layouts.app')
@section('title', 'Edit Supplier - Kamil Maju Persada')
@section('content')

<x-welcome-banner title="Edit Supplier" subtitle="Mengedit data supplier dan bahan baku yang disediakan" icon="fas fa-edit" />

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['title' => 'Purchasing', 'url' => '#'],
    ['title' => 'Supplier', 'url' => route('supplier.index')],
    'Edit Supplier: ' . $supplier->nama
]" />

{{-- Back Button --}}
<div class="mb-4 sm:mb-6">
    <a href="{{ route('supplier.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 hover:text-gray-900 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md transform hover:-translate-y-0.5">
        <i class="fas fa-arrow-left mr-2"></i>
        <span class="font-semibold">Kembali</span>
    </a>
</div>

{{-- Display Validation Errors --}}
@if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
        <strong class="font-bold">Ada kesalahan dalam input:</strong>
        <ul class="mt-2">
            @foreach ($errors->all() as $error)
                <li class="list-disc list-inside">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Display Success Message --}}
@if (session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
        {{ session('success') }}
    </div>
@endif

{{-- Form Container --}}
<form action="{{ route('supplier.update', $supplier->slug) }}" method="POST" class="space-y-6">
    @csrf
    @method('PUT')
    
    {{-- Informasi Supplier Section --}}
    <div class="bg-white rounded-lg sm:rounded-xl shadow-sm p-4 sm:p-6 border border-gray-200">
        <div class="flex items-center mb-4 sm:mb-6">
            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3">
                <i class="fas fa-building text-white text-sm"></i>
            </div>
            <h2 class="text-lg sm:text-xl font-bold text-green-800">Informasi Supplier</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
            {{-- Nama Supplier --}}
            <div class="sm:col-span-2">
                <label for="nama" class="block text-sm font-semibold text-green-700 mb-2">
                    <i class="fas fa-industry mr-2 text-green-500"></i>
                    Nama Supplier <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nama" id="nama" required
                       value="{{ $supplier->nama }}"
                       placeholder="Masukkan nama supplier..."
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-gray-50 focus:bg-white transition-all duration-200">
            </div>

            {{-- Alamat --}}
            <div class="sm:col-span-2">
                <label for="alamat" class="block text-sm font-semibold text-green-700 mb-2">
                    <i class="fas fa-map-marker-alt mr-2 text-green-500"></i>
                    Alamat Lengkap
                </label>
                <textarea name="alamat" id="alamat" rows="3"
                          placeholder="Masukkan alamat lengkap supplier..."
                          class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-gray-50 focus:bg-white transition-all duration-200 resize-none">{{ $supplier->alamat }}</textarea>
            </div>

            {{-- No HP --}}
            <div>
                <label for="no_hp" class="block text-sm font-semibold text-green-700 mb-2">
                    <i class="fas fa-phone mr-2 text-green-500"></i>
                    Nomor HP/Telepon
                </label>
                <input type="tel" name="no_hp" id="no_hp"
                       value="{{ $supplier->no_hp }}"
                       placeholder="Contoh: 08123456789"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-gray-50 focus:bg-white transition-all duration-200">
            </div>

            {{-- PIC Purchasing --}}
            <div>
                <label for="pic_purchasing_id" class="block text-sm font-semibold text-green-700 mb-2">
                    <i class="fas fa-user-tie mr-2 text-green-500"></i>
                    PIC Procurement
                </label>
                <select name="pic_purchasing_id" id="pic_purchasing_id"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-gray-50 focus:bg-white transition-all duration-200">
                    <option value="">Pilih PIC Procurement</option>
                    @if(isset($purchasingUsers))
                        @foreach($purchasingUsers as $user)
                            <option value="{{ $user->id }}" {{ $supplier->pic_purchasing_id == $user->id ? 'selected' : '' }}>
                                {{ $user->nama }} ({{ ucfirst($user->role) }})
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>
    </div>

    {{-- Bahan Baku Section --}}
    <div class="bg-white rounded-lg sm:rounded-xl shadow-sm p-4 sm:p-6 border border-gray-200">
        <div class="flex items-center justify-between mb-4 sm:mb-6">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-boxes text-white text-sm"></i>
                </div>
                <h2 class="text-lg sm:text-xl font-bold text-green-800">Daftar Bahan Baku</h2>
            </div>
            <button type="button" onclick="addBahanBaku()" 
                    class="px-3 py-2 sm:px-4 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-all duration-200 shadow-md hover:shadow-lg font-semibold text-sm">
                <i class="fas fa-plus mr-2"></i>
                Tambah Bahan
            </button>
        </div>

        <div id="bahan-baku-container" class="space-y-3">
            {{-- Render existing bahan baku items from database --}}
            @foreach($supplier->bahanBakuSuppliers as $index => $bahanBaku)
                <div class="bahan-baku-item bg-white border-2 border-gray-200 rounded-lg p-4 hover:border-green-300 transition-all duration-200">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-cube text-green-600 text-sm"></i>
                            </div>
                            <span class="font-bold text-gray-700">Bahan Baku #{{ $index + 1 }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="showDetailModal(this)" 
                                    data-bahan-baku-slug="{{ $bahanBaku->slug }}"
                                    class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 p-2 rounded-lg transition-all" 
                                    title="Lihat Detail">
                                <i class="fas fa-eye text-sm"></i>
                            </button>
                            <button type="button" onclick="removeBahanBaku(this)" 
                                    class="text-red-600 hover:text-red-800 hover:bg-red-50 p-2 rounded-lg transition-all" 
                                    title="Hapus">
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div class="sm:col-span-2 lg:col-span-1">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Nama Bahan Baku</label>
                            <input type="text" name="bahan_baku[{{ $index }}][nama]"
                                   value="{{ $bahanBaku->nama }}"
                                   placeholder="Contoh: Tepung Terigu"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-200 focus:border-green-400 transition-all">
                            <input type="hidden" name="bahan_baku[{{ $index }}][id]" value="{{ $bahanBaku->id }}">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Satuan</label>
                            <input type="text" name="bahan_baku[{{ $index }}][satuan]" value="Kg" readonly
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Harga per Kg</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">Rp</span>
                                <input type="text" name="bahan_baku[{{ $index }}][harga_per_satuan]" 
                                       value="{{ number_format($bahanBaku->harga_per_satuan, 0, ',', '.') }}"
                                       placeholder="0"
                                       class="currency-input w-full pl-10 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-200 focus:border-green-400 transition-all">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Stok (Kg)</label>
                            <input type="text" name="bahan_baku[{{ $index }}][stok]"
                                   value="{{ number_format($bahanBaku->stok, 0, ',', '.') }}"
                                   placeholder="0"
                                   class="number-input w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-200 focus:border-green-400 transition-all">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t-2 border-gray-100">
        <button type="submit" 
                class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl font-semibold">
            <i class="fas fa-save mr-2"></i>
            Update Supplier
        </button>
        
        <button type="reset" 
                class="w-full sm:w-auto px-6 py-3 bg-gray-400 hover:bg-gray-500 text-white rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl font-semibold">
            <i class="fas fa-undo mr-2"></i>
            Reset Form
        </button>
    </div>
</form>

{{-- Update Confirmation Modal --}}
<div id="saveModal" class="fixed inset-0 backdrop-blur-sm bg-black/20 backdrop-blur-xs bg-opacity-50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full transform scale-95 transition-all duration-300" id="modalContent">
        {{-- Modal Header --}}
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-6 rounded-t-xl">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-edit text-green-600"></i>
                </div>
                <h3 class="text-xl font-bold">Konfirmasi Update</h3>
            </div>
        </div>
        
        {{-- Modal Body --}}
        <div class="p-6">
            <div class="flex items-start mb-4">
                <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-question-circle text-green-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-800 font-medium mb-3">Apakah Anda yakin ingin mengupdate data supplier ini?</p>
                    <div id="modalSummary" class="text-sm text-gray-600 bg-gray-50 rounded-lg p-3 space-y-2">
                        <div class="flex items-center">
                            <i class="fas fa-building text-green-500 mr-2 w-5"></i>
                            <span class="font-semibold mr-1">Supplier:</span>
                            <span id="summaryNama" class="text-gray-800">-</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-boxes text-green-500 mr-2 w-5"></i>
                            <span class="font-semibold mr-1">Total Bahan Baku:</span>
                            <span id="summaryBahanBaku" class="text-gray-800">0</span>
                            <span class="ml-1">item</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Modal Footer --}}
        <div class="bg-gray-50 px-6 py-4 rounded-b-xl flex gap-3 justify-end">
            <button type="button" onclick="closeModal()" 
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-all font-semibold">
                <i class="fas fa-times mr-2"></i>
                Batal
            </button>
            <button type="button" onclick="confirmSave()" 
                    class="px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-lg transition-all font-semibold shadow-lg">
                <i class="fas fa-check mr-2"></i>
                Ya, Update
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Format number with thousand separators
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Remove formatting from number
function unformatNumber(str) {
    return str.replace(/\./g, '');
}

// Handle currency input formatting
function handleCurrencyInput(input) {
    let value = input.value.replace(/\D/g, '');
    if (value === '') {
        input.value = '';
        return;
    }
    input.value = formatNumber(value);
}

// Handle number input formatting
function handleNumberInput(input) {
    let value = input.value.replace(/\D/g, '');
    if (value === '') {
        input.value = '';
        return;
    }
    input.value = formatNumber(value);
}

function addBahanBaku() {
    const container = document.getElementById('bahan-baku-container');
    const currentItems = container.querySelectorAll('.bahan-baku-item');
    const newIndex = currentItems.length;
    const newNumber = currentItems.length + 1;
    
    const bahanBakuHtml = `
        <div class="bahan-baku-item bg-white border-2 border-gray-200 rounded-lg p-4 hover:border-green-300 transition-all duration-200 animate-slideIn">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-cube text-green-600 text-sm"></i>
                    </div>
                    <span class="font-bold text-gray-700">Bahan Baku #${newNumber}</span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="showDetailModal(this)" 
                            data-bahan-baku-slug=""
                            class="text-gray-400 p-2 rounded-lg cursor-not-allowed" 
                            title="Simpan data terlebih dahulu"
                            disabled>
                        <i class="fas fa-eye text-sm"></i>
                    </button>
                    <button type="button" onclick="removeBahanBaku(this)" 
                            class="text-red-600 hover:text-red-800 hover:bg-red-50 p-2 rounded-lg transition-all" 
                            title="Hapus">
                        <i class="fas fa-trash text-sm"></i>
                    </button>
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Bahan Baku</label>
                    <input type="text" name="bahan_baku[${newIndex}][nama]"
                           placeholder="Contoh: Tepung Terigu"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-200 focus:border-green-400 transition-all">
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Satuan</label>
                    <input type="text" name="bahan_baku[${newIndex}][satuan]" value="Kg" readonly
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed">
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Harga per Kg</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">Rp</span>
                        <input type="text" name="bahan_baku[${newIndex}][harga_per_satuan]"
                               placeholder="0"
                               class="currency-input w-full pl-10 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-200 focus:border-green-400 transition-all">
                    </div>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Stok (Kg)</label>
                    <input type="text" name="bahan_baku[${newIndex}][stok]"
                           placeholder="0"
                           class="number-input w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-200 focus:border-green-400 transition-all">
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', bahanBakuHtml);
    
    // Add event listeners to new inputs
    const newItem = container.lastElementChild;
    const currencyInputs = newItem.querySelectorAll('.currency-input');
    const numberInputs = newItem.querySelectorAll('.number-input');
    
    currencyInputs.forEach(input => {
        input.addEventListener('input', function() {
            handleCurrencyInput(this);
        });
        input.addEventListener('wheel', function(e) {
            e.preventDefault();
        });
    });
    
    numberInputs.forEach(input => {
        input.addEventListener('input', function() {
            handleNumberInput(this);
        });
        input.addEventListener('wheel', function(e) {
            e.preventDefault();
        });
    });
}

function removeBahanBaku(button) {
    const item = button.closest('.bahan-baku-item');
    item.style.animation = 'slideOut 0.3s ease-in-out';
    
    setTimeout(() => {
        item.remove();
        updateBahanBakuNumbers();
    }, 300);
}

function updateBahanBakuNumbers() {
    const items = document.querySelectorAll('.bahan-baku-item');
    items.forEach((item, index) => {
        // Update display title
        const title = item.querySelector('.font-bold');
        title.textContent = `Bahan Baku #${index + 1}`;
        
        // Update name attributes for form inputs
        const inputs = item.querySelectorAll('input, select');
        inputs.forEach(input => {
            const name = input.getAttribute('name');
            if (name && name.includes('bahan_baku[')) {
                const newName = name.replace(/bahan_baku\[\d+\]/, `bahan_baku[${index}]`);
                input.setAttribute('name', newName);
            }
        });
    });
}

// Modal functions
function showModal() {
    const modal = document.getElementById('saveModal');
    const modalContent = document.getElementById('modalContent');
    
    const namaSupplier = document.getElementById('nama').value || 'Belum diisi';
    const bahanBakuCount = document.querySelectorAll('.bahan-baku-item').length;
    
    document.getElementById('summaryNama').textContent = namaSupplier;
    document.getElementById('summaryBahanBaku').textContent = bahanBakuCount;
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => {
        modalContent.classList.remove('scale-95');
        modalContent.classList.add('scale-100');
    }, 10);
}

function closeModal() {
    const modal = document.getElementById('saveModal');
    const modalContent = document.getElementById('modalContent');
    
    modalContent.classList.remove('scale-100');
    modalContent.classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }, 300);
}

function confirmSave() {
    closeModal();
    
    const currencyInputs = document.querySelectorAll('.currency-input');
    const numberInputs = document.querySelectorAll('.number-input');
    
    currencyInputs.forEach(input => {
        if (input.value) {
            input.value = unformatNumber(input.value);
        }
    });
    
    numberInputs.forEach(input => {
        if (input.value) {
            input.value = unformatNumber(input.value);
        }
    });
    
    setTimeout(() => {
        document.querySelector('form').submit();
    }, 100);
}

function showDetailModal(button) {
    const bahanBakuSlug = button.getAttribute('data-bahan-baku-slug');
    
    if (!bahanBakuSlug || bahanBakuSlug.trim() === '') {
        alert('Harap simpan data bahan baku terlebih dahulu untuk melihat riwayat harga');
        return;
    }
    
    const form = document.querySelector('form');
    const actionUrl = form.getAttribute('action');
    const supplierSlug = actionUrl.match(/supplier\/([^\/]+)/)?.[1] || '{{ $supplier->slug }}';
    
    const url = `/procurement/supplier/${supplierSlug}/bahan-baku/${bahanBakuSlug}/riwayat-harga`;
    window.location.href = url;
}

// Add custom CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes slideOut {
        from {
            opacity: 1;
            transform: translateY(0);
        }
        to {
            opacity: 0;
            transform: translateY(-10px);
        }
    }
    
    .animate-slideIn {
        animation: slideIn 0.3s ease-in-out;
    }
    
    .currency-input, .number-input {
        -moz-appearance: textfield;
    }
    
    .currency-input::-webkit-outer-spin-button,
    .currency-input::-webkit-inner-spin-button,
    .number-input::-webkit-outer-spin-button,
    .number-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
`;
document.head.appendChild(style);

// Initialize event listeners
document.addEventListener('DOMContentLoaded', function() {
    const currencyInputs = document.querySelectorAll('.currency-input');
    const numberInputs = document.querySelectorAll('.number-input');
    
    currencyInputs.forEach(input => {
        input.addEventListener('input', function() {
            handleCurrencyInput(this);
        });
        input.addEventListener('wheel', function(e) {
            e.preventDefault();
        });
    });
    
    numberInputs.forEach(input => {
        input.addEventListener('input', function() {
            handleNumberInput(this);
        });
        input.addEventListener('wheel', function(e) {
            e.preventDefault();
        });
    });
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const namaSupplier = document.getElementById('nama');
    let isValid = true;
    
    if (!namaSupplier.value.trim()) {
        isValid = false;
        namaSupplier.classList.add('border-red-500');
        namaSupplier.classList.remove('border-gray-300');
        
        let errorMsg = namaSupplier.parentNode.querySelector('.error-message');
        if (!errorMsg) {
            errorMsg = document.createElement('div');
            errorMsg.className = 'error-message text-red-500 text-xs mt-1 flex items-center';
            errorMsg.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i>Nama Supplier wajib diisi!';
            namaSupplier.parentNode.appendChild(errorMsg);
        }
        
        namaSupplier.focus();
        return;
    } else {
        namaSupplier.classList.remove('border-red-500');
        namaSupplier.classList.add('border-gray-300');
        
        const errorMsg = namaSupplier.parentNode.querySelector('.error-message');
        if (errorMsg) {
            errorMsg.remove();
        }
    }
    
    if (isValid) {
        showModal();
    }
});
</script>
@endpush