{{-- Modal Buat Forecast --}}
<div id="forecastModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        {{-- Backdrop --}}
        <div class="fixed inset-0 transition-opacity backdrop-blur-xs" aria-hidden="true" onclick="closeForecastModal()"></div>
        
        {{-- Center the modal --}}
        <span class="hidden sm:inline-block border border-green-600 sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="relative inline-block align-bottom rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full max-h-[95vh] overflow-y-auto m-2 sm:m-0 border-4 border-green-500">
            <form id="forecastForm">
                <div class="bg-white px-0 pt-0 pb-4 sm:px-0 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="w-full mt-0 sm:mt-0 sm:text-left">
                            {{-- Header with close button --}}
                            <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-green-300 rounded-t-xl mb-6">
                                <div class="flex items-center space-x-4">
                                    <div class="w-10 h-10 bg-gray-400 rounded-lg flex items-center justify-center shadow-sm">
                                        <i class="fas fa-chart-bar text-white text-lg"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900">Buat Forecast Bahan Baku</h3>
                                        <p class="text-sm text-gray-600" id="forecastModalSubtitle">Buat forecast berdasarkan purchase order</p>
                                    </div>
                                </div>
                                <button type="button" onclick="closeForecastModal()" 
                                        class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2 rounded-full transition-all duration-200">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>
                            
                            {{-- Modal Content --}}
                            <div class="px-6 pb-6">
                            
                            {{-- Info PO dan Bahan Baku --}}
                            <div class="bg-green-50 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                    <div>
                                        <label class="text-xs sm:text-sm font-medium text-gray-700">Purchase Order</label>
                                        <p class="text-sm sm:text-lg font-semibold text-gray-900" id="modalPONumber"></p>
                                    </div>
                                    <div>
                                        <label class="text-xs sm:text-sm font-medium text-gray-700">Bahan Baku</label>
                                        <p class="text-sm sm:text-lg font-semibold text-gray-900" id="modalBahanBaku"></p>
                                    </div>
                                </div>
                            </div>

                            {{-- Form Fields --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
                                <div>
                                    <label for="perkiraan_tanggal_kirim" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">
                                        <i class="fas fa-calendar-alt text-green-500 mr-1"></i>
                                        Perkiraan Tanggal Kirim <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" id="perkiraan_tanggal_kirim" name="perkiraan_tanggal_kirim" required
                                           class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                                           onchange="calculateDeliveryDays()">
                                </div>
                                <div>
                                    <label for="perkiraan_hari_kirim" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">
                                        <i class="fas fa-truck text-green-500 mr-1"></i>
                                        Hari Pengiriman <span class="text-gray-500">(otomatis)</span>
                                    </label>
                                    <input type="text" id="perkiraan_hari_kirim" name="perkiraan_hari_kirim" readonly
                                           class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm bg-gray-50 cursor-not-allowed"
                                           placeholder="Pilih tanggal dulu">
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Hari dalam minggu dari tanggal kirim
                                    </p>
                                </div>
                            </div>

                            {{-- Pilih Bahan Baku & Supplier --}}
                            <div class="mb-4 sm:mb-6">
                                <label for="bahan_baku_supplier_select" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-building text-green-500 mr-1"></i>
                                    Pilih Bahan Baku & Supplier <span class="text-red-500">*</span>
                                </label>
                                <p class="text-xs text-gray-600 mb-3">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Pilih kombinasi bahan baku dan supplier yang tersedia dengan stok mencukupi
                                </p>
                                <select id="bahan_baku_supplier_select" name="bahan_baku_supplier_select" required
                                        class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                                        onchange="selectBahanBakuSupplier()">
                                    <option value="">-- Pilih Bahan Baku & Supplier --</option>
                                    {{-- Options akan diisi via JavaScript --}}
                                </select>
                                
                                {{-- Loading state --}}
                                <div id="supplierLoading" class="text-center py-4 text-gray-500">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>
                                    Memuat data bahan baku dan supplier...
                                </div>
                                
                                {{-- Detail supplier terpilih --}}
                                <div id="selectedSupplierInfo" class="hidden mt-3 bg-green-50 border border-green-200 rounded-lg p-3">
                                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
                                        <div>
                                            <span class="text-gray-600">Stok Tersedia:</span>
                                            <p class="font-semibold text-green-700" id="infoStok">-</p>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Harga per Satuan:</span>
                                            <p class="font-semibold text-green-700" id="infoHarga">-</p>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Supplier:</span>
                                            <p class="font-semibold text-green-700" id="infoSupplier">-</p>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">PIC Purchasing:</span>
                                            <p class="font-semibold text-green-700" id="infoPICPurchasing">-</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Quantity dan Harga --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
                                <div>
                                    <label for="qty_forecast" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">
                                        <i class="fas fa-cubes text-green-500 mr-1"></i>
                                        Quantity Forecast <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="number" id="qty_forecast" name="qty_forecast" required min="0.01" step="0.01" placeholder="0.00"
                                               class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 pr-12 sm:pr-16 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors text-right font-medium"
                                               oninput="calculateTotal()"
                                               onwheel="event.preventDefault()"
                                               style="appearance: textfield; -moz-appearance: textfield;">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 sm:pr-3">
                                            <span class="text-gray-500 text-xs sm:text-sm font-medium" id="satuanBahanBaku"></span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Jumlah di PO: <span id="jumlahPO" class="font-medium text-green-600"></span>
                                    </p>
                                </div>
                                <div>
                                    <label for="harga_satuan_forecast" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">
                                        <i class="fas fa-tag text-green-500 mr-1"></i>
                                        Harga Satuan Supplier <span class="text-gray-500">(otomatis)</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-2 sm:pl-3 text-gray-500 text-sm">Rp</span>
                                        <input type="text" id="harga_satuan_forecast" name="harga_satuan_forecast" required readonly
                                               class="w-full border border-gray-300 rounded-lg pl-8 sm:pl-10 pr-2 sm:pr-3 py-2 text-sm bg-gray-50 cursor-not-allowed text-right font-medium"
                                               placeholder="Pilih supplier dulu"
                                               style="appearance: textfield; -moz-appearance: textfield;">
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Harga PO: <span id="hargaPO" class="font-medium text-blue-600">-</span>
                                    </p>
                                </div>
                            </div>

                            {{-- Total Harga & Perbandingan --}}
                            <div class="mb-4 sm:mb-6">
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-calculator text-green-500 mr-1"></i>
                                    Perbandingan Harga
                                </label>
                                
                                {{-- Total Harga Forecast --}}
                                <div class="bg-green-50 border-2 border-green-200 rounded-lg p-3 mb-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-700">Total Harga Forecast (Supplier):</span>
                                        <span class="text-lg font-bold text-green-700" id="totalHargaForecast">Rp 0</span>
                                    </div>
                                </div>
                                
                                {{-- Total Harga PO --}}
                                <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-3 mb-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-700">Total Harga PO:</span>
                                        <span class="text-lg font-bold text-blue-700" id="totalHargaPO">Rp 0</span>
                                    </div>
                                </div>
                                
                                {{-- Selisih --}}
                                <div class="bg-gray-50 border-2 border-gray-200 rounded-lg p-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-700">Selisih:</span>
                                        <div class="text-right">
                                            <span class="text-lg font-bold" id="selisihHarga">Rp 0</span>
                                            <div class="text-xs" id="persentaseSelisih"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Catatan --}}
                            <div class="mb-4 sm:mb-6">
                                <label for="catatan" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-sticky-note text-green-500 mr-1"></i>
                                    Catatan
                                </label>
                                <textarea id="catatan" name="catatan" rows="3"
                                          class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none"
                                          placeholder="Tambahkan catatan untuk forecast ini (opsional)..."></textarea>
                            </div>

                            {{-- Hidden Fields --}}
                            <input type="hidden" id="purchase_order_id" name="purchase_order_id">
                            <input type="hidden" id="order_detail_id" name="order_detail_id">
                            <input type="hidden" id="bahan_baku_supplier_id" name="bahan_baku_supplier_id">
                            <input type="hidden" id="harga_satuan_po" name="harga_satuan_po">
                            <input type="hidden" id="tanggal_forecast" name="tanggal_forecast">
                            <input type="hidden" id="hari_kirim_forecast" name="hari_kirim_forecast">
                            
                            </div>
                            {{-- End Modal Content --}}
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-3 py-3 sm:px-6 sm:py-3 sm:flex sm:flex-row-reverse border-t border-gray-200">
                    <button type="submit" id="submitBtn"
                            class="w-full inline-flex justify-center items-center rounded-md border border-transparent shadow-sm px-4 py-2.5 bg-green-600 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors sm:ml-3 sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-save mr-2"></i>
                        Buat Forecast
                    </button>
                    <button type="button" onclick="closeForecastModal()"
                            class="mt-3 w-full inline-flex justify-center items-center rounded-md border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors sm:mt-0 sm:ml-3 sm:w-auto">
                        <i class="fas fa-times mr-2"></i>
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Hide spinner/arrows on number inputs */
input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    -moz-appearance: textfield;
    appearance: textfield;
}

