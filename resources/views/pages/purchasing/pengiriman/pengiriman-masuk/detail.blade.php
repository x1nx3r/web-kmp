{{-- Meta tags untuk CSRF --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    // Define user role access for this modal
    $currentUser = Auth::user();
    $isPIC = $pengiriman->purchasing_id === $currentUser->id;
    $canManagePengiriman = in_array($currentUser->role, ['direktur', 'manager_purchasing', 'staff_purchasing']);
    $canEdit = $canManagePengiriman && ($currentUser->role !== 'staff_purchasing' || $isPIC);
@endphp

{{-- Modal Header - Sticky & Responsive --}}
<div class="sticky top-0 z-10 flex items-center justify-between p-4 sm:p-6 border-b border-gray-200 bg-blue-600 rounded-t-xl">
    <div class="flex items-center space-x-2 sm:space-x-4">
        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center shadow-sm">
            <i class="fas fa-truck text-blue-600 text-lg sm:text-xl"></i>
        </div>
        <div>
            <h3 class="text-lg sm:text-xl font-bold text-white">Pengiriman Masuk</h3>
            <p class="text-xs sm:text-sm text-blue-100 opacity-90 hidden sm:block">Masukkan Data dengan Benar</p>
        </div>
    </div>
    <button type="button" onclick="closeAksiModal()" 
            class="text-white hover:text-gray-200 hover:bg-white hover:bg-opacity-20 p-2 rounded-full transition-all duration-200">
        <i class="fas fa-times text-lg sm:text-xl"></i>
    </button>
</div>

{{-- Modal Content - Scrollable & Responsive --}}
<div class="overflow-y-auto max-h-[calc(90vh-160px)] p-3 sm:p-6 space-y-4 sm:space-y-6">

    <form id="pengirimanForm" method="POST" action="{{ route('purchasing.pengiriman.submit') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="pengiriman_id" value="{{ $pengiriman->id }}">
        
        {{-- Card 1: Data PO & PIC Purchasing - Responsive --}}
        <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 shadow-sm mb-4">
            <div class="flex items-center mb-3 sm:mb-4 pb-3 border-b border-gray-200">
                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-2 sm:mr-3 flex-shrink-0">
                    <i class="fas fa-file-alt text-blue-600 text-sm sm:text-base"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 truncate">Data Purchase Order (PO)</h3>
                    <p class="text-xs text-gray-500 hidden sm:block">Informasi order dari klien</p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">No. Purchase Order</label>
                    <input type="text" 
                           value="{{ $pengiriman->order->po_number ?? '-' }}" 
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-900 font-medium cursor-not-allowed" 
                           readonly>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">PIC Procurement</label>
                    <input type="text" 
                           value="{{ $pengiriman->purchasing->nama ?? '-' }}" 
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-900 font-medium cursor-not-allowed" 
                           readonly>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Total Qty PO</label>
                    <input type="text" 
                           value="{{ $pengiriman->order && $pengiriman->order->orderDetails ? number_format($pengiriman->order->orderDetails->sum('qty'), 2, ',', '.') . ' KG' : '-' }}" 
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-900 font-medium cursor-not-allowed" 
                           readonly>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Total Harga PO (Harga Jual ke Klien)</label>
                    <input type="text" 
                           value="{{ $pengiriman->order->total_amount ? 'Rp ' . number_format($pengiriman->order->total_amount, 2, ',', '.') : '-' }}" 
                           class="w-full px-3 py-2 text-sm border border-green-300 rounded-lg bg-green-50 text-green-700 font-semibold cursor-not-allowed" 
                           readonly>
                </div>
            </div>
        </div>

        {{-- Card 2: Data Forecasting - Responsive --}}
        <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 shadow-sm mb-4">
            <div class="flex items-center mb-3 sm:mb-4 pb-3 border-b border-gray-200">
                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-green-100 rounded-lg flex items-center justify-center mr-2 sm:mr-3 shrink-0">
                    <i class="fas fa-chart-line text-green-600 text-sm sm:text-base"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 truncate">Data Forecasting</h3>
                    <p class="text-xs text-gray-500 hidden sm:block">Informasi perencanaan pengiriman</p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">No. Forecast</label>
                    <input type="text" 
                           value="{{ $pengiriman->forecast->no_forecast ?? '-' }}" 
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-900 font-medium cursor-not-allowed" 
                           readonly>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Forecast</label>
                    <input type="text" 
                           value="{{ $pengiriman->forecast->tanggal_forecast ? $pengiriman->forecast->tanggal_forecast->format('d M Y') : '-' }}" 
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-900 font-medium cursor-not-allowed" 
                           readonly>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Hari Kirim</label>
                    <input type="text" 
                           value="{{ $pengiriman->forecast->hari_kirim_forecast ?? '-' }}" 
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-900 font-medium cursor-not-allowed" 
                           readonly>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Total Qty</label>
                    <input type="text" 
                           value="{{ $pengiriman->forecast ? number_format($pengiriman->forecast->total_qty_forecast, 2, ',', '.') . ' kg' : '-' }}" 
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-900 font-medium cursor-not-allowed" 
                           readonly>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Total Harga Forecast</label>
                    <input type="text" 
                           value="{{ $pengiriman->forecast ? 'Rp ' . number_format($pengiriman->forecast->total_harga_forecast, 2, ',', '.') : '-' }}" 
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-900 font-medium cursor-not-allowed" 
                           readonly>
                </div>
            </div>
        </div>

        {{-- Card 3: Form Input Pengiriman & Detail - Responsive --}}
        <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 shadow-sm mb-4">
            <div class="flex items-center mb-3 sm:mb-4 pb-3 border-b border-gray-200">
                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-2 sm:mr-3 shrink-0">
                    <i class="fas fa-shipping-fast text-orange-600 text-sm sm:text-base"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 truncate">Form Input Pengiriman & Detail</h3>
                    <p class="text-xs text-gray-500 hidden sm:block">Data pengiriman dan barang yang dikirim</p>
                </div>
            </div>
            
            {{-- Input Pengiriman - Responsive --}}
            <div class="mb-4 sm:mb-6">
                <h4 class="text-sm font-semibold text-gray-700 mb-2 sm:mb-3 flex items-center">
                    <i class="fas fa-clipboard-list text-orange-600 mr-2 text-sm"></i>
                    <span class="text-sm sm:text-base">Data Pengiriman</span>
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">No. Pengiriman</label>
                        <input type="text" 
                               name="no_pengiriman_display" 
                               id="no_pengiriman_display"
                               value="{{ $pengiriman->no_pengiriman ?? 'Auto Generate' }}"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed"
                               readonly
                               placeholder="Akan di-generate otomatis">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">
                            Tanggal Kirim <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               name="tanggal_kirim" 
                               id="tanggal_kirim"
                               value="{{ old('tanggal_kirim', $pengiriman->tanggal_kirim ? $pengiriman->tanggal_kirim->format('Y-m-d') : '') }}"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 {{ !$canEdit ? 'bg-gray-50 cursor-not-allowed' : '' }}"
                               onchange="updateHariKirim()"
                               {{ !$canEdit ? 'readonly' : 'required' }}>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Hari Kirim</label>
                        <input type="text" 
                               name="hari_kirim" 
                               id="hari_kirim"
                               value="{{ old('hari_kirim', $pengiriman->hari_kirim ?? '') }}"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                               readonly
                               placeholder="Akan terisi otomatis">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Total Qty (Auto)</label>
                        <input type="text" 
                               name="total_qty_kirim_display" 
                               id="total_qty_kirim_display"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                               readonly>
                        <input type="hidden" name="total_qty_kirim" id="total_qty_kirim">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Total Harga Beli (Auto)</label>
                        <input type="text" 
                               name="total_harga_kirim_display" 
                               id="total_harga_kirim_display"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                               readonly>
                        <input type="hidden" name="total_harga_kirim" id="total_harga_kirim">
                    </div>
                </div>
                
                {{-- Bukti Foto Bongkar - Full Width Section with Better Layout --}}
                <div class="mt-4 p-4 bg-orange-50 border border-orange-200 rounded-lg">
                    <div class="mb-3">
                        <label class="block text-sm font-semibold text-orange-800 mb-1 flex items-center">
                            <i class="fas fa-camera mr-2"></i>
                            Bukti Foto Bongkar
                        </label>
                        <p class="text-xs text-orange-600">Upload foto bukti bongkar barang (JPG/PNG/PDF - Max 10MB per file)</p>
                    </div>
                    
                    {{-- Container for multiple file inputs --}}
                    <div id="bukti-foto-container" class="space-y-2 mb-3">
                        {{-- First file input --}}
                        <div class="bukti-foto-item flex items-center space-x-2">
                            <input type="file" 
                                   name="bukti_foto_bongkar[]" 
                                   class="bukti-foto-input flex-1 px-3 py-2 text-sm border border-orange-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white {{ !$canEdit ? 'bg-gray-50 cursor-not-allowed' : '' }}"
                                   accept="image/*,application/pdf"
                                   {{ !$canEdit ? 'disabled' : '' }}>
                            <button type="button" 
                                    onclick="removeBuktiFotoInput(this)"
                                    class="hidden px-3 py-2 bg-red-500 text-white text-xs rounded-lg hover:bg-red-600 transition-colors flex-shrink-0 {{ !$canEdit ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    {{ !$canEdit ? 'disabled' : '' }}>
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    {{-- Add more button --}}
                    @if($canEdit)
                    <div class="mb-3">
                        <button type="button" 
                                onclick="addBuktiFotoInput()"
                                class="px-4 py-2 bg-blue-500 text-white text-sm rounded-lg hover:bg-blue-600 transition-colors flex items-center space-x-2 shadow-sm">
                            <i class="fas fa-plus"></i>
                            <span>Tambah File Lagi</span>
                        </button>
                    </div>
                    @endif
                    
                    {{-- Display existing photos --}}
                    @php
                        $existingPhotos = $pengiriman->bukti_foto_bongkar_array ?? [];
                    @endphp
                    @if(!empty($existingPhotos))
                        <div class="mt-4 p-3 bg-white border border-orange-200 rounded-lg">
                            <p class="text-sm font-semibold text-orange-700 mb-3 flex items-center">
                                <i class="fas fa-images mr-2"></i>
                                Foto yang Sudah Diupload ({{ count($existingPhotos) }} file)
                            </p>
                            <div class="space-y-2">
                                @foreach($existingPhotos as $index => $photo)
                                    <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors">
                                        <a href="{{ asset('storage/pengiriman/bukti/' . $photo) }}" 
                                           target="_blank"
                                           class="text-sm text-blue-600 hover:text-blue-800 flex items-center flex-1 min-w-0">
                                            <i class="fas fa-file-image mr-2 text-lg flex-shrink-0"></i>
                                            <span class="truncate font-medium">{{ $photo }}</span>
                                        </a>
                                        @if($canEdit)
                                        <button type="button"
                                                onclick="deleteExistingPhoto('{{ $pengiriman->id }}', '{{ $photo }}', this)"
                                                class="ml-3 px-3 py-1.5 bg-red-100 text-red-600 text-sm rounded-lg hover:bg-red-200 transition-colors flex items-center space-x-1 flex-shrink-0"
                                                title="Hapus foto ini">
                                            <i class="fas fa-trash-alt"></i>
                                            <span class="hidden sm:inline">Hapus</span>
                                        </button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            @if($pengiriman->bukti_foto_bongkar_uploaded_at)
                                <div class="mt-3 pt-3 border-t border-orange-200">
                                    <p class="text-xs text-orange-600 flex items-center">
                                        <i class="fas fa-clock mr-2"></i>
                                        Upload terakhir: <span class="font-semibold ml-1">{{ $pengiriman->bukti_foto_bongkar_uploaded_at->diffForHumans() }}</span>
                                        <span class="text-orange-400 ml-1">({{ $pengiriman->bukti_foto_bongkar_uploaded_at->format('d/m/Y H:i') }})</span>
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif
                    
                    <div class="mt-3 p-2 bg-orange-100 rounded-lg">
                        <p class="text-xs text-orange-700 flex items-start">
                            <i class="fas fa-info-circle mr-2 mt-0.5 flex-shrink-0"></i>
                            <span>Anda dapat mengupload beberapa file sekaligus. Klik tombol <strong>"Tambah File Lagi"</strong> untuk menambah form upload baru.</span>
                        </p>
                    </div>
                </div>
                
                {{-- Info: Foto tanda terima diupload di tab menunggu verifikasi - Responsive --}}
                <div class="mt-3 sm:mt-4 p-3 sm:p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start space-x-2 sm:space-x-3">
                        <div class="shrink-0">
                            <i class="fas fa-info-circle text-blue-500 text-base sm:text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 class="text-xs sm:text-sm font-semibold text-blue-800 mb-1">Informasi Upload Foto Tanda Terima</h4>
                            <p class="text-xs text-blue-700">
                                Upload foto tanda terima dilakukan di tab <strong>Menunggu Verifikasi</strong> setelah pengiriman disubmit. 
                                Anda dapat mengupload foto langsung pada kartu pengiriman di tab tersebut.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detail Pengiriman - Responsive --}}
            <div>
                <div class="mb-3 sm:mb-4">
                    <h4 class="text-sm font-semibold text-gray-700 flex items-center">
                        <i class="fas fa-boxes text-orange-600 mr-2 text-sm"></i>
                        <span class="text-sm sm:text-base">Detail Barang Pengiriman</span>
                    </h4>
                    <p class="text-xs text-gray-500 mt-1 ml-6">Atur qty kirim dan harga beli untuk setiap barang</p>
                    
                    
                </div>
                
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <div class="inline-block min-w-full align-middle">
                        <div class="overflow-hidden">
                            <table class="min-w-full border border-gray-300 rounded-lg">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b whitespace-nowrap">Bahan Baku</th>
                                <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b whitespace-nowrap hidden sm:table-cell">Supplier</th>
                                <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b whitespace-nowrap">Qty (kg)</th>
                                <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b whitespace-nowrap hidden md:table-cell">Harga Beli</th>
                                <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b whitespace-nowrap hidden md:table-cell">Harga Jual</th>
                                <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b whitespace-nowrap">Total Beli</th>
                            </tr>
                        </thead>
                        <tbody id="pengirimanDetailContainer" class="bg-white divide-y divide-gray-200">
                            @forelse($pengiriman->pengirimanDetails ?? [] as $index => $detail)
                                @php
                                    // Get klien_id from order
                                    $klienId = $pengiriman->order->klien_id ?? null;
                                    
                                    // ✅ PRIORITAS 1: Gunakan harga HISTORIS yang sudah tersimpan di database
                                    // Ini adalah harga yang FROZEN saat pengiriman dibuat, tidak boleh berubah!
                                    $hargaBeli = $detail->harga_satuan ?? 0;
                                    
                                    // ✅ FALLBACK: Hanya jika data lama yang belum punya harga tersimpan (data corrupt/migration)
                                    if ($hargaBeli == 0 && $detail->bahan_baku_supplier_id) {
                                        // Try to get client-specific price
                                        if ($klienId) {
                                            $bahanBakuSupplierKlien = \App\Models\BahanBakuSupplierKlien::where('bahan_baku_supplier_id', $detail->bahan_baku_supplier_id)
                                                ->where('klien_id', $klienId)
                                                ->first();
                                            $hargaBeli = $bahanBakuSupplierKlien ? $bahanBakuSupplierKlien->harga_per_satuan : 0;
                                        }
                                        
                                        // Final fallback to default supplier price
                                        if ($hargaBeli == 0) {
                                            $latestHarga = $detail->bahanBakuSupplier->riwayatHarga->first();
                                            $hargaBeli = $latestHarga ? $latestHarga->harga_baru : ($detail->bahanBakuSupplier->harga_per_satuan ?? 0);
                                        }
                                    }
                                    
                                    // Get harga jual (PO price to client)
                                    $hargaJual = 0;
                                    
                                    // ✅ Try to get harga_jual from orderDetail relation
                                    if ($detail->orderDetail) {
                                        $hargaJual = $detail->orderDetail->harga_jual ?? 0;
                                    }
                                    
                                    // ✅ FALLBACK: If orderDetail is null or harga_jual is 0, find matching order_detail by bahan baku name
                                    if ($hargaJual == 0 && $pengiriman->order && $detail->bahanBakuSupplier) {
                                        $namaBahanBaku = $detail->bahanBakuSupplier->nama;
                                        $matchingOrderDetail = $pengiriman->order->orderDetails->first(function($od) use ($namaBahanBaku) {
                                            return $od->bahanBakuKlien && $od->bahanBakuKlien->nama === $namaBahanBaku;
                                        });
                                        
                                        if ($matchingOrderDetail) {
                                            $hargaJual = $matchingOrderDetail->harga_jual ?? 0;
                                        }
                                    }
                                    
                                    // Calculate margin - Profit Margin: (margin / harga jual) * 100
                                    $marginPerUnit = $hargaJual - $hargaBeli;
                                    $marginPercentage = $hargaJual > 0 ? (($marginPerUnit / $hargaJual) * 100) : 0;
                                    
                                    // Total calculations
                                    $totalHargaBeli = ($detail->qty_kirim ?? 0) * $hargaBeli;
                                    $totalHargaJual = ($detail->qty_kirim ?? 0) * $hargaJual;
                                    $totalMargin = $totalHargaJual - $totalHargaBeli;
                                @endphp
                                <tr class="detail-item" data-index="{{ $index }}" 
                                    data-harga-beli="{{ $hargaBeli }}" 
                                    data-harga-jual="{{ $hargaJual }}">
                                    <!-- Bahan Baku -->
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 border-b">
                                        <input type="hidden" name="details[{{ $index }}][bahan_baku_supplier_id]" value="{{ $detail->bahan_baku_supplier_id }}">
                                        <div class="text-xs sm:text-sm font-medium text-gray-900">
                                            {{ $detail->bahanBakuSupplier->nama ?? 'Unknown Bahan Baku' }}
                                        </div>
                                        <!-- Supplier name - visible on mobile -->
                                        <div class="text-xs text-gray-500 mt-1 sm:hidden">
                                            {{ $detail->bahanBakuSupplier->supplier->nama ?? 'Unknown Supplier' }}
                                        </div>
                                    </td>
                                    
                                    <!-- Supplier - hidden on mobile -->
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 border-b hidden sm:table-cell">
                                        <div class="text-xs sm:text-sm font-medium text-gray-900">
                                            {{ $detail->bahanBakuSupplier->supplier->nama ?? 'Unknown Supplier' }}
                                        </div>
                                    </td>
                                    
                                    <!-- Qty Kirim -->
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 border-b">
                                        <input type="number" 
                                               name="details[{{ $index }}][qty_kirim]" 
                                               value="{{ old('details.' . $index . '.qty_kirim', $detail->qty_kirim ?? 0) }}"
                                               class="qty-input w-full px-2 sm:px-3 py-1.5 sm:py-2 border border-gray-300 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ !$canEdit ? 'bg-gray-50 cursor-not-allowed' : '' }}" 
                                               step="0.01" min="0" 
                                               onchange="calculateSubtotal({{ $index }})"
                                               oninput="calculateSubtotal({{ $index }})"
                                               {{ !$canEdit ? 'readonly' : 'required' }}>
                                    </td>
                                    
                                    <!-- Harga Beli - hidden on mobile - EDITABLE -->
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 border-b hidden md:table-cell">
                                        <input type="number" 
                                               name="details[{{ $index }}][harga_satuan]" 
                                               value="{{ $hargaBeli }}"
                                               class="harga-beli-input w-full px-2 sm:px-3 py-1.5 sm:py-2 border rounded-lg text-xs sm:text-sm focus:ring-2 {{ !$canEdit ? 'bg-gray-50 border-gray-300 cursor-not-allowed' : 'bg-white border-orange-300 focus:ring-orange-500 focus:border-orange-500' }}" 
                                               step="0.01" 
                                               min="0"
                                               data-original-price="{{ $hargaBeli }}"
                                               onchange="handleHargaBeliChange({{ $index }}, 'desktop')"
                                               oninput="calculateSubtotal({{ $index }})"
                                               {{ !$canEdit ? 'readonly' : '' }}>
                                        <div class="text-xs text-gray-500 mt-1 flex items-center justify-between">
                                            <span class="harga-beli-display">Rp {{ number_format($hargaBeli, 2, ',', '.') }}/{{ $detail->bahanBakuSupplier->satuan ?? 'kg' }}</span>
                                            @if($canEdit)
                                            <button type="button" 
                                                    onclick="resetHargaBeli({{ $index }})"
                                                    class="text-blue-500 hover:text-blue-700 text-xs font-medium"
                                                    title="Reset ke harga asli">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    <!-- Harga Jual - hidden on mobile -->
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 border-b hidden md:table-cell">
                                        <div class="text-xs sm:text-sm font-medium text-gray-900">
                                            Rp {{ number_format($hargaJual, 2, ',', '.') }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            /{{ $detail->bahanBakuSupplier->satuan ?? 'kg' }}
                                        </div>
                                    </td>
                                    
                                    <!-- Total Harga Beli -->
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 border-b">
                                        <!-- Harga Beli Mobile - Editable -->
                                        <div class="md:hidden mb-2">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Harga Beli/{{ $detail->bahanBakuSupplier->satuan ?? 'kg' }}:</label>
                                            <div class="flex items-center space-x-2">
                                                <input type="number" 
                                                       name="details_mobile[{{ $index }}][harga_satuan]" 
                                                       value="{{ $hargaBeli }}"
                                                       class="harga-beli-input-mobile flex-1 px-2 py-1.5 border rounded-lg text-xs focus:ring-2 {{ !$canEdit ? 'bg-gray-50 border-gray-300 cursor-not-allowed' : 'bg-white border-orange-300 focus:ring-orange-500 focus:border-orange-500' }}" 
                                                       step="0.01" 
                                                       min="0"
                                                       data-original-price="{{ $hargaBeli }}"
                                                       onchange="handleHargaBeliChange({{ $index }}, 'mobile')"
                                                       oninput="syncHargaBeli({{ $index }}, 'mobile'); calculateSubtotal({{ $index }})"
                                                       {{ !$canEdit ? 'readonly' : '' }}>
                                                @if($canEdit)
                                                <button type="button" 
                                                        onclick="resetHargaBeli({{ $index }})"
                                                        class="px-2 py-1.5 text-blue-500 hover:text-blue-700 text-xs"
                                                        title="Reset">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Total Harga (Auto Calculated) -->
                                        <input type="number" 
                                               name="details[{{ $index }}][total_harga]" 
                                               value="{{ $totalHargaBeli }}"
                                               class="total-harga-input w-full px-2 sm:px-3 py-1.5 sm:py-2 bg-blue-50 border border-blue-300 rounded-lg text-xs sm:text-sm cursor-not-allowed font-semibold text-blue-700" 
                                               readonly>
                                        <div class="text-xs text-blue-600 mt-1 font-medium total-harga-display">
                                            Rp {{ number_format($totalHargaBeli, 2, ',', '.') }}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                        <i class="fas fa-inbox text-3xl text-gray-300 mb-2"></i>
                                        <p>Belum ada detail barang pengiriman</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                        </div>
                    </div>
                </div>

                {{-- Total Summary - Responsive --}}
                <div class="bg-gradient-to-r from-blue-50 to-green-50 border border-blue-200 rounded-lg p-3 sm:p-5 mt-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                        <!-- Total Qty -->
                        <div class="bg-white rounded-lg p-3 shadow-sm">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                                <span class="text-xs font-medium text-gray-600 mb-1 sm:mb-0">Total Qty:</span>
                                <span class="text-lg sm:text-base font-bold text-blue-600" id="totalQtyKirim">0 kg</span>
                            </div>
                        </div>
                        
                        <!-- Total Harga Beli -->
                        <div class="bg-white rounded-lg p-3 shadow-sm">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                                <span class="text-xs font-medium text-gray-600 mb-1 sm:mb-0">Total Harga Beli:</span>
                                <span class="text-lg sm:text-base font-bold text-orange-600" id="totalHargaBeli">Rp 0</span>
                            </div>
                        </div>
                        
                        <!-- Total Harga Jual -->
                        <div class="bg-white rounded-lg p-3 shadow-sm">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                                <span class="text-xs font-medium text-gray-600 mb-1 sm:mb-0">Total Harga Jual:</span>
                                <span class="text-lg sm:text-base font-bold text-green-600" id="totalHargaJual">Rp 0</span>
                            </div>
                        </div>
                        
                        <!-- Total Margin -->
                        <div class="bg-white rounded-lg p-3 shadow-sm border-2 border-green-300">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-1">
                                <span class="text-xs font-medium text-gray-600 mb-1 sm:mb-0">Total Margin:</span>
                                <span class="text-lg sm:text-base font-bold text-green-600" id="totalMargin">Rp 0</span>
                            </div>
                            <div class="text-left sm:text-right">
                                <span class="text-xs text-green-500 font-semibold" id="marginPercentage">0%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 4: Catatan Pengiriman - Responsive --}}
        <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 shadow-sm">
            <div class="flex items-center mb-3 sm:mb-4 pb-3 border-b border-gray-200">
                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-2 sm:mr-3 shrink-0">
                    <i class="fas fa-sticky-note text-orange-600 text-sm sm:text-base"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 truncate">Catatan Pengiriman</h3>
                    <p class="text-xs text-gray-500 hidden sm:block">Informasi catatan terkait pengiriman</p>
                </div>
            </div>
            
            <div class="space-y-3 sm:space-y-4">
                {{-- Catatan (Read-only from previous status) --}}
                @if($pengiriman->catatan)
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">
                        Catatan Sebelumnya (Read-only)
                    </label>
                    <textarea 
                        name="catatan_display" 
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                        rows="2"
                        readonly>{{ $pengiriman->catatan }}</textarea>
                </div>
                @endif
                
                {{-- Catatan Refraksi (Editable) --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">
                        Catatan Refraksi <span class="text-gray-400">(Opsional)</span>
                    </label>
                    <textarea 
                        name="catatan_refraksi" 
                        id="catatan_refraksi"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 {{ !$canEdit ? 'bg-gray-50 cursor-not-allowed' : '' }}" 
                        rows="3"
                        placeholder="{{ $canEdit ? 'Masukkan catatan refraksi (opsional)...' : 'Mode lihat saja - tidak dapat mengedit' }}"
                        {{ !$canEdit ? 'readonly' : '' }}>{{ old('catatan_refraksi', $pengiriman->catatan_refraksi ?? '') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        Catatan terkait pemeriksaan dan refraksi barang yang dikirim
                    </p>
                </div>
            </div>
        </div>

    </form>

</div>
{{-- End Modal Content --}}

{{-- Footer - Sticky & Responsive --}}
<div class="sticky bottom-0 bg-white z-10 flex flex-col sm:flex-row justify-between items-stretch sm:items-center p-3 sm:p-6 border-t border-gray-200 rounded-b-xl gap-3 sm:gap-0">
    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3 order-2 sm:order-1">
        <button type="button" onclick="closeAksiModal()" 
                class="w-full sm:w-auto px-4 sm:px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors font-medium text-sm">
            <i class="fas fa-times mr-2"></i>
            Tutup
        </button>
        
        @if($canEdit)
            {{-- Batalkan button - Only for authorized users --}}
            <button type="button" onclick="openBatalModal()" 
                    class="w-full sm:w-auto px-4 sm:px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-medium text-sm">
                <i class="fas fa-ban mr-2"></i>
                <span class="hidden sm:inline">Jadikan Pengiriman Batal</span>
                <span class="sm:hidden">Batalkan</span>
            </button>
        @endif
    </div>
    
    @if($canEdit)
        {{-- Ajukan Verifikasi button - Only for authorized users --}}
        <button type="button" onclick="submitPengiriman()" 
                class="w-full sm:w-auto px-6 sm:px-8 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors font-semibold shadow-md hover:shadow-lg text-sm order-1 sm:order-2">
            <i class="fas fa-paper-plane mr-2"></i>
            Ajukan Verifikasi
        </button>
    @else
        {{-- Read-only indicator for unauthorized users --}}
        <div class="w-full sm:w-auto px-6 sm:px-8 py-2 bg-gray-100 text-gray-500 rounded-lg font-semibold text-sm order-1 sm:order-2 text-center border border-gray-300">
            <i class="fas fa-lock mr-2"></i>
            Mode Lihat Saja
        </div>
    @endif
</div>

{{-- SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- JavaScript khusus untuk modal aksi --}}
<script>
/**
 * Sync harga beli between mobile and desktop views
 * Ensures both inputs always have the same value
 */
function syncHargaBeli(index, source) {
    const row = document.querySelector(`.detail-item[data-index="${index}"]`);
    if (!row) return;
    
    const desktopInput = row.querySelector('.harga-beli-input');
    const mobileInput = row.querySelector('.harga-beli-input-mobile');
    
    if (!desktopInput || !mobileInput) return;
    
    if (source === 'mobile') {
        // Sync from mobile to desktop
        desktopInput.value = mobileInput.value;
    } else {
        // Sync from desktop to mobile
        mobileInput.value = desktopInput.value;
    }
    
    // Update data attribute for reference
    row.setAttribute('data-harga-beli', desktopInput.value);
}

/**
 * Handle harga beli change with validation
 * Shows warning if user changes from original price
 */
function handleHargaBeliChange(index, source) {
    const row = document.querySelector(`.detail-item[data-index="${index}"]`);
    if (!row) return;
    
    const input = source === 'mobile' 
        ? row.querySelector('.harga-beli-input-mobile')
        : row.querySelector('.harga-beli-input');
    
    if (!input) return;
    
    const currentValue = parseFloat(input.value) || 0;
    const originalPrice = parseFloat(input.getAttribute('data-original-price')) || 0;
    
    // Validate minimum value
    if (currentValue < 0) {
        Swal.fire({
            icon: 'error',
            title: 'Nilai Tidak Valid',
            text: 'Harga beli tidak boleh negatif!',
            confirmButtonColor: '#3085d6'
        });
        input.value = originalPrice;
        syncHargaBeli(index, source);
        calculateSubtotal(index);
        return;
    }
    
    // Show warning if price changed significantly (more than 20%)
    const priceDiff = Math.abs(currentValue - originalPrice);
    const percentDiff = originalPrice > 0 ? (priceDiff / originalPrice) * 100 : 0;
    
    if (percentDiff > 20 && currentValue !== originalPrice) {
        Swal.fire({
            icon: 'warning',
            title: 'Perubahan Harga Signifikan',
            html: `
                <div class="text-left">
                    <p class="mb-2">Anda mengubah harga beli dengan perbedaan <strong>${percentDiff.toFixed(2)}%</strong></p>
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        <li>Harga Asli: <span class="font-bold text-blue-600">Rp ${formatNumber(originalPrice)}</span></li>
                        <li>Harga Baru: <span class="font-bold text-orange-600">Rp ${formatNumber(currentValue)}</span></li>
                        <li>Selisih: <span class="font-bold ${currentValue > originalPrice ? 'text-red-600' : 'text-green-600'}">Rp ${formatNumber(priceDiff)}</span></li>
                    </ul>
                    <p class="mt-3 text-xs text-gray-600">Pastikan harga yang Anda masukkan sudah benar.</p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Reset ke Harga Asli',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280'
        }).then((result) => {
            if (!result.isConfirmed) {
                // Reset to original price
                input.value = originalPrice;
                syncHargaBeli(index, source);
                calculateSubtotal(index);
            } else {
                // Continue with new price
                syncHargaBeli(index, source);
                calculateSubtotal(index);
            }
        });
    } else {
        // Small change or no change, just sync and calculate
        syncHargaBeli(index, source);
        calculateSubtotal(index);
    }
    
    // Update border color indicator
    updatePriceIndicator(index);
}

/**
 * Reset harga beli to original price from database
 */
function resetHargaBeli(index) {
    const row = document.querySelector(`.detail-item[data-index="${index}"]`);
    if (!row) return;
    
    const desktopInput = row.querySelector('.harga-beli-input');
    const mobileInput = row.querySelector('.harga-beli-input-mobile');
    
    if (!desktopInput) return;
    
    const originalPrice = parseFloat(desktopInput.getAttribute('data-original-price')) || 0;
    
    Swal.fire({
        icon: 'question',
        title: 'Reset Harga',
        text: `Reset harga beli ke nilai asli: Rp ${formatNumber(originalPrice)}?`,
        showCancelButton: true,
        confirmButtonText: 'Ya, Reset',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6b7280'
    }).then((result) => {
        if (result.isConfirmed) {
            desktopInput.value = originalPrice;
            if (mobileInput) mobileInput.value = originalPrice;
            
            calculateSubtotal(index);
            updatePriceIndicator(index);
            
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Harga beli telah direset ke nilai asli',
                timer: 1500,
                showConfirmButton: false
            });
        }
    });
}

/**
 * Update visual indicator for price changes
 */
function updatePriceIndicator(index) {
    const row = document.querySelector(`.detail-item[data-index="${index}"]`);
    if (!row) return;
    
    const desktopInput = row.querySelector('.harga-beli-input');
    const mobileInput = row.querySelector('.harga-beli-input-mobile');
    
    if (!desktopInput) return;
    
    const currentValue = parseFloat(desktopInput.value) || 0;
    const originalPrice = parseFloat(desktopInput.getAttribute('data-original-price')) || 0;
    
    const inputs = [desktopInput, mobileInput].filter(i => i);
    
    inputs.forEach(input => {
        if (Math.abs(currentValue - originalPrice) > 0.01) {
            // Price changed - show orange border
            input.classList.remove('border-orange-300', 'border-green-300');
            input.classList.add('border-yellow-400', 'ring-2', 'ring-yellow-200');
        } else {
            // Price same as original - normal border
            input.classList.remove('border-yellow-400', 'ring-2', 'ring-yellow-200', 'border-green-300');
            input.classList.add('border-orange-300');
        }
    });
}

/**
 * Calculate subtotal for a specific row
 * Updates: total harga, display text, and grand totals
 */
function calculateSubtotal(index) {
    const row = document.querySelector(`.detail-item[data-index="${index}"]`);
    if (!row) return;
    
    // Get inputs
    const qtyInput = row.querySelector('.qty-input');
    const hargaBeliInput = row.querySelector('.harga-beli-input') || row.querySelector('.harga-beli-input-mobile');
    const totalHargaInput = row.querySelector('.total-harga-input');
    
    if (!qtyInput || !hargaBeliInput || !totalHargaInput) return;
    
    // Parse values with validation
    const qty = Math.max(0, parseFloat(qtyInput.value) || 0);
    const hargaBeli = Math.max(0, parseFloat(hargaBeliInput.value) || 0);
    
    // Calculate total
    const totalHarga = qty * hargaBeli;
    
    // Update total input
    totalHargaInput.value = totalHarga.toFixed(2);
    
    // Update data attribute
    row.setAttribute('data-harga-beli', hargaBeli.toFixed(2));
    
    // Update display text
    const hargaBeliDisplay = row.querySelector('.harga-beli-display');
    if (hargaBeliDisplay) {
        const satuan = hargaBeliDisplay.textContent.split('/')[1] || 'kg';
        hargaBeliDisplay.textContent = `Rp ${formatNumber(hargaBeli)}/${satuan}`;
    }
    
    const totalDisplay = row.querySelector('.total-harga-display');
    if (totalDisplay) {
        totalDisplay.textContent = `Rp ${formatNumber(totalHarga)}`;
    }
    
    // Update grand totals
    updateTotals();
}

/**
 * Update all grand totals (qty, harga beli, harga jual, margin)
 * Reads from actual input values, not data attributes
 */
function updateTotals() {
    let totalQty = 0;
    let totalHargaBeli = 0;
    let totalHargaJual = 0;
    
    document.querySelectorAll('.detail-item').forEach(row => {
        // Get qty
        const qtyInput = row.querySelector('.qty-input');
        const qty = Math.max(0, parseFloat(qtyInput?.value) || 0);
        
        // Get harga beli from input (current value, may be edited)
        const hargaBeliInput = row.querySelector('.harga-beli-input') || row.querySelector('.harga-beli-input-mobile');
        const hargaBeli = Math.max(0, parseFloat(hargaBeliInput?.value) || 0);
        
        // Get harga jual from data attribute (readonly, from database)
        const hargaJual = Math.max(0, parseFloat(row.getAttribute('data-harga-jual')) || 0);
        
        // Calculate totals
        totalQty += qty;
        totalHargaBeli += (qty * hargaBeli);
        totalHargaJual += (qty * hargaJual);
    });
    
    const totalMargin = totalHargaJual - totalHargaBeli;
    const marginPercentage = totalHargaJual > 0 ? ((totalMargin / totalHargaJual) * 100) : 0;
    
    // Update display elements
    const totalQtyElem = document.getElementById('totalQtyKirim');
    const totalHargaBeliElem = document.getElementById('totalHargaBeli');
    const totalHargaJualElem = document.getElementById('totalHargaJual');
    const totalMarginElem = document.getElementById('totalMargin');
    const marginPercentageElem = document.getElementById('marginPercentage');
    
    if (totalQtyElem) totalQtyElem.textContent = `${formatNumber(totalQty)} kg`;
    if (totalHargaBeliElem) totalHargaBeliElem.textContent = `Rp ${formatNumber(totalHargaBeli)}`;
    if (totalHargaJualElem) totalHargaJualElem.textContent = `Rp ${formatNumber(totalHargaJual)}`;
    
    if (totalMarginElem) {
        totalMarginElem.textContent = `Rp ${formatNumber(totalMargin)}`;
        // Dynamic color based on profit/loss
        totalMarginElem.classList.remove('text-green-600', 'text-red-600');
        totalMarginElem.classList.add(totalMargin >= 0 ? 'text-green-600' : 'text-red-600');
    }
    
    if (marginPercentageElem) {
        marginPercentageElem.textContent = `${marginPercentage.toFixed(2)}%`;
        // Dynamic color based on percentage
        marginPercentageElem.classList.remove('text-green-500', 'text-red-500', 'text-yellow-500');
        if (marginPercentage < 0) {
            marginPercentageElem.classList.add('text-red-500');
        } else if (marginPercentage < 10) {
            marginPercentageElem.classList.add('text-yellow-500');
        } else {
            marginPercentageElem.classList.add('text-green-500');
        }
    }
    
    // Update hidden form inputs for submission
    const totalQtyInput = document.getElementById('total_qty_kirim');
    const totalHargaInput = document.getElementById('total_harga_kirim');
    const totalQtyDisplay = document.getElementById('total_qty_kirim_display');
    const totalHargaDisplay = document.getElementById('total_harga_kirim_display');
    
    if (totalQtyInput) totalQtyInput.value = totalQty.toFixed(2);
    if (totalHargaInput) totalHargaInput.value = totalHargaBeli.toFixed(2);
    if (totalQtyDisplay) totalQtyDisplay.value = `${formatNumber(totalQty)} kg`;
    if (totalHargaDisplay) totalHargaDisplay.value = `Rp ${formatNumber(totalHargaBeli)}`;
}

/**
 * Format number to Indonesian currency format
 * Example: 1234567.89 => 1.234.567,89
 */
function formatNumber(num) {
    if (isNaN(num) || num === null || num === undefined) return '0,00';
    
    const fixed = parseFloat(num).toFixed(2);
    const parts = fixed.split('.');
    
    // Format integer part with thousand separator
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    
    // Join with comma as decimal separator
    return parts.join(',');
}

// NOTE: All other functions (submitPengiriman, openBatalModal, etc.) 
// are defined in the parent file: pengiriman-masuk.blade.php
// This keeps the code DRY and easier to maintain

// Auto-initialize modal when loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.initializePengirimanModal === 'function') {
            window.initializePengirimanModal();
        }
        // Initial calculation and indicator update
        setTimeout(() => {
            updateTotals();
            document.querySelectorAll('.detail-item').forEach((row, index) => {
                updatePriceIndicator(index);
            });
        }, 100);
    });
} else {
    // DOM already loaded (untuk AJAX loaded content)
    if (typeof window.initializePengirimanModal === 'function') {
        window.initializePengirimanModal();
    }
    // Initial calculation and indicator update
    setTimeout(() => {
        updateTotals();
        document.querySelectorAll('.detail-item').forEach((row, index) => {
            updatePriceIndicator(index);
        });
    }, 100);
}
</script>
