<div class="relative">
    {{-- Flash Messages --}}
    @if(session()->has('message'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 mb-5 text-sm">
            <i class="fas fa-check-circle text-green-500"></i>
            <span>{{ session('message') }}</span>
        </div>
    @endif
    @if(session()->has('error'))
        <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-5 text-sm">
            <i class="fas fa-exclamation-circle text-red-400"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Breadcrumb --}}
    <div class="bg-white border-b border-gray-200 mb-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <nav>
                <ol class="flex flex-wrap items-center gap-x-2 text-sm text-gray-500">
                    <li>
                        <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-home"></i>
                        </a>
                    </li>
                    <li class="flex items-center gap-2"><i class="fas fa-chevron-right text-gray-300 text-xs"></i>Accounting</li>
                    <li class="flex items-center gap-2">
                        <i class="fas fa-chevron-right text-gray-300 text-xs"></i>
                        <a href="{{ route('accounting.approval-pembayaran') }}" class="hover:text-gray-700">Approval Pembayaran</a>
                    </li>
                    <li class="flex items-center gap-2"><i class="fas fa-chevron-right text-gray-300 text-xs"></i><span class="text-gray-900 font-medium">Approve</span></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('accounting.approval-pembayaran') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-arrow-left text-xs"></i>Kembali
                </a>
                <h1 class="text-xl font-bold text-gray-900">Approve Pembayaran</h1>
            </div>
            @if($approval)
                <span class="px-3 py-1.5 text-xs font-semibold rounded-full
                    @if($approval->status === 'pending') bg-yellow-100 text-yellow-700
                    @elseif($approval->status === 'completed') bg-green-100 text-green-700
                    @elseif($approval->status === 'rejected') bg-red-100 text-red-700
                    @else bg-gray-100 text-gray-700 @endif">
                    <i class="fas fa-circle text-xs mr-1"></i>
                    @if($approval->status === 'pending') Menunggu Approval
                    @elseif($approval->status === 'completed') Selesai
                    @elseif($approval->status === 'rejected') Ditolak
                    @else {{ ucfirst($approval->status) }} @endif
                </span>
            @endif
        </div>

        @if($approval && $pengiriman)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left Column --}}
                <div class="lg:col-span-2 space-y-5">

                    {{-- Informasi Pengiriman --}}
                    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                            <i class="fas fa-shipping-fast text-green-500 text-sm"></i>
                            <h2 class="text-sm font-bold text-gray-800">Informasi Pengiriman</h2>
                        </div>
                        <div class="p-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                                <div>
                                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Nomor Pengiriman</p>
                                    <p class="text-sm font-bold text-gray-900">{{ $pengiriman->no_pengiriman ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Tanggal Kirim</p>
                                    <p class="text-sm text-gray-800">
                                        {{ $pengiriman->tanggal_kirim ? \Carbon\Carbon::parse($pengiriman->tanggal_kirim)->format('d M Y') : '-' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Total Qty Kirim</p>
                                    <p class="text-sm font-bold text-gray-900">{{ number_format($pengiriman->total_qty_kirim, 2, ',', '.') }} kg</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Total Harga (Original)</p>
                                    <p class="text-sm font-bold text-gray-900">Rp {{ number_format($pengiriman->total_harga_kirim, 2, ',', '.') }}</p>
                                </div>
                            </div>

                            @if($pengiriman->pengirimanDetails && count($pengiriman->pengirimanDetails) > 0)
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Detail Pengiriman</p>
                                <div class="space-y-2">
                                    @foreach($pengiriman->pengirimanDetails as $detail)
                                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-100">
                                            <div class="grid grid-cols-2 gap-2 text-sm">
                                                <div>
                                                    <span class="text-gray-400 text-xs">Supplier:</span>
                                                    <span class="font-medium text-gray-800 ml-1">{{ $detail->bahanBakuSupplier->supplier->nama ?? '-' }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-400 text-xs">Bahan Baku:</span>
                                                    <span class="font-medium text-gray-800 ml-1">{{ $detail->bahanBakuSupplier->nama ?? '-' }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-400 text-xs">Qty:</span>
                                                    <span class="font-semibold text-gray-900 ml-1">{{ number_format($detail->qty_kirim, 2, ',', '.') }} kg</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-400 text-xs">Total:</span>
                                                    <span class="font-bold text-gray-900 ml-1">Rp {{ number_format($detail->total_harga, 2, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Refraksi Penagihan --}}
                    @if($invoicePenagihan)
                        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                            <div class="px-5 py-3.5 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                                <i class="fas fa-file-invoice text-purple-500 text-sm"></i>
                                <div>
                                    <h2 class="text-sm font-bold text-gray-800">Refraksi Penagihan <span class="text-gray-400 font-normal">(Customer)</span></h2>
                                </div>
                            </div>
                            <div class="p-5">
                                @if($invoicePenagihan->refraksi_type && $invoicePenagihan->refraksi_value > 0)
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Jenis</p>
                                            <p class="text-sm font-semibold text-gray-800">
                                                @if($invoicePenagihan->refraksi_type === 'qty')
                                                    Qty ({{ number_format($invoicePenagihan->refraksi_value, 2) }}%)
                                                @else
                                                    Rp {{ number_format($invoicePenagihan->refraksi_value, 2, ',', '.') }}/kg
                                                @endif
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Potongan</p>
                                            <p class="text-sm font-bold text-red-600">- Rp {{ number_format($invoicePenagihan->refraksi_amount, 2, ',', '.') }}</p>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-400 text-center py-3">Tidak ada refraksi penagihan diterapkan</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Catatan Pengiriman --}}
                    @if(!empty($pengiriman->catatan))
                        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                            <div class="px-5 py-3.5 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                                <i class="fas fa-sticky-note text-gray-400 text-sm"></i>
                                <h2 class="text-sm font-bold text-gray-800">Catatan Pengiriman</h2>
                            </div>
                            <div class="p-5">
                                <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $pengiriman->catatan }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Catatan Refraksi --}}
                    @if(!empty($pengiriman->catatan_refraksi))
                        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                            <div class="px-5 py-3.5 border-b border-yellow-100 bg-yellow-50 flex items-center gap-2">
                                <i class="fas fa-calculator text-yellow-500 text-sm"></i>
                                <h2 class="text-sm font-bold text-gray-800">Catatan Refraksi</h2>
                            </div>
                            <div class="p-5">
                                <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $pengiriman->catatan_refraksi }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Bukti Foto Bongkar --}}
                    @php
                        $photos = $pengiriman->bukti_foto_bongkar_array ?? [];
                    @endphp
                    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-camera text-green-500 text-sm"></i>
                                <h2 class="text-sm font-bold text-gray-800">Bukti Foto Bongkar</h2>
                            </div>
                            @if($pengiriman->bukti_foto_bongkar_uploaded_at)
                                <span class="text-xs text-gray-400">
                                    <i class="far fa-clock mr-1"></i>
                                    {{ $pengiriman->bukti_foto_bongkar_uploaded_at->format('d M Y, H:i') }} WIB
                                </span>
                            @endif
                        </div>
                        <div class="p-5">
                            @if(is_array($photos) && count($photos) > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach($photos as $index => $photo)
                                        @if($photo)
                                            @php $photoUrl = asset('storage/pengiriman/bukti/' . $photo); @endphp
                                            <div class="relative group">
                                                <img src="{{ $photoUrl }}"
                                                     alt="Bukti Foto Bongkar {{ $index + 1 }}"
                                                     class="w-full h-48 object-cover rounded-lg border border-gray-200 hover:border-green-400 transition cursor-pointer"
                                                     onclick="openPhotoModal('{{ $photoUrl }}')"
                                                     onerror="this.onerror=null;this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgdmlld0JveD0iMCAwIDQwMCAzMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSI0MDAiIGhlaWdodD0iMzAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxjaXJjbGUgY3g9IjIwMCIgY3k9IjEyMCIgcj0iMzAiIGZpbGw9IiM5Q0EzQUYiLz4KPHRleHQgeD0iMjAwIiB5PSIyNjAiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNiIgZmlsbD0iIzZCNzI4MCIgdGV4dC1hbmNob3I9Im1pZGRsZSI+R2FtYmFyIHRpZGFrIGRpdGVtdWthbjwvdGV4dD4KPC9zdmc+';this.classList.add('opacity-40');">
                                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 rounded-lg transition flex items-center justify-center opacity-0 group-hover:opacity-100">
                                                    <div class="flex gap-2">
                                                        <button onclick="event.stopPropagation();openPhotoModal('{{ $photoUrl }}')"
                                                                class="w-9 h-9 bg-white text-green-600 rounded-full shadow flex items-center justify-center hover:bg-green-50 transition">
                                                            <i class="fas fa-search-plus text-sm"></i>
                                                        </button>
                                                        <button onclick="event.stopPropagation();downloadImage('{{ $photoUrl }}','bukti_foto_bongkar_{{ $index + 1 }}.jpg')"
                                                                class="w-9 h-9 bg-white text-gray-600 rounded-full shadow flex items-center justify-center hover:bg-gray-50 transition">
                                                            <i class="fas fa-download text-sm"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-10 text-gray-400">
                                    <i class="fas fa-image text-4xl block mb-3"></i>
                                    <p class="text-sm">Foto belum diunggah untuk pengiriman ini</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Photo Modal --}}
                    <div id="photoModal" class="fixed inset-0 bg-black/75 z-50 hidden items-center justify-center p-4"
                         onclick="if(event.target===this)closePhotoModal()">
                        <div class="relative max-w-6xl max-h-full">
                            <button onclick="closePhotoModal()"
                                    class="absolute -top-10 right-0 w-9 h-9 flex items-center justify-center text-white bg-black/50 rounded-full hover:bg-black/70 transition">
                                <i class="fas fa-times"></i>
                            </button>
                            <img id="photoModalImage" src="" alt="Bukti Foto" class="max-w-full max-h-[90vh] rounded-lg shadow-2xl">
                        </div>
                    </div>

                    {{-- Potong Piutang Supplier --}}
                    @php
                        $supplier = $pengiriman->pengirimanDetails->first()?->bahanBakuSupplier?->supplier;
                        $totalPiutang = $supplier ? \App\Models\CatatanPiutang::where('supplier_id', $supplier->id)->where('status', '!=', 'lunas')->sum('sisa_piutang') : 0;
                    @endphp
                    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                            <i class="fas fa-file-invoice text-blue-500 text-sm"></i>
                            <div>
                                <h2 class="text-sm font-bold text-gray-800">Potong Piutang Supplier <span class="text-gray-400 font-normal">(Opsional)</span></h2>
                            </div>
                        </div>
                        <div class="p-5">
                            {{-- Piutang Summary --}}
                            <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 mb-4">
                                <div>
                                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide">Total Piutang Supplier</p>
                                    @if($supplier)
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $supplier->nama }}</p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <p class="text-base font-bold {{ $totalPiutang > 0 ? 'text-orange-600' : 'text-gray-500' }}">
                                        Rp {{ number_format($totalPiutang, 2, ',', '.') }}
                                    </p>
                                    <p class="text-xs {{ $totalPiutang > 0 ? 'text-orange-500' : 'text-gray-400' }} mt-0.5">
                                        <i class="fas {{ $totalPiutang > 0 ? 'fa-exclamation-circle' : 'fa-check-circle' }} mr-1"></i>
                                        {{ $totalPiutang > 0 ? 'Ada piutang' : 'Tidak ada piutang' }}
                                    </p>
                                </div>
                            </div>

                            @if($approval->catatan_piutang_id)
                                <div class="grid grid-cols-2 gap-4 p-4 bg-gray-50 border border-gray-200 rounded-lg mb-4">
                                    <div>
                                        <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Piutang Dipilih</p>
                                        <p class="text-sm font-semibold text-gray-800">#{{ $approval->catatanPiutang?->id ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Pemotongan</p>
                                        <p class="text-sm font-bold text-red-600">- Rp {{ number_format($approval->piutang_amount, 2, ',', '.') }}</p>
                                    </div>
                                    @if($approval->piutang_notes)
                                        <div class="col-span-2">
                                            <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Catatan</p>
                                            <p class="text-sm text-gray-700">{{ $approval->piutang_notes }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if($canManage && $approval->status === 'pending')
                                @php
                                    $piutangList = $supplier ? \App\Models\CatatanPiutang::where('supplier_id', $supplier->id)
                                        ->where('status', '!=', 'lunas')->where('sisa_piutang', '>', 0)
                                        ->orderBy('tanggal_piutang', 'asc')->with('supplier')->get() : collect();
                                @endphp
                                <div class="border border-gray-200 rounded-lg p-4 space-y-4">
                                    <p class="text-xs font-bold text-gray-700 flex items-center gap-2">
                                        <i class="fas fa-edit text-gray-400"></i>
                                        {{ $approval->catatan_piutang_id ? 'Edit' : 'Tambah' }} Pemotongan Piutang
                                    </p>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1.5">
                                            Pilih Piutang @if($supplier)<span class="text-gray-400">({{ $supplier->nama }})</span>@endif
                                        </label>
                                        <select wire:model.live="piutangForm.catatan_piutang_id"
                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-500 bg-white transition">
                                            <option value="">-- Tidak ada pemotongan piutang --</option>
                                            @foreach($piutangList as $idx => $piutang)
                                                <option value="{{ $piutang->id }}">
                                                    {{ $idx === 0 ? '⭐ ' : '' }}{{ \Carbon\Carbon::parse($piutang->tanggal_piutang)->format('d/m/Y') }} - Sisa: Rp {{ number_format($piutang->sisa_piutang, 2, ',', '.') }}{{ $idx === 0 ? ' (Terlama)' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($piutangList->isEmpty() && $supplier)
                                            <p class="text-xs text-gray-400 mt-1"><i class="fas fa-info-circle mr-1"></i>Supplier ini tidak memiliki piutang aktif</p>
                                        @elseif(!$piutangList->isEmpty())
                                            <p class="text-xs text-blue-500 mt-1"><i class="fas fa-info-circle mr-1"></i>Diurutkan dari yang terlama (FIFO)</p>
                                        @endif
                                    </div>

                                    @if($piutangForm['catatan_piutang_id'])
                                        @php $selectedPiutang = $piutangList->firstWhere('id', $piutangForm['catatan_piutang_id']); @endphp
                                        <div class="grid grid-cols-2 gap-3 text-xs bg-blue-50 border border-blue-200 rounded-lg p-3">
                                            <div>
                                                <span class="text-gray-500">Total Piutang:</span>
                                                <span class="font-semibold ml-1">Rp {{ number_format($selectedPiutang->jumlah_piutang ?? 0, 2, ',', '.') }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Sisa:</span>
                                                <span class="font-semibold text-orange-600 ml-1">Rp {{ number_format($selectedPiutang->sisa_piutang ?? 0, 2, ',', '.') }}</span>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Jumlah Pemotongan <span class="text-red-500">*</span></label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">Rp</span>
                                                <input type="text" id="piutang_amount_display"
                                                       value="{{ number_format($piutangForm['amount'] ?? 0, 2, ',', '.') }}"
                                                       class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-500 transition"
                                                       placeholder="0"
                                                       oninput="formatCurrencyPiutang(this,'piutang_amount_hidden')"
                                                       onblur="autoSavePiutang()">
                                            </div>
                                            <input type="hidden" wire:model.defer="piutangForm.amount" id="piutang_amount_hidden">
                                            <p class="text-xs text-gray-400 mt-1">Maksimal: Rp {{ number_format($selectedPiutang->sisa_piutang ?? 0, 2, ',', '.') }}</p>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Catatan <span class="text-gray-400">(Opsional)</span></label>
                                            <textarea wire:model="piutangForm.notes" rows="2"
                                                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-500 transition"
                                                      placeholder="Tambahkan catatan..."></textarea>
                                        </div>
                                    @endif

                                    <p class="text-xs text-green-600 flex items-center gap-1.5">
                                        <i class="fas fa-check-circle"></i>Perubahan tersimpan otomatis
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Refraksi Pembayaran --}}
                    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                            <i class="fas fa-hand-holding-usd text-green-500 text-sm"></i>
                            <div>
                                <h2 class="text-sm font-bold text-gray-800">Refraksi Pembayaran <span class="text-gray-400 font-normal">(Supplier)</span></h2>
                            </div>
                        </div>
                        <div class="p-5">
                            @if($approval->refraksi_type && $approval->refraksi_value > 0)
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Jenis</p>
                                        <p class="text-sm font-semibold text-gray-800">
                                            @if($approval->refraksi_type === 'qty') Qty ({{ number_format($approval->refraksi_value, 2) }}%)
                                            @elseif($approval->refraksi_type === 'rupiah') Rp {{ number_format($approval->refraksi_value, 2, ',', '.') }}/kg
                                            @elseif($approval->refraksi_type === 'lainnya') Lainnya (Rp {{ number_format($approval->refraksi_value, 2, ',', '.') }})
                                            @else {{ ucfirst($approval->refraksi_type) }} @endif
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Potongan</p>
                                        <p class="text-sm font-bold text-red-600">- Rp {{ number_format($approval->refraksi_amount, 2, ',', '.') }}</p>
                                    </div>
                                    <div class="col-span-2">
                                        <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Total Pembayaran</p>
                                        <p class="text-base font-bold text-green-600">Rp {{ number_format($approval->amount_after_refraksi, 2, ',', '.') }}</p>
                                    </div>
                                </div>
                            @else
                                <p class="text-sm text-gray-400 text-center py-3">Tidak ada refraksi pembayaran diterapkan</p>
                            @endif

                            @if($canManage && $approval->status === 'pending')
                                <div class="border border-gray-200 rounded-lg p-4 space-y-4 mt-2">
                                    <p class="text-xs font-bold text-gray-700 flex items-center gap-2">
                                        <i class="fas fa-edit text-gray-400"></i>Edit Refraksi Pembayaran
                                        <span class="font-normal text-gray-400">(Isi 0 untuk tanpa refraksi)</span>
                                    </p>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Jenis Refraksi</label>
                                            <select wire:model="refraksiForm.type"
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-500 bg-white transition">
                                                <option value="qty">Qty (%)</option>
                                                <option value="rupiah">Rupiah (Rp/kg)</option>
                                                <option value="lainnya">Refraksi Lainnya (Manual)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1.5">
                                                Nilai Refraksi
                                                <span class="text-gray-400">
                                                    @if($refraksiForm['type'] === 'qty')(dalam %)
                                                    @elseif($refraksiForm['type'] === 'rupiah')(Rp per kg)
                                                    @else(total potongan Rp)@endif
                                                </span>
                                            </label>
                                            <div class="relative">
                                                @if($refraksiForm['type'] === 'qty')
                                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">%</span>
                                                @else
                                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">Rp</span>
                                                @endif
                                                <input type="text" id="refraksi_value_display"
                                                       value="{{ number_format($refraksiForm['value'] ?? 0, 2, ',', '.') }}"
                                                       class="w-full {{ $refraksiForm['type'] === 'qty' ? 'pr-8 pl-3' : 'pl-9 pr-3' }} py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-500 transition"
                                                       placeholder="0"
                                                       oninput="formatCurrencyRefraksi(this,'refraksi_value_hidden','{{ $refraksiForm['type'] }}')"
                                                       onblur="autoSaveRefraksi()">
                                            </div>
                                            <input type="hidden" wire:model.defer="refraksiForm.value" id="refraksi_value_hidden">
                                        </div>
                                    </div>
                                    <p class="text-xs text-green-600 flex items-center gap-1.5">
                                        <i class="fas fa-check-circle"></i>Perubahan tersimpan otomatis
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Pengeluaran Tambahan (Truk/Kuli/Fee/Lainnya) --}}
                    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden" wire:key="pengeluaran-tambahan-{{ $approval->id }}">
                        <div class="px-5 py-3.5 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                            <i class="fas fa-receipt text-orange-500 text-sm"></i>
                            <div>
                                <h2 class="text-sm font-bold text-gray-800">Pengeluaran Tambahan <span class="text-gray-400 font-normal">(Truk, Kuli, Fee, dll)</span></h2>
                            </div>
                        </div>

                        <div class="p-5 space-y-4">
                            @php
                                $expensesTotal = floatval($approval->additional_expenses_total ?? 0);
                            @endphp

                            {{-- Input form (persist via updateExpenses) --}}
                            @if($canManage && $approval->status === 'pending')
                                <div class="border border-gray-200 rounded-lg p-4 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Truk</label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">Rp</span>
                                                <input type="number" step="0.01" min="0" wire:model.defer="expenseForm.truk"
                                                       class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-200 focus:border-orange-400 transition"
                                                       placeholder="0"  onwheel="this.blur()">
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Kuli</label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">Rp</span>
                                                <input type="number" step="0.01" min="0" wire:model.defer="expenseForm.kuli"
                                                       class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-200 focus:border-orange-400 transition"
                                                       placeholder="0"  onwheel="this.blur()">
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Fee</label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">Rp</span>
                                                <input type="number" step="0.01" min="0" wire:model.defer="expenseForm.fee"
                                                       class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-200 focus:border-orange-400 transition"
                                                       placeholder="0"  onwheel="this.blur()">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <p class="text-xs font-semibold text-gray-700">Lainnya</p>
                                            <button type="button" wire:click="addOtherExpenseRow"
                                                    class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-lg bg-orange-50 text-orange-700 hover:bg-orange-100 border border-orange-200">
                                                <i class="fas fa-plus"></i>Tambah Baris
                                            </button>
                                        </div>

                                        <div class="space-y-2">
                                            @forelse(($expenseForm['others'] ?? []) as $i => $row)
                                                <div class="grid grid-cols-1 md:grid-cols-12 gap-2 items-center">
                                                    <div class="md:col-span-6">
                                                        <input type="text" wire:model.defer="expenseForm.others.{{ $i }}.type"
                                                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-200 focus:border-orange-400 transition"
                                                               placeholder="Nama pengeluaran (contoh: Parkir / Tol / Solar)">
                                                    </div>
                                                    <div class="md:col-span-5">
                                                        <div class="relative">
                                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">Rp</span>
                                                            <input type="number" step="0.01" min="0" wire:model.defer="expenseForm.others.{{ $i }}.amount"
                                                                   class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-200 focus:border-orange-400 transition"
                                                                   placeholder="0"  onwheel="this.blur()">
                                                        </div>
                                                    </div>
                                                    <div class="md:col-span-1 flex md:justify-end">
                                                        <button type="button" wire:click="removeOtherExpenseRow({{ $i }})"
                                                                class="inline-flex items-center justify-center w-10 h-10 text-red-600 bg-red-50 hover:bg-red-100 rounded-lg border border-red-200">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @empty
                                                <p class="text-xs text-gray-400">Belum ada baris lainnya.</p>
                                            @endforelse
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                                        <p class="text-xs text-gray-500">Klik simpan untuk menerapkan perubahan.</p>
                                        <button type="button" wire:click="updateExpenses"
                                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg bg-orange-600 text-white hover:bg-orange-700 transition">
                                            <i class="fas fa-save"></i>Simpan
                                        </button>
                                    </div>

                                    <p class="text-xs text-green-600 flex items-center gap-1.5">
                                        <i class="fas fa-info-circle"></i>Setelah disimpan, subtotal & total dibayarkan akan ikut terhitung.
                                    </p>
                                </div>
                            @else
                                {{-- Read-only list (from DB relation) --}}
                                @php $expensesList = $approval->expenses ?? collect(); @endphp
                                <div class="space-y-2">
                                    @forelse($expensesList as $exp)
                                        <div class="flex items-center justify-between gap-3 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                                            <div class="min-w-0">
                                                <p class="text-xs font-semibold text-gray-700">{{ strtoupper($exp->type) }}</p>
                                                <p class="text-sm font-bold text-gray-900">Rp {{ number_format($exp->amount ?? 0, 2, ',', '.') }}</p>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-sm text-gray-400 text-center py-3">Belum ada pengeluaran tambahan</div>
                                    @endforelse
                                </div>
                            @endif

                            <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                                <span class="text-sm font-semibold text-gray-800">Total Pengeluaran Tambahan</span>
                                <span class="text-sm font-bold text-orange-600">Rp {{ number_format($expensesTotal, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Right Column: Sticky Summary & Actions --}}
                <div class="lg:col-span-1">
                    <div class="sticky top-6 space-y-5">

                        {{-- Ringkasan Pembayaran --}}
                        @php
                            $totalAwal = $pengiriman->total_harga_kirim ?? 0;
                            $piutangPotongan = $approval->piutang_amount ?? 0;
                            $refraksiPotongan = $approval->refraksi_amount ?? 0;
                            $pengeluaranTambahan = $approval->additional_expenses_total ?? 0;
                            $subtotal = $approval->subtotal ?? max(0, $totalAwal - $refraksiPotongan - $pengeluaranTambahan);
                            $totalPembayaran = $approval->total_dibayarkan ?? max(0, $subtotal - $piutangPotongan);
                        @endphp
                        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden" wire:key="ringkasan-{{ $approval->id }}">
                            <div class="px-5 py-3.5 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                                <i class="fas fa-wallet text-green-500 text-sm"></i>
                                <h2 class="text-sm font-bold text-gray-800">Ringkasan Pembayaran</h2>
                            </div>
                            <div class="p-5 space-y-3">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-500">Total Awal</span>
                                    <span class="font-semibold text-gray-800">Rp {{ number_format($totalAwal, 2, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-500">Potongan Refraksi</span>
                                    <span class="font-semibold text-red-500">- Rp {{ number_format($refraksiPotongan, 2, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-500">Pengeluaran Tambahan</span>
                                    <span class="font-semibold text-red-500">- Rp {{ number_format($pengeluaranTambahan, 2, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-500">Subtotal</span>
                                    <span class="font-semibold text-indigo-700">Rp {{ number_format($subtotal, 2, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-500">Potongan Piutang</span>
                                    <span class="font-semibold text-red-500">- Rp {{ number_format($piutangPotongan, 2, ',', '.') }}</span>
                                </div>
                                <div class="pt-3 border-t border-gray-100 flex justify-between items-center">
                                    <span class="text-sm font-bold text-gray-900">Total Dibayarkan</span>
                                    <span class="text-xl font-extrabold text-green-600">Rp {{ number_format($totalPembayaran, 2, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Approval Card --}}
                        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                            <div class="px-5 py-4 bg-green-600 flex items-center gap-2">
                                <i class="fas fa-check-circle text-white text-sm"></i>
                                <h2 class="text-sm font-bold text-white">Approval Action</h2>
                            </div>
                            <div class="p-5 space-y-4">
                                {{-- Status Info --}}
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Status</p>
                                        <p class="text-sm font-semibold text-gray-800">
                                            @if($approval->status === 'pending') <i class="fas fa-clock text-yellow-500 mr-1"></i>Menunggu Approval
                                            @elseif($approval->status === 'completed') <i class="fas fa-check-circle text-green-500 mr-1"></i>Selesai
                                            @elseif($approval->status === 'rejected') <i class="fas fa-times-circle text-red-500 mr-1"></i>Ditolak
                                            @else - @endif
                                        </p>
                                    </div>
                                    @if($approval->staff)
                                        <div>
                                            <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Disetujui Oleh</p>
                                            <p class="text-sm text-gray-700">{{ $approval->staff->nama }} <span class="text-gray-400">(Staff Accounting)</span></p>
                                        </div>
                                    @endif
                                    @if($approval->manager)
                                        <div>
                                            <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Disetujui Oleh</p>
                                            <p class="text-sm text-gray-700">{{ $approval->manager->nama }} <span class="text-gray-400">(Manager Accounting)</span></p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Notes --}}
                                @if($canManage && $approval->status === 'pending')
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Catatan <span class="text-gray-400">(Opsional)</span></label>
                                        <textarea wire:model="notes" rows="3"
                                                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-500 transition"
                                                  placeholder="Tambahkan catatan untuk approval..."></textarea>
                                    </div>
                                @elseif(!$canManage)
                                    <p class="text-xs text-gray-400 bg-gray-50 border border-gray-200 rounded-lg p-3">
                                        Hanya tim accounting yang dapat melakukan approval.
                                    </p>
                                @endif

                                {{-- Upload Bukti Pembayaran --}}
                                @if($canManage && $approval->status === 'pending')
                                    <div class="border border-gray-200 rounded-lg p-4 space-y-3">
                                        <p class="text-xs font-bold text-gray-700 flex items-center gap-2">
                                            <i class="fas fa-file-upload text-green-500"></i>
                                            Bukti Pembayaran <span class="text-red-500">*</span>
                                        </p>
                                        <p class="text-xs text-gray-400">Format JPG, PNG, atau PDF. Total maks. 20 MB.</p>

                                        <input type="file" wire:model="buktiPembayaran" accept=".jpg,.jpeg,.png,.pdf" multiple
                                               class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-green-600 file:text-white hover:file:bg-green-700 file:cursor-pointer">

                                        <div wire:loading wire:target="buktiPembayaran" class="flex items-center gap-2 text-green-600 text-xs">
                                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Mengunggah...
                                        </div>

                                        @error('buktiPembayaran.*')
                                            <p class="text-xs text-red-500">{{ $message }}</p>
                                        @enderror

                                        @if($buktiPembayaran && count($buktiPembayaran) > 0)
                                            @php $totalSize = collect($buktiPembayaran)->sum(fn($f) => $f->getSize()); @endphp
                                            <div class="text-xs text-gray-600 bg-gray-50 rounded-lg p-3 space-y-2">
                                                <p class="font-medium">{{ count($buktiPembayaran) }} file dipilih — {{ number_format($totalSize / 1024 / 1024, 2) }} MB</p>
                                                @if($totalSize > 20 * 1024 * 1024)
                                                    <p class="text-red-500 font-semibold"><i class="fas fa-exclamation-triangle mr-1"></i>Total melebihi 20 MB!</p>
                                                @endif
                                                @foreach($buktiPembayaran as $file)
                                                    <div class="text-gray-500">
                                                        <i class="fas fa-file mr-1"></i>{{ $file->getClientOriginalName() }}
                                                        ({{ number_format($file->getSize() / 1024, 1) }} KB)
                                                        @if(in_array($file->getClientOriginalExtension(), ['jpg','jpeg','png']))
                                                            <img src="{{ $file->temporaryUrl() }}" class="mt-1.5 w-full h-auto rounded border border-gray-200 max-h-40 object-contain">
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @elseif($approval->bukti_pembayaran)
                                    @php
                                        try { $buktiFiles = json_decode($approval->bukti_pembayaran, true) ?: [$approval->bukti_pembayaran]; }
                                        catch (\Exception $e) { $buktiFiles = [$approval->bukti_pembayaran]; }
                                    @endphp
                                    <div class="space-y-2">
                                        @foreach($buktiFiles as $i => $filePath)
                                            <a href="{{ Storage::url($filePath) }}" target="_blank"
                                               class="flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg transition">
                                                <i class="fas fa-download"></i>
                                                Bukti Pembayaran{{ count($buktiFiles) > 1 ? ' #'.($i+1) : '' }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Action Button --}}
                                @if($canManage && $approval->status === 'pending')
                                    <button wire:click="approve"
                                            wire:confirm="Apakah Anda yakin ingin menyetujui approval ini?"
                                            wire:loading.attr="disabled"
                                            wire:target="buktiPembayaran"
                                            class="w-full py-3 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <span wire:loading.remove wire:target="buktiPembayaran">
                                            <i class="fas fa-check-circle mr-1"></i>Approve
                                        </span>
                                        <span wire:loading wire:target="buktiPembayaran" class="flex items-center gap-2">
                                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Mengunggah...
                                        </span>
                                    </button>
                                @elseif(!$canManage && $approval->status === 'pending')
                                    <p class="text-xs text-gray-400 bg-gray-50 border border-gray-200 rounded-lg p-3 text-center">
                                        Hanya tim accounting yang dapat melakukan approval.
                                    </p>
                                @elseif($approval->status === 'completed')
                                    <div class="text-center py-4 text-gray-400">
                                        <i class="fas fa-check-circle text-green-500 text-3xl block mb-2"></i>
                                        <p class="text-sm">Approval sudah selesai</p>
                                    </div>
                                @elseif($approval->status === 'rejected')
                                    <div class="text-center py-4 text-gray-400">
                                        <i class="fas fa-times-circle text-red-400 text-3xl block mb-2"></i>
                                        <p class="text-sm">Approval ditolak</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-16 text-gray-400">
                <i class="fas fa-exclamation-triangle text-red-300 text-4xl block mb-3"></i>
                <p class="text-sm font-medium text-gray-500">Data approval tidak ditemukan</p>
            </div>
        @endif
    </div>
</div>

<script>
if (typeof window.downloadImage !== 'function') {
    window.downloadImage = function(src, name = 'bukti_foto_bongkar.jpg') {
        const a = document.createElement('a');
        a.href = src; a.download = name;
        document.body.appendChild(a); a.click(); document.body.removeChild(a);
    };
}

function openPhotoModal(src) {
    const m = document.getElementById('photoModal');
    document.getElementById('photoModalImage').src = src;
    m.classList.remove('hidden'); m.classList.add('flex');
    document.body.style.overflow = 'hidden';
}
function closePhotoModal() {
    const m = document.getElementById('photoModal');
    m.classList.add('hidden'); m.classList.remove('flex');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closePhotoModal(); });

function formatCurrencyPiutang(el, hiddenId) {
    const v = el.value.replace(/[^0-9]/g, '');
    const h = document.getElementById(hiddenId);
    if (h) { h.value = v; h.dispatchEvent(new Event('input', { bubbles: true })); }
    el.value = v ? parseInt(v).toLocaleString('id-ID') : '';
}

function formatCurrencyRefraksi(el, hiddenId, type) {
    const h = document.getElementById(hiddenId);
    if (type === 'qty') {
        let v = el.value.replace(/[^0-9.,]/g, '').replace(',', '.');
        const parts = v.split('.'); if (parts.length > 2) v = parts[0] + '.' + parts.slice(1).join('');
        if (h) { h.value = v; h.dispatchEvent(new Event('input', { bubbles: true })); }
        el.value = v;
    } else {
        const v = el.value.replace(/[^0-9]/g, '');
        if (h) { h.value = v; h.dispatchEvent(new Event('input', { bubbles: true })); }
        el.value = v ? parseInt(v).toLocaleString('id-ID') : '';
    }
}

let piutangTimer, refraksiTimer;
function autoSavePiutang() {
    clearTimeout(piutangTimer);
    piutangTimer = setTimeout(() => @this.call('updatePiutang'), 500);
}
function autoSaveRefraksi() {
    clearTimeout(refraksiTimer);
    refraksiTimer = setTimeout(() => @this.call('updateRefraksi'), 500);
}

document.addEventListener('livewire:initialized', () => {
    Livewire.hook('morph.updated', () => {
        const pd = document.getElementById('piutang_amount_display');
        const ph = document.getElementById('piutang_amount_hidden');
        if (pd && ph?.value) {
            const v = ph.value.replace(/[^0-9]/g, '');
            if (v) pd.value = parseInt(v).toLocaleString('id-ID');
        }
        const rd = document.getElementById('refraksi_value_display');
        const rh = document.getElementById('refraksi_value_hidden');
        if (rd && rh?.value) {
            const type = @this.get('refraksiForm.type');
            rd.value = type === 'qty' ? rh.value : (parseInt(rh.value.replace(/[^0-9]/g, '')) || '').toLocaleString?.('id-ID') ?? rh.value;
        }
    });
});
</script>