/* Prevent scroll wheel on number inputs */
input[type="number"] {
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

input[type="number"]:focus {
    -webkit-user-select: text;
    -moz-user-select: text;
    -ms-user-select: text;
    user-select: text;
}

/* Ensure text inputs also have consistent styling */
input[type="text"]::-webkit-outer-spin-button,
input[type="text"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>

<script>
// Variables untuk modal
let currentOrderDetailId = null;
let currentPurchaseOrderId = null;

// Fungsi untuk format rupiah Indonesia (titik untuk ribuan)
function formatRupiah(angka) {
    return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Format angka dengan decimal Indonesia (koma untuk decimal, titik untuk ribuan)
function formatNumber(num, decimals = 2) {
    const parts = num.toFixed(decimals).split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    return parts.join(',');
}

// Open forecast modal
async function openForecastModal(orderDetailId, bahanBakuNama, jumlah, purchaseOrderId, noPO) {
    console.log('Opening forecast modal with params:', {
        orderDetailId,
        bahanBakuNama,
        jumlah,
        purchaseOrderId,
        noPO
    });
    
    currentOrderDetailId = orderDetailId;
    currentPurchaseOrderId = purchaseOrderId;
    
    // Set info
    document.getElementById('modalPONumber').textContent = noPO;
    document.getElementById('modalBahanBaku').textContent = bahanBakuNama;
    document.getElementById('jumlahPO').textContent = jumlah;
    document.getElementById('purchase_order_id').value = purchaseOrderId;
    document.getElementById('order_detail_id').value = orderDetailId;
    
    // Reset form
    document.getElementById('forecastForm').reset();
    document.getElementById('purchase_order_id').value = purchaseOrderId;
    document.getElementById('order_detail_id').value = orderDetailId;
    
    // Set default perkiraan tanggal kirim ke 7 hari dari sekarang
    const today = new Date();
    const deliveryDate = new Date(today);
    deliveryDate.setDate(today.getDate() + 7);  
    document.getElementById('perkiraan_tanggal_kirim').value = deliveryDate.toISOString().split('T')[0];
    
    // Calculate delivery days
    calculateDeliveryDays();
    
    try {
        // Load supplier options
        console.log(`Fetching supplier data from: /procurement/forecasting/bahan-baku-suppliers/${orderDetailId}`);
        const response = await fetch(`/procurement/forecasting/bahan-baku-suppliers/${orderDetailId}`);
        console.log('Supplier fetch response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`Failed to fetch supplier data: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Supplier data received:', data);
        console.log('Number of suppliers:', data.bahan_baku_suppliers ? data.bahan_baku_suppliers.length : 0);
        
        if (data.error) {
            alert('Gagal memuat data supplier: ' + data.error);
            return;
        }
        
        // Populate supplier options
        const supplierSelect = document.getElementById('bahan_baku_supplier_select');
        const supplierLoading = document.getElementById('supplierLoading');
        
        supplierSelect.innerHTML = '<option value="">-- Pilih Bahan Baku & Supplier --</option>';
        
        if (data.bahan_baku_suppliers.length === 0) {
            supplierSelect.innerHTML += '<option value="" disabled>Tidak ada bahan baku dan supplier yang tersedia</option>';
            supplierLoading.style.display = 'none';
            console.log('No suppliers available from API');
        } else {
            data.bahan_baku_suppliers.forEach((supplier, index) => {
                console.log(`Supplier ${index}:`, supplier);
                
                const hargaSatuan = supplier.harga_per_satuan || 0;
                const stok = supplier.stok || 0;
                
                console.log(`Supplier ${supplier.nama}: stok=${stok}, harga=${hargaSatuan}, supplier_nama=${supplier.supplier_nama}`);
                
                // Pastikan semua field yang dibutuhkan ada
                const supplierNama = supplier.supplier_nama || supplier.supplier?.nama || 'Supplier tidak diketahui';
                const bahanBakuNama = supplier.nama || 'Bahan Baku tidak diketahui';
                const satuan = supplier.satuan || 'unit';
                const picPurchasing = supplier.pic_purchasing_nama || 'Belum ditentukan';
                
                // Tampilkan semua supplier untuk debugging (nanti bisa dikembalikan filter stok > 0)
                const optionText = `${bahanBakuNama} - ${supplierNama} (Stok: ${formatRupiah(stok)} ${satuan}) - Rp ${formatRupiah(Math.round(hargaSatuan))}`;
                const option = new Option(optionText, supplier.id);
                option.dataset.hargaPerSatuan = hargaSatuan;
                option.dataset.stok = stok;
                option.dataset.satuan = satuan;
                option.dataset.supplierNama = supplierNama;
                option.dataset.bahanBakuNama = bahanBakuNama;
                option.dataset.picPurchasing = picPurchasing;
                
                // Disable option jika stok kosong
                if (stok <= 0) {
                    option.disabled = true;
                    option.text = optionText + ' (STOK KOSONG)';
                }
                
                supplierSelect.appendChild(option);
                console.log(`Added option: ${optionText} ${stok <= 0 ? '(DISABLED)' : ''}`);
                
                if (stok <= 0) {
                    console.log(`Supplier ${bahanBakuNama} has no stock (${stok})`);
                }
            });
            
            // Cek apakah ada option yang ditambahkan selain option default
            if (supplierSelect.options.length <= 1) {
                supplierSelect.innerHTML += '<option value="" disabled>Semua supplier tidak memiliki stok</option>';
                console.log('All suppliers have no stock');
            }
            
            supplierLoading.style.display = 'none';
        }
        
        // Set satuan dan harga PO dari order_detail
        if (data.order_detail) {
            // Set satuan dari bahan_baku_klien yang sudah di-load
            if (data.order_detail.bahan_baku_klien) {
                document.getElementById('satuanBahanBaku').textContent = data.order_detail.bahan_baku_klien.satuan || '';
            }
            
            // Set harga PO - ambil dari harga_jual pada order_detail
            const hargaPO = data.order_detail.harga_jual || 0;
            document.getElementById('harga_satuan_po').value = hargaPO;
            document.getElementById('hargaPO').textContent = 'Rp ' + formatRupiah(Math.round(hargaPO));
            
            // Calculate total PO
            const qtyPO = data.order_detail.qty || 0;
            const totalPO = hargaPO * qtyPO;
            document.getElementById('totalHargaPO').textContent = 'Rp ' + formatRupiah(Math.round(totalPO));
        }
        
    } catch (error) {
        console.error('Error loading supplier data:', error);
        alert('Gagal memuat data supplier');
        return;
    }
    
    // Show modal
    document.getElementById('forecastModal').classList.remove('hidden');
}

// Close forecast modal
function closeForecastModal() {
    document.getElementById('forecastModal').classList.add('hidden');
    document.getElementById('forecastForm').reset();
    currentOrderDetailId = null;
    currentPurchaseOrderId = null;
}

// Calculate delivery days
function calculateDeliveryDays() {
    const deliveryDate = document.getElementById('perkiraan_tanggal_kirim').value;
    if (deliveryDate) {
        const today = new Date();
        const targetDate = new Date(deliveryDate);
        const diffTime = targetDate - today;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        // Array nama hari dalam bahasa Indonesia
        const hariIndonesia = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const namaHari = hariIndonesia[targetDate.getDay()];
        
        document.getElementById('perkiraan_hari_kirim').value = namaHari;
        
        // Update hidden fields for backend
        document.getElementById('tanggal_forecast').value = deliveryDate;
        document.getElementById('hari_kirim_forecast').value = namaHari;
    }
}

// Calculate total
function calculateTotal() {
    const qty = parseFloat(document.getElementById('qty_forecast').value) || 0;
    const hargaSupplierRaw = parseFloat(document.getElementById('harga_satuan_forecast').dataset.rawValue) || 0;
    const hargaPO = parseFloat(document.getElementById('harga_satuan_po').value) || 0;
    
    const totalForecast = qty * hargaSupplierRaw;
    const totalPO = qty * hargaPO;
    const selisih = totalForecast - totalPO;
    const persentase = totalPO > 0 ? ((selisih / totalPO) * 100) : 0;
    
    // Update total forecast
    document.getElementById('totalHargaForecast').textContent = 'Rp ' + formatRupiah(Math.round(totalForecast));
    
    // Update total PO untuk quantity yang dipilih
    document.getElementById('totalHargaPO').textContent = 'Rp ' + formatRupiah(Math.round(totalPO));
    
    // Update selisih
    const selisihElement = document.getElementById('selisihHarga');
    const persentaseElement = document.getElementById('persentaseSelisih');
    
    if (selisih > 0) {
        selisihElement.textContent = '+Rp ' + formatRupiah(Math.round(Math.abs(selisih)));
        selisihElement.className = 'text-lg font-bold text-red-600';
        persentaseElement.textContent = `+${persentase.toFixed(2)}% (Lebih mahal)`;
        persentaseElement.className = 'text-xs text-red-600';
    } else if (selisih < 0) {
        selisihElement.textContent = '-Rp ' + formatRupiah(Math.round(Math.abs(selisih)));
        selisihElement.className = 'text-lg font-bold text-green-600';
        persentaseElement.textContent = `${persentase.toFixed(2)}% (Lebih murah)`;
        persentaseElement.className = 'text-xs text-green-600';
    } else {
        selisihElement.textContent = 'Rp 0';
        selisihElement.className = 'text-lg font-bold text-gray-600';
        persentaseElement.textContent = 'Sama';
        persentaseElement.className = 'text-xs text-gray-600';
    }
}

// Select bahan baku supplier from dropdown
function selectBahanBakuSupplier() {
    const select = document.getElementById('bahan_baku_supplier_select');
    const selectedOption = select.options[select.selectedIndex];
    const infoDiv = document.getElementById('selectedSupplierInfo');
    
    if (select.value && !selectedOption.disabled) {
        const price = parseFloat(selectedOption.dataset.hargaPerSatuan);
        const stok = parseFloat(selectedOption.dataset.stok);
        
        // Update hidden field with raw number
        document.getElementById('bahan_baku_supplier_id').value = select.value;
        document.getElementById('harga_satuan_forecast').value = formatRupiah(Math.round(price));
        
        // Set raw price for calculations
        document.getElementById('harga_satuan_forecast').dataset.rawValue = price;
        
        // Show supplier info with stock status
        const stokText = stok > 0 ? `${formatRupiah(stok)} ${selectedOption.dataset.satuan}` : 'KOSONG';
        const stokClass = stok > 0 ? 'text-green-700' : 'text-red-700';
        
        document.getElementById('infoStok').textContent = stokText;
        document.getElementById('infoStok').className = `font-semibold ${stokClass}`;
        document.getElementById('infoHarga').textContent = `Rp ${formatRupiah(Math.round(price))} / ${selectedOption.dataset.satuan}`;
        document.getElementById('infoSupplier').textContent = selectedOption.dataset.supplierNama;
        document.getElementById('infoPICPurchasing').textContent = selectedOption.dataset.picPurchasing || 'Belum ditentukan';
        
        infoDiv.classList.remove('hidden');
        
        // Calculate total
        calculateTotal();
    } else {
        // Hide info and reset
        infoDiv.classList.add('hidden');
        document.getElementById('bahan_baku_supplier_id').value = '';
        document.getElementById('harga_satuan_forecast').value = '';
        document.getElementById('harga_satuan_forecast').dataset.rawValue = '';
        document.getElementById('infoPICPurchasing').textContent = '-';
        calculateTotal();
    }
}

// Submit forecast form
document.getElementById('forecastForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    
    // Check if supplier is selected
    const selectedSupplier = document.getElementById('bahan_baku_supplier_select').value;
    if (!selectedSupplier) {
        alert('Silakan pilih bahan baku dan supplier terlebih dahulu');
        return;
    }
    
    // Check if selected supplier is disabled (no stock)
    const selectedOption = document.getElementById('bahan_baku_supplier_select').options[document.getElementById('bahan_baku_supplier_select').selectedIndex];
    if (selectedOption.disabled) {
        alert('Supplier yang dipilih tidak memiliki stok. Silakan pilih supplier lain.');
        return;
    }
    
    // Check if quantity is filled
    const qtyForecast = document.getElementById('qty_forecast').value;
    if (!qtyForecast || parseFloat(qtyForecast) <= 0) {
        alert('Silakan isi quantity forecast yang valid');
        return;
    }
    
    // Check if delivery date is filled
    const deliveryDate = document.getElementById('perkiraan_tanggal_kirim').value;
    if (!deliveryDate) {
        alert('Silakan pilih tanggal perkiraan kirim');
        return;
    }
    
    // Additional validations
    const purchaseOrderId = document.getElementById('purchase_order_id').value;
    const orderDetailId = document.getElementById('order_detail_id').value;
    
    if (!purchaseOrderId) {
        alert('Order ID tidak ditemukan. Silakan refresh halaman dan coba lagi.');
        return;
    }
    
    if (!orderDetailId) {
        alert('Order Detail ID tidak ditemukan. Silakan refresh halaman dan coba lagi.');
        return;
    }
    
    // Validate price data
    const hargaSupplierRaw = document.getElementById('harga_satuan_forecast').dataset.rawValue;
    if (!hargaSupplierRaw || parseFloat(hargaSupplierRaw) <= 0) {
        alert('Harga supplier tidak valid. Silakan pilih supplier lagi.');
        return;
    }
    
    // Set supplier ID
    document.getElementById('bahan_baku_supplier_id').value = selectedSupplier;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
    
    try {
        const formData = new FormData(this);
        
        // Get and validate all required data
        const purchaseOrderId = parseInt(formData.get('purchase_order_id'));
        const orderDetailId = parseInt(formData.get('order_detail_id'));
        const bahanBakuSupplierId = parseInt(selectedSupplier);
        const qtyForecast = parseFloat(formData.get('qty_forecast'));
        const hargaSupplierRaw = parseFloat(document.getElementById('harga_satuan_forecast').dataset.rawValue);
        const tanggalForecast = formData.get('tanggal_forecast') || deliveryDate;
        const hariForecast = formData.get('hari_kirim_forecast') || document.getElementById('perkiraan_hari_kirim').value;
        const catatan = formData.get('catatan') || '';
        
        // Validate all data
        if (isNaN(purchaseOrderId) || purchaseOrderId <= 0) {
            throw new Error('Order ID tidak valid');
        }
        if (isNaN(orderDetailId) || orderDetailId <= 0) {
            throw new Error('Order Detail ID tidak valid');
        }
        if (isNaN(bahanBakuSupplierId) || bahanBakuSupplierId <= 0) {
            throw new Error('Bahan Baku Supplier ID tidak valid');
        }
        if (isNaN(qtyForecast) || qtyForecast <= 0) {
            throw new Error('Quantity forecast tidak valid');
        }
        if (isNaN(hargaSupplierRaw) || hargaSupplierRaw <= 0) {
            throw new Error('Harga supplier tidak valid');
        }
        if (!tanggalForecast) {
            throw new Error('Tanggal forecast tidak valid');
        }
        if (!hariForecast) {
            throw new Error('Hari forecast tidak valid');
        }
        
        // Get and validate CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            throw new Error('CSRF token tidak ditemukan. Silakan refresh halaman.');
        }
        
        // Prepare data for API
        const data = {
            _token: csrfToken,
            purchase_order_id: purchaseOrderId,
            tanggal_forecast: tanggalForecast,
            hari_kirim_forecast: hariForecast,
            catatan: catatan,
            details: [{
                purchase_order_bahan_baku_id: orderDetailId,
                bahan_baku_supplier_id: bahanBakuSupplierId,
                qty_forecast: qtyForecast,
                harga_satuan_forecast: hargaSupplierRaw,
                catatan_detail: null
            }]
        };
        
        console.log('Data yang akan dikirim:', data);
        console.log('FormData values:');
        for (let [key, value] of formData.entries()) {
            console.log(key, ':', value);
        }
        
        // Buat AbortController untuk timeout
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 25000); // 25 detik timeout
        
        const response = await fetch('/procurement/forecasting/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data),
            signal: controller.signal
        });
        
        clearTimeout(timeoutId); // Clear timeout jika request berhasil
        
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        // Try to get response text first
        const responseText = await response.text();
        console.log('Response text:', responseText);
        
        if (!response.ok) {
            // Try to parse as JSON if possible
            let errorMessage = `Server Error (${response.status})`;
            try {
                const errorData = JSON.parse(responseText);
                if (errorData.message) {
                    errorMessage += ': ' + errorData.message;
                }
                if (errorData.errors) {
                    errorMessage += '\nDetail: ' + JSON.stringify(errorData.errors);
                }
            } catch (jsonError) {
                // If not JSON, use raw response text
                if (responseText && responseText.length > 0) {
                    errorMessage += ': ' + responseText.substring(0, 200);
                }
            }
            throw new Error(errorMessage);
        }
        
        // Parse JSON response
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (jsonError) {
            throw new Error('Invalid JSON response from server');
        }
        
        console.log('Response data:', result);
        
        if (result.success) {
            closeForecastModal();
            showSuccessModal(result.forecast);
        } else {
            alert('Gagal membuat forecast: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error creating forecast:', error);
        
        // Show more specific error message
        if (error.name === 'AbortError') {
            closeForecastModal();
            showTimeoutModal();
            return;
        } else if (error.name === 'TypeError' && error.message.includes('fetch')) {
            alert('Terjadi kesalahan jaringan. Pastikan koneksi internet stabil.');
        } else if (error.message.includes('timeout')) {
            alert('Request timeout. Server mungkin sedang sibuk, silakan coba lagi.');
        } else if (error.message.includes('Server Error')) {
            alert('Terjadi kesalahan pada server:\n' + error.message);
        } else {
            alert('Terjadi kesalahan saat membuat forecast:\n' + error.message);
        }
        } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Close modal when clicking outside
document.getElementById('forecastModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeForecastModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeForecastModal();
    }
});

