@extends('layouts.app')
@section('title', 'Edit Supplier - Kamil Maju Persada')
@section('content')

{{-- Welcome Banner --}}
<div class="bg-green-800 rounded-xl sm:rounded-2xl p-3 sm:p-6 lg:p-8 mb-4 sm:mb-6 lg:mb-8 text-white shadow-lg mt-2 sm:mt-4 lg:mt-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg sm:text-2xl lg:text-3xl font-bold mb-1 sm:mb-2">Edit Supplier</h1>
            <p class="text-white text-xs sm:text-base lg:text-lg">Mengedit data supplier dan bahan baku yang disediakan</p>
        </div>
        <div class="hidden lg:block">
            <i class="fas fa-edit text-6xl text-white"></i>
        </div>
    </div>
</div>

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
                    PIC Purchasing
                </label>
                <select name="pic_purchasing_id" id="pic_purchasing_id"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-gray-50 focus:bg-white transition-all duration-200">
                    <option value="">Pilih PIC Purchasing</option>
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
                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-boxes text-white text-sm"></i>
                </div>
                <h2 class="text-lg sm:text-xl font-bold text-purple-800">Daftar Bahan Baku</h2>
            </div>
            <button type="button" onclick="addBahanBaku()" 
                    class="px-3 py-2 sm:px-4 bg-purple-500 hover:bg-purple-600 text-white rounded-lg transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 font-semibold text-xs sm:text-sm">
                <i class="fas fa-plus mr-1 sm:mr-2"></i>
                <span class="hidden sm:inline">Tambah </span>Bahan Baku
            </button>
        </div>

        <div id="bahan-baku-container" class="space-y-3 sm:space-y-4">
            {{-- Render existing bahan baku items from database --}}
            @foreach($supplier->bahanBakuSuppliers as $index => $bahanBaku)
                @php
                    $colors = ['green', 'blue', 'purple', 'yellow', 'red', 'indigo'];
                    $color = $colors[$index % count($colors)];
                @endphp
                <div class="bahan-baku-item bg-gradient-to-r from-{{ $color }}-50 to-{{ $color == 'green' ? 'blue' : 'green' }}-50 rounded-lg p-3 sm:p-4 border-l-4 border-{{ $color }}-500">
                    <div class="flex items-center justify-between mb-3 sm:mb-4">
                        <h3 class="text-sm sm:text-lg font-bold text-gray-800 flex items-center">
                            <i class="fas fa-cube text-{{ $color }}-600 mr-1 sm:mr-2 text-sm"></i>
                            <span class="hidden sm:inline">Bahan Baku {{ $index + 1 }}</span>
                            <span class="sm:hidden">BB {{ $index + 1 }}</span>
                        </h3>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="showDetailModal(this)" 
                                    data-bahan-baku-slug="{{ $bahanBaku->slug }}"
                                    class="text-green-600 hover:text-green-800 hover:bg-green-100 p-1.5 sm:p-2 rounded-full transition-all duration-200" 
                                    title="Lihat Detail">
                                <i class="fas fa-eye text-xs sm:text-sm"></i>
                            </button>
                            <button type="button" onclick="removeBahanBaku(this)" 
                                    class="text-red-600 hover:text-red-800 hover:bg-red-100 p-1.5 sm:p-2 rounded-full transition-all duration-200" 
                                    title="Hapus">
                                <i class="fas fa-trash text-xs sm:text-sm"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4">
                        <div class="sm:col-span-2 lg:col-span-1">
                            <label class="block text-xs sm:text-sm font-semibold text-{{ $color }}-700 mb-1 sm:mb-2">
                                <i class="fas fa-tag mr-1 text-{{ $color }}-500 text-xs"></i>
                                <span class="hidden sm:inline">Nama Bahan Baku</span>
                                <span class="sm:hidden">Nama</span>
                            </label>
                            <input type="text" name="bahan_baku[{{ $index }}][nama]"
                                   value="{{ $bahanBaku->nama }}"
                                   placeholder="Contoh: Tepung Terigu"
                                   class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-{{ $color }}-200 focus:border-{{ $color }}-500 bg-white transition-all duration-200">
                            <input type="hidden" name="bahan_baku[{{ $index }}][id]" value="{{ $bahanBaku->id }}">
                        </div>
                        
                        <div>
                            <label class="block text-xs sm:text-sm font-semibold text-{{ $color }}-700 mb-1 sm:mb-2">
                                <i class="fas fa-weight-hanging mr-1 text-{{ $color }}-500 text-xs"></i>
                                Satuan
                            </label>
                            <select name="bahan_baku[{{ $index }}][satuan]"
                                    class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-{{ $color }}-200 focus:border-{{ $color }}-500 bg-white transition-all duration-200">
                                <option value="">Pilih Satuan</option>
                                <option value="kg" {{ $bahanBaku->satuan == 'kg' ? 'selected' : '' }}>KG</option>
                                <option value="gram" {{ $bahanBaku->satuan == 'gram' ? 'selected' : '' }}>GR</option>
                                <option value="ton" {{ $bahanBaku->satuan == 'ton' ? 'selected' : '' }}>Ton</option>
                                <option value="liter" {{ $bahanBaku->satuan == 'liter' ? 'selected' : '' }}>L</option>
                                <option value="ml" {{ $bahanBaku->satuan == 'ml' ? 'selected' : '' }}>ML</option>
                                <option value="pcs" {{ $bahanBaku->satuan == 'pcs' ? 'selected' : '' }}>PCS</option>
                                <option value="pack" {{ $bahanBaku->satuan == 'pack' ? 'selected' : '' }}>Pack</option>
                                <option value="box" {{ $bahanBaku->satuan == 'box' ? 'selected' : '' }}>Box</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-xs sm:text-sm font-semibold text-{{ $color }}-700 mb-1 sm:mb-2">
                                <i class="fas fa-money-bill-wave mr-1 text-{{ $color }}-500 text-xs"></i>
                                <span class="hidden sm:inline">Harga per Satuan</span>
                                <span class="sm:hidden">Harga</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-2 sm:left-3 top-1/2 transform -translate-y-1/2 text-gray-500 font-semibold text-xs sm:text-sm">Rp</span>
                                <input type="text" name="bahan_baku[{{ $index }}][harga_per_satuan]" 
                                       value="{{ number_format($bahanBaku->harga_per_satuan, 0, ',', '.') }}"
                                       placeholder="0"
                                       class="currency-input w-full pl-8 sm:pl-10 pr-2 sm:pr-3 py-1.5 sm:py-2 text-xs sm:text-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-{{ $color }}-200 focus:border-{{ $color }}-500 bg-white transition-all duration-200">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs sm:text-sm font-semibold text-{{ $color }}-700 mb-1 sm:mb-2">
                                <i class="fas fa-warehouse mr-1 text-{{ $color }}-500 text-xs"></i>
                                <span class="hidden sm:inline">Stok Tersedia</span>
                                <span class="sm:hidden">Stok</span>
                            </label>
                            <input type="text" name="bahan_baku[{{ $index }}][stok]"
                                   value="{{ number_format($bahanBaku->stok, 0, ',', '.') }}"
                                   placeholder="0"
                                   class="number-input w-full px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-{{ $color }}-200 focus:border-{{ $color }}-500 bg-white transition-all duration-200">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t-2 border-gray-100">
        <button type="submit" 
                class="w-full sm:w-auto px-4 sm:px-6 py-2.5 sm:py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold text-sm sm:text-base">
            <i class="fas fa-save mr-2"></i>
            Update Supplier
        </button>
        
        <button type="reset" 
                class="w-full sm:w-auto px-4 sm:px-6 py-2.5 sm:py-3 bg-gradient-to-r from-gray-400 to-gray-500 hover:from-gray-500 hover:to-gray-600 text-white rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold text-sm sm:text-base">
            <i class="fas fa-undo mr-2"></i>
            Reset Form
        </button>
    </div>
</form>

{{-- Update Confirmation Modal --}}
<div id="saveModal" class="fixed inset-0 backdrop-blur-xs bg-opacity-50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 transform scale-95 transition-all duration-300" id="modalContent">
        {{-- Modal Header --}}
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-4 sm:p-6 rounded-t-xl">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-edit text-white text-sm"></i>
                </div>
                <h3 class="text-lg sm:text-xl font-bold">Konfirmasi Update</h3>
            </div>
        </div>
        
        {{-- Modal Body --}}
        <div class="p-4 sm:p-6">
            <div class="flex items-start mb-4">
                <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-question-circle text-green-600 text-lg"></i>
                </div>
                <div>
                    <p class="text-gray-800 font-medium mb-2">Apakah Anda yakin ingin mengupdate data supplier ini?</p>
                    <div id="modalSummary" class="text-sm text-gray-600 bg-gray-50 rounded-lg p-3">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-building text-green-500 mr-2"></i>
                            <span class="font-semibold">Supplier:</span>
                            <span id="summaryNama" class="ml-1 text-gray-800">-</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-boxes text-purple-500 mr-2"></i>
                            <span class="font-semibold">Total Bahan Baku:</span>
                            <span id="summaryBahanBaku" class="ml-1 text-gray-800">0</span>
                            <span class="ml-1">item</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Modal Footer --}}
        <div class="bg-gray-50 px-4 sm:px-6 py-3 sm:py-4 rounded-b-xl flex flex-col sm:flex-row gap-2 sm:gap-3 sm:justify-end">
            <button type="button" onclick="closeModal()" 
                    class="w-full sm:w-auto px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 hover:text-gray-900 rounded-lg transition-all duration-200 font-semibold order-2 sm:order-1">
                <i class="fas fa-times mr-2"></i>
                Batal
            </button>
            <button type="button" onclick="confirmSave()" 
                    class="w-full sm:w-auto px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-lg transition-all duration-200 font-semibold shadow-lg hover:shadow-xl order-1 sm:order-2">
                <i class="fas fa-check mr-2"></i>
                Ya, Update
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Color schemes for new bahan baku items
const colorSchemes = [
    { border: 'border-green-500', text: 'text-green-700', icon: 'text-green-500', focus: 'focus:ring-green-200 focus:border-green-500', bg: 'from-green-50 to-green-50' },
    { border: 'border-green-500', text: 'text-green-700', icon: 'text-green-500', focus: 'focus:ring-green-200 focus:border-green-500', bg: 'from-green-50 to-purple-50' },
    { border: 'border-purple-500', text: 'text-purple-700', icon: 'text-purple-500', focus: 'focus:ring-purple-200 focus:border-purple-500', bg: 'from-purple-50 to-pink-50' },
    { border: 'border-yellow-500', text: 'text-yellow-700', icon: 'text-yellow-500', focus: 'focus:ring-yellow-200 focus:border-yellow-500', bg: 'from-yellow-50 to-orange-50' },
    { border: 'border-red-500', text: 'text-red-700', icon: 'text-red-500', focus: 'focus:ring-red-200 focus:border-red-500', bg: 'from-red-50 to-pink-50' },
    { border: 'border-indigo-500', text: 'text-indigo-700', icon: 'text-indigo-500', focus: 'focus:ring-indigo-200 focus:border-indigo-500', bg: 'from-indigo-50 to-green-50' }
];

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
    let value = input.value.replace(/\D/g, ''); // Remove non-digits
    if (value === '') {
        input.value = '';
        return;
    }
    
    // Format with thousand separators
    input.value = formatNumber(value);
}