// Show success modal using universal modal API
function showSuccessModal(forecast) {
    const forecastNumber = forecast.no_forecast || 'N/A';
    const totalQty = formatRupiah(forecast.total_qty_forecast || 0);
    const totalHarga = 'Rp ' + formatRupiah(Math.round(forecast.total_harga_forecast || 0));
    
    const message = 'Forecast berhasil dibuat!';
    const description = 'Data forecast telah disimpan ke sistem dengan baik.';
    const additionalInfo = `No. Forecast: ${forecastNumber} • Total Qty: ${totalQty} • Total Harga: ${totalHarga}`;
    
    // Use universal success modal API with forecast type
    if (window.showSuccessModal) {
        window.showSuccessModal('forecast', message, description, additionalInfo, false); // Don't auto-close
    } else {
        // Fallback if universal modal not loaded
        alert(`${message}\n\n${description}\n\n${additionalInfo}`);
        if (window.closeSuccessModal) {
            window.closeSuccessModal();
        }
    }
}

// Note: closeSuccessModal is now handled by the universal modal component with parameter preservation

// Show timeout modal
function showTimeoutModal() {
    document.getElementById('timeoutModal').classList.remove('hidden');
}

// Close timeout modal and refresh
function closeTimeoutModal() {
    document.getElementById('timeoutModal').classList.add('hidden');
    
    // Use global refresh function to preserve all parameters
    if (window.refreshWithPreservedParams) {
        window.refreshWithPreservedParams();
    } else {
        // Fallback to simple refresh
        window.location.reload();
    }
}

// Close timeout modal only (without refresh)
function closeTimeoutModalOnly() {
    document.getElementById('timeoutModal').classList.add('hidden');
}
</script>