// Handle number input formatting (without currency)
function handleNumberInput(input) {
    let value = input.value.replace(/\D/g, ''); // Remove non-digits
    if (value === '') {
        input.value = '';
        return;
    }
    
    // Format with thousand separators
    input.value = formatNumber(value);
}

function addBahanBaku() {
    const container = document.getElementById('bahan-baku-container');
    const currentItems = container.querySelectorAll('.bahan-baku-item');
    const newIndex = currentItems.length;
    const newNumber = currentItems.length + 1;
    const colorIndex = newIndex % colorSchemes.length;
    const colors = colorSchemes[colorIndex];
    
    const bahanBakuHtml = `
        <div class="bahan-baku-item bg-gradient-to-r ${colors.bg} rounded-lg p-3 sm:p-4 border-l-4 ${colors.border} animate-slideIn">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <h3 class="text-sm sm:text-lg font-bold text-gray-800 flex items-center">
                    <i class="fas fa-cube ${colors.icon} mr-1 sm:mr-2 text-sm"></i>
                    <span class="hidden sm:inline">Bahan Baku ${newNumber}</span>
                    <span class="sm:hidden">BB ${newNumber}</span>
                </h3>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="showDetailModal(this)" 
                            data-bahan-baku-slug=""
                            class="text-gray-400 p-1.5 sm:p-2 rounded-full cursor-not-allowed" 
                            title="Simpan data terlebih dahulu untuk melihat riwayat harga"
                            disabled>
                        <i class="fas fa-eye text-xs sm:text-sm"></i>
                    </button>
                    <button type="button" onclick="removeBahanBaku(this)" 
                            class="text-red-600 hover:text-red-800 hover:bg-red-100 p-1.5 sm:p-2 rounded-full transition-all duration-200" 
                            title="Hapus">
                        <i class="fas fa-trash text-xs sm:text-sm"></i>
                    </button>
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4">
                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="block text-xs sm:text-sm font-semibold ${colors.text} mb-1 sm:mb-2">
                        <i class="fas fa-tag mr-1 ${colors.icon} text-xs"></i>
                        <span class="hidden sm:inline">Nama Bahan Baku</span>
                        <span class="sm:hidden">Nama</span>
                    </label>
                    <input type="text" name="bahan_baku[${newIndex}][nama]"
                           placeholder="Nama bahan baku..."
                           class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm border-2 border-gray-300 rounded-lg ${colors.focus} bg-white transition-all duration-200">
                </div>
                
                <div>
                    <label class="block text-xs sm:text-sm font-semibold ${colors.text} mb-1 sm:mb-2">
                        <i class="fas fa-weight-hanging mr-1 ${colors.icon} text-xs"></i>
                        Satuan
                    </label>
                    <select name="bahan_baku[${newIndex}][satuan]"
                            class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm border-2 border-gray-300 rounded-lg ${colors.focus} bg-white transition-all duration-200">
                        <option value="">Pilih Satuan</option>
                        <option value="kg">KG</option>
                        <option value="gram">GR</option>
                        <option value="ton">Ton</option>
                        <option value="liter">L</option>
                        <option value="ml">ML</option>
                        <option value="pcs">PCS</option>
                        <option value="pack">Pack</option>
                        <option value="box">Box</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs sm:text-sm font-semibold ${colors.text} mb-1 sm:mb-2">
                        <i class="fas fa-money-bill-wave mr-1 ${colors.icon} text-xs"></i>
                        <span class="hidden sm:inline">Harga per Satuan</span>
                        <span class="sm:hidden">Harga</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-2 sm:left-3 top-1/2 transform -translate-y-1/2 text-gray-500 font-semibold text-xs sm:text-sm">Rp</span>
                        <input type="text" name="bahan_baku[${newIndex}][harga_per_satuan]"
                               placeholder="0"
                               class="currency-input w-full pl-8 sm:pl-10 pr-2 sm:pr-3 py-1.5 sm:py-2 text-xs sm:text-sm border-2 border-gray-300 rounded-lg ${colors.focus} bg-white transition-all duration-200">
                    </div>
                </div>
                
                <div>
                    <label class="block text-xs sm:text-sm font-semibold ${colors.text} mb-1 sm:mb-2">
                        <i class="fas fa-warehouse mr-1 ${colors.icon} text-xs"></i>
                        <span class="hidden sm:inline">Stok Tersedia</span>
                        <span class="sm:hidden">Stok</span>
                    </label>
                    <input type="text" name="bahan_baku[${newIndex}][stok]"
                           placeholder="0"
                           class="number-input w-full px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm border-2 border-gray-300 rounded-lg ${colors.focus} bg-white transition-all duration-200">
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
    const bahanBakuItems = document.querySelectorAll('.bahan-baku-item');
    
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
        const title = item.querySelector('h3');
        const icon = title.querySelector('i');
        title.innerHTML = `
            ${icon.outerHTML}
            <span class="hidden sm:inline">Bahan Baku ${index + 1}</span>
            <span class="sm:hidden">BB ${index + 1}</span>
        `;
        
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

// Modal functions for save confirmation
function showModal() {
    const modal = document.getElementById('saveModal');
    const modalContent = document.getElementById('modalContent');
    
    // Update modal summary
    const namaSupplier = document.getElementById('nama').value || 'Belum diisi';
    const bahanBakuCount = document.querySelectorAll('.bahan-baku-item').length;
    
    document.getElementById('summaryNama').textContent = namaSupplier;
    document.getElementById('summaryBahanBaku').textContent = bahanBakuCount;
    
    // Show modal with animation
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
    
    // Hide modal with animation
    modalContent.classList.remove('scale-100');
    modalContent.classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }, 300);
}

function confirmSave() {
    closeModal();
    
    // Convert formatted numbers back to raw numbers before submitting
    const currencyInputs = document.querySelectorAll('.currency-input');
    const numberInputs = document.querySelectorAll('.number-input');
    
    currencyInputs.forEach(input => {
        if (input.value) {
            // Remove dots from formatted numbers
            input.value = unformatNumber(input.value);
        }
    });
    
    numberInputs.forEach(input => {
        if (input.value) {
            // Remove dots from formatted numbers  
            input.value = unformatNumber(input.value);
        }
    });
    
    // Add a small delay to ensure formatting is complete
    setTimeout(() => {
        // Submit the form
        document.querySelector('form').submit();
    }, 100);
}

// Detail modal functions
// Detail modal functions - now redirects to price history
function showDetailModal(button) {
    // Get bahan baku slug from the button's data attribute
    const bahanBakuSlug = button.getAttribute('data-bahan-baku-slug');
    
    if (!bahanBakuSlug || bahanBakuSlug.trim() === '') {
        alert('Harap simpan data bahan baku terlebih dahulu untuk melihat riwayat harga');
        return;
    }
    
    // Get supplier slug from the form action URL
    const form = document.querySelector('form');
    const actionUrl = form.getAttribute('action');
    const supplierSlug = actionUrl.match(/supplier\/([^\/]+)/)?.[1] || '{{ $supplier->slug }}';
    
    // Redirect to price history page using slugs
    const url = `/supplier/${supplierSlug}/bahan-baku/${bahanBakuSlug}/riwayat-harga`;
    window.location.href = url;
}

// Add custom CSS animations and styles
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
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
            transform: translateY(-20px);
        }
    }
    
    .animate-slideIn {
        animation: slideIn 0.3s ease-in-out;
    }
    
    .transform {
        transition: transform 0.2s ease-in-out;
    }
    
    /* Disable scroll on number inputs */
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
    
    /* Smooth focus transitions */
    input:focus, select:focus, textarea:focus {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
`;
document.head.appendChild(style);

// Initialize event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to existing currency and number inputs
    const currencyInputs = document.querySelectorAll('.currency-input');
    const numberInputs = document.querySelectorAll('.number-input');
    
    currencyInputs.forEach(input => {
        input.addEventListener('input', function() {
            handleCurrencyInput(this);
        });
        
        // Prevent wheel scrolling
        input.addEventListener('wheel', function(e) {
            e.preventDefault();
        });
    });
    
    numberInputs.forEach(input => {
        input.addEventListener('input', function() {
            handleNumberInput(this);
        });
        
        // Prevent wheel scrolling
        input.addEventListener('wheel', function(e) {
            e.preventDefault();
        });
    });
});

// Form validation with modal confirmation
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault(); // Always prevent default to show modal
    
    // Only validate required fields (nama supplier)
    const namaSupplier = document.getElementById('nama');
    let isValid = true;
    
    if (!namaSupplier.value.trim()) {
        isValid = false;
        namaSupplier.classList.add('border-red-500');
        namaSupplier.classList.remove('border-gray-300');
        
        // Show error message
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
        
        // Remove error message if exists
        const errorMsg = namaSupplier.parentNode.querySelector('.error-message');
        if (errorMsg) {
            errorMsg.remove();
        }
    }
    
    if (isValid) {
        // Show confirmation modal
        showModal();
    }
});
</script>
@endpush
