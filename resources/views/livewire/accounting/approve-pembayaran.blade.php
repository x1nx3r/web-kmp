<div class="relative">
    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                <p class="text-green-700 font-medium">{{ session('message') }}</p>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                <p class="text-red-700 font-medium">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    {{-- Navigation Breadcrumb --}}
    <div class="bg-white border-b border-gray-200 mb-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between">
                <nav aria-label="Breadcrumb" class="w-full">
                    <ol class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-gray-500">
                        <li>
                            <a href="{{ route('dashboard') }}" class="flex items-center text-gray-400 hover:text-gray-500">
                                <i class="fas fa-home"></i>
                                <span class="sr-only">Home</span>
                            </a>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-300 mx-2"></i>
                            <span>Accounting</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-300 mx-2"></i>
                            <a href="{{ route('accounting.approval-pembayaran') }}" class="text-gray-500 hover:text-gray-700">Approval Pembayaran</a>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-300 mx-2"></i>
                            <span class="text-gray-900 font-medium">Approve</span>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Header with Back Button --}}
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('accounting.approval-pembayaran') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Approve Pembayaran</h1>
            </div>

            {{-- Status Badge --}}
            @if($approval)
                <span class="px-4 py-2 text-sm font-semibold rounded-full
                    @if($approval->status === 'pending') bg-yellow-100 text-yellow-800
                    @elseif($approval->status === 'completed') bg-green-100 text-green-800
                    @elseif($approval->status === 'rejected') bg-red-100 text-red-800
                    @else bg-gray-100 text-gray-800
                    @endif">
                    <i class="fas fa-circle text-xs mr-1"></i>
                    @if($approval->status === 'pending')
                        Menunggu Approval
                    @elseif($approval->status === 'completed')
                        Selesai
                    @elseif($approval->status === 'rejected')
                        Ditolak
                    @else
                        {{ ucfirst($approval->status) }}
                    @endif
                </span>
            @endif
        </div>

        @if($approval && $pengiriman)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left Column: Informasi & Refraksi --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Informasi Pengiriman --}}
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-shipping-fast text-blue-600 mr-3"></i>
                                Informasi Pengiriman
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Nomor Pengiriman</label>
                                    <p class="mt-1 text-base text-gray-900 font-semibold">{{ $pengiriman->no_pengiriman ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Tanggal Kirim</label>
                                    <p class="mt-1 text-base text-gray-900">
                                        <i class="fas fa-calendar text-gray-400 mr-2"></i>
                                        {{ $pengiriman->tanggal_kirim ? \Carbon\Carbon::parse($pengiriman->tanggal_kirim)->format('d M Y') : '-' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Total Qty Kirim</label>
                                    <p class="mt-1 text-base text-gray-900 font-semibold">{{ number_format($pengiriman->total_qty_kirim, 2, ',', '.') }} kg</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Total Harga (Original)</label>
                                    <p class="mt-1 text-lg text-gray-900 font-bold">Rp {{ number_format($pengiriman->total_harga_kirim, 0, ',', '.') }}</p>
                                </div>
                            </div>

                            {{-- Pengiriman Details --}}
                            @if($pengiriman->pengirimanDetails && count($pengiriman->pengirimanDetails) > 0)
                                <div class="mt-6">
                                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Detail Pengiriman</h3>
                                    <div class="space-y-3">
                                        @foreach($pengiriman->pengirimanDetails as $detail)
                                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                                <div class="grid grid-cols-2 gap-3 text-sm">
                                                    <div>
                                                        <span class="text-gray-500">Supplier:</span>
                                                        <span class="font-medium text-gray-900">{{ $detail->bahanBakuSupplier->supplier->nama ?? '-' }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-500">Bahan Baku:</span>
                                                        <span class="font-medium text-gray-900">{{ $detail->bahanBakuSupplier->nama ?? '-' }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-500">Qty:</span>
                                                        <span class="font-semibold text-gray-900">{{ number_format($detail->qty_kirim, 2, ',', '.') }} kg</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-500">Total:</span>
                                                        <span class="font-bold text-gray-900">Rp {{ number_format($detail->total_harga, 0, ',', '.') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Refraksi Penagihan (dari Invoice) --}}
                    @if($invoicePenagihan)
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg shadow-sm border border-purple-200">
                            <div class="border-b border-purple-200 bg-purple-100 px-6 py-4">
                                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-file-invoice text-purple-600 mr-3"></i>
                                    Refraksi Penagihan (Customer)
                                </h2>
                                <p class="text-sm text-purple-700 mt-1">Refraksi yang dikenakan kepada customer (opsional)</p>
                            </div>
                            <div class="p-6">
                                @if($invoicePenagihan->refraksi_type && $invoicePenagihan->refraksi_value > 0)
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-xs font-medium text-purple-700">Jenis</label>
                                            <p class="mt-1 text-sm font-semibold">
                                                @if($invoicePenagihan->refraksi_type === 'qty')
                                                    <i class="fas fa-percentage text-purple-600 mr-1"></i>Qty ({{ number_format($invoicePenagihan->refraksi_value, 2) }}%)
                                                @else
                                                    <i class="fas fa-money-bill text-purple-600 mr-1"></i>Rp {{ number_format($invoicePenagihan->refraksi_value, 0, ',', '.') }}/kg
                                                @endif
                                            </p>
                                        </div>
                                        <div>
                                            <label class="text-xs font-medium text-purple-700">Potongan</label>
                                            <p class="mt-1 text-sm text-red-600 font-bold">- Rp {{ number_format($invoicePenagihan->refraksi_amount, 0, ',', '.') }}</p>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500 text-center py-3">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Tidak ada refraksi penagihan diterapkan
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Catatan Pengiriman (Card Putih) --}}
                    @if(! empty($pengiriman->catatan))
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-sticky-note text-gray-600 mr-3"></i>
                                    Catatan Pengiriman
                                </h2>
                            </div>
                            <div class="p-6">
                                <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $pengiriman->catatan }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Catatan Refraksi (Card Kuning) --}}
                    @if(! empty($pengiriman->catatan_refraksi))
                        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg shadow-sm border border-yellow-200">
                            <div class="border-b border-yellow-200 bg-yellow-100 px-6 py-4">
                                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-calculator text-yellow-600 mr-3"></i>
                                    Catatan Refraksi
                                </h2>
                            </div>
                            <div class="p-6">
                                <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $pengiriman->catatan_refraksi }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Bukti Foto Bongkar --}}
                    @if($pengiriman->bukti_foto_bongkar_raw)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                        <i class="fas fa-camera text-blue-600 mr-3"></i>
                                        Bukti Foto Bongkar
                                    </h2>
                                    @if($pengiriman->bukti_foto_bongkar_uploaded_at)
                                        <p class="text-xs text-gray-500">
                                            <i class="fas fa-clock mr-1"></i>
                                            Upload: {{ $pengiriman->bukti_foto_bongkar_uploaded_at->format('d M Y, H:i') }} WIB
                                            <span class="text-gray-400">({{ $pengiriman->bukti_foto_bongkar_uploaded_at->diffForHumans() }})</span>
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="p-6">
                                @php
                                    $photos = $pengiriman->bukti_foto_bongkar_array ?? [];
                                @endphp

                                @if(is_array($photos) && count($photos) > 0)
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @foreach($photos as $index => $photo)
                                            @if($photo)
                                                @php
                                                    $photoUrl = asset('storage/pengiriman/bukti/' . $photo);
                                                @endphp
                                                <div class="relative group">
                                                    <img
                                                        src="{{ $photoUrl }}"
                                                        alt="Bukti Foto Bongkar {{ $index + 1 }}"
                                                        class="w-full h-48 object-cover rounded-lg shadow-md border-2 border-gray-200 hover:border-blue-400 transition-all cursor-pointer"
                                                        onclick="openPhotoModal('{{ $photoUrl }}')"
                                                        onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgdmlld0JveD0iMCAwIDQwMCAzMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSI0MDAiIGhlaWdodD0iMzAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxjaXJjbGUgY3g9IjIwMCIgY3k9IjEyMCIgcj0iMzAiIGZpbGw9IiM5Q0EzQUYiLz4KPHRleHQgeD0iMjAwIiB5PSIyNjAiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNiIgZmlsbD0iIzZCNzI4MCIgdGV4dC1hbmNob3I9Im1pZGRsZSI+R2FtYmFyIHRpZGFrIGRpdGVtdWthbjwvdGV4dD4KPC9zdmc+'; this.classList.add('opacity-50');">
                                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 rounded-lg transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                                                        <div class="flex space-x-2">
                                                            <button
                                                                onclick="event.stopPropagation(); openPhotoModal('{{ $photoUrl }}')"
                                                                class="bg-white text-blue-600 p-2 rounded-full shadow-lg hover:bg-blue-50 transition-colors"
                                                                title="Lihat"
                                                            >
                                                                <i class="fas fa-search-plus"></i>
                                                            </button>
                                                            <button
                                                                onclick="event.stopPropagation(); downloadImage('{{ $photoUrl }}', 'bukti_foto_bongkar_{{ $index + 1 }}.jpg')"
                                                                class="bg-white text-green-600 p-2 rounded-full shadow-lg hover:bg-green-50 transition-colors"
                                                                title="Download"
                                                            >
                                                                <i class="fas fa-download"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <div class="flex flex-col items-center justify-center py-12">
                                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                            <i class="fas fa-image text-gray-400 text-3xl"></i>
                                        </div>
                                        <p class="text-gray-500 text-sm">Tidak ada bukti foto bongkar</p>
                                        <p class="text-gray-400 text-xs mt-1">Foto belum diunggah untuk pengiriman ini</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Photo Modal --}}
                        <div id="photoModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closePhotoModal()">
                            <div class="relative max-w-6xl max-h-full">
                                <button onclick="closePhotoModal(); event.stopPropagation();" class="absolute -top-12 right-0 text-white hover:text-gray-300 bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center z-10">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                                <img id="photoModalImage" src="" alt="Bukti Foto Bongkar" class="max-w-full max-h-[90vh] rounded-lg shadow-2xl bg-white">
                            </div>
                        </div>

                        <script>
                            function openPhotoModal(imageSrc) {
                                const modal = document.getElementById('photoModal');
                                const modalImage = document.getElementById('photoModalImage');
                                modalImage.src = imageSrc;
                                modal.classList.remove('hidden');
                                modal.classList.add('flex');
                                document.body.style.overflow = 'hidden';
                            }

                            function closePhotoModal() {
                                const modal = document.getElementById('photoModal');
                                modal.classList.add('hidden');
                                modal.classList.remove('flex');
                                document.body.style.overflow = '';
                            }

                            // Close modal with Escape key
                            document.addEventListener('keydown', function(e) {
                                if (e.key === 'Escape') {
                                    closePhotoModal();
                                }
                            });
                        </script>
                    @else
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                        <i class="fas fa-camera text-blue-600 mr-3"></i>
                                        Bukti Foto Bongkar
                                    </h2>
                                    @if($pengiriman->bukti_foto_bongkar_uploaded_at)
                                        <p class="text-xs text-gray-500">
                                            <i class="fas fa-clock mr-1"></i>
                                            Upload: {{ $pengiriman->bukti_foto_bongkar_uploaded_at->format('d M Y, H:i') }} WIB
                                            <span class="text-gray-400">({{ $pengiriman->bukti_foto_bongkar_uploaded_at->diffForHumans() }})</span>
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="p-6">
                                @php
                                    $photos = $pengiriman->bukti_foto_bongkar_array ?? [];
                                @endphp

                                @if(is_array($photos) && count($photos) > 0)
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @foreach($photos as $index => $photo)
                                            @if($photo)
                                                @php
                                                    $photoUrl = asset('storage/pengiriman/bukti/' . $photo);
                                                @endphp
                                                <div class="relative group">
                                                    <img
                                                        src="{{ $photoUrl }}"
                                                        alt="Bukti Foto Bongkar {{ $index + 1 }}"
                                                        class="w-full h-48 object-cover rounded-lg shadow-md border-2 border-gray-200 hover:border-blue-400 transition-all cursor-pointer"
                                                        onclick="openPhotoModal('{{ $photoUrl }}')"
                                                        onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgdmlld0JveD0iMCAwIDQwMCAzMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSI0MDAiIGhlaWdodD0iMzAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxjaXJjbGUgY3g9IjIwMCIgY3k9IjEyMCIgcj0iMzAiIGZpbGw9IiM5Q0EzQUYiLz4KPHRleHQgeD0iMjAwIiB5PSIyNjAiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNiIgZmlsbD0iIzZCNzI4MCIgdGV4dC1hbmNob3I9Im1pZGRsZSI+R2FtYmFyIHRpZGFrIGRpdGVtdWthbjwvdGV4dD4KPC9zdmc+'; this.classList.add('opacity-50');">
                                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 rounded-lg transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                                                        <div class="flex space-x-2">
                                                            <button
                                                                onclick="event.stopPropagation(); openPhotoModal('{{ $photoUrl }}')"
                                                                class="bg-white text-blue-600 p-2 rounded-full shadow-lg hover:bg-blue-50 transition-colors"
                                                                title="Lihat"
                                                            >
                                                                <i class="fas fa-search-plus"></i>
                                                            </button>
                                                            <button
                                                                onclick="event.stopPropagation(); downloadImage('{{ $photoUrl }}', 'bukti_foto_bongkar_{{ $index + 1 }}.jpg')"
                                                                class="bg-white text-green-600 p-2 rounded-full shadow-lg hover:bg-green-50 transition-colors"
                                                                title="Download"
                                                            >
                                                                <i class="fas fa-download"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <div class="flex flex-col items-center justify-center py-12">
                                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                            <i class="fas fa-image text-gray-400 text-3xl"></i>
                                        </div>
                                        <p class="text-gray-500 text-sm">Tidak ada bukti foto bongkar</p>
                                        <p class="text-gray-400 text-xs mt-1">Foto belum diunggah untuk pengiriman ini</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Piutang Supplier --}}
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow-sm border border-blue-200">
                        <div class="border-b border-blue-200 bg-blue-100 px-6 py-4">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-file-invoice text-blue-600 mr-3"></i>
                                Potong Piutang Supplier (Opsional)
                            </h2>
                            <p class="text-sm text-blue-700 mt-1">Kurangi pembayaran dengan piutang yang dimiliki supplier</p>
                        </div>
                        <div class="p-6">
                            @php
                                $supplier = $pengiriman->pengirimanDetails->first()?->bahanBakuSupplier?->supplier;
                                $totalPiutang = $supplier ? \App\Models\CatatanPiutang::where('supplier_id', $supplier->id)
                                    ->where('status', '!=', 'lunas')
                                    ->sum('sisa_piutang') : 0;
                            @endphp

                            {{-- Info Total Piutang Supplier --}}
                            <div class="bg-white border-2 border-blue-300 rounded-lg p-4 mb-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="text-xs font-medium text-blue-700">Total Piutang Supplier</label>
                                        @if($supplier)
                                            <p class="text-xs text-gray-600 mt-0.5">{{ $supplier->nama }}</p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-bold {{ $totalPiutang > 0 ? 'text-orange-600' : 'text-gray-600' }}">
                                            Rp {{ number_format($totalPiutang, 0, ',', '.') }}
                                        </p>
                                        @if($totalPiutang > 0)
                                            <p class="text-xs text-orange-600 mt-0.5">
                                                <i class="fas fa-exclamation-circle mr-1"></i>Ada piutang
                                            </p>
                                        @else
                                            <p class="text-xs text-gray-500 mt-0.5">
                                                <i class="fas fa-check-circle mr-1"></i>Tidak ada piutang
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($approval->catatan_piutang_id)
                                <div class="bg-white border border-blue-200 rounded-lg p-4 mb-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-xs font-medium text-blue-700">Piutang Dipilih</label>
                                            <p class="mt-1 text-sm font-semibold">
                                                {{ $approval->catatanPiutang ? '#'.$approval->catatanPiutang->id : '-' }}
                                            </p>
                                        </div>
                                        <div>
                                            <label class="text-xs font-medium text-blue-700">Jumlah Pemotongan</label>
                                            <p class="mt-1 text-sm text-red-600 font-bold">- Rp {{ number_format($approval->piutang_amount, 0, ',', '.') }}</p>
                                        </div>
                                        @if($approval->piutang_notes)
                                        <div class="col-span-2">
                                            <label class="text-xs font-medium text-blue-700">Catatan</label>
                                            <p class="mt-1 text-sm text-gray-700">{{ $approval->piutang_notes }}</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            {{-- Edit Piutang Form --}}
                            @if($canManage && $approval->status === 'pending')
                            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                                <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                                    <i class="fas fa-edit text-indigo-600 mr-2"></i>
                                    {{ $approval->catatan_piutang_id ? 'Edit' : 'Tambah' }} Pemotongan Piutang (Opsional)
                                </h3>

                                @php
                                    $piutangList = $supplier ? \App\Models\CatatanPiutang::where('supplier_id', $supplier->id)
                                        ->where('status', '!=', 'lunas')
                                        ->where('sisa_piutang', '>', 0)
                                        ->orderBy('tanggal_piutang', 'asc') // Piutang terlama dulu (FIFO)
                                        ->with('supplier')
                                        ->get() : collect();
                                @endphp

                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-2">
                                            Pilih Piutang Supplier
                                            @if($supplier)
                                                <span class="text-blue-600">({{ $supplier->nama }})</span>
                                            @endif
                                        </label>
                                        <select wire:model.live="piutangForm.catatan_piutang_id" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">-- Tidak ada pemotongan piutang --</option>
                                            @foreach($piutangList as $index => $piutang)
                                                <option value="{{ $piutang->id }}">
                                                    {{ $index === 0 ? '⭐ ' : '' }}{{ \Carbon\Carbon::parse($piutang->tanggal_piutang)->format('d/m/Y') }} - Sisa: Rp {{ number_format($piutang->sisa_piutang, 0, ',', '.') }}{{ $index === 0 ? ' (Terlama)' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($piutangList->isEmpty() && $supplier)
                                            <p class="text-xs text-gray-500 mt-1">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Supplier ini tidak memiliki piutang aktif
                                            </p>
                                        @elseif(!$piutangList->isEmpty())
                                            <p class="text-xs text-blue-600 mt-1">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Piutang diurutkan dari yang terlama (sistem FIFO - First In First Out)
                                            </p>
                                        @endif
                                    </div>

                                    @if($piutangForm['catatan_piutang_id'])
                                        @php
                                            $selectedPiutang = $piutangList->firstWhere('id', $piutangForm['catatan_piutang_id']);
                                        @endphp
                                        <div class="bg-blue-50 border border-blue-200 rounded p-3">
                                            <p class="text-xs font-medium text-blue-800 mb-2">Informasi Piutang Terpilih</p>
                                            <div class="grid grid-cols-2 gap-2 text-xs">
                                                <div>
                                                    <span class="text-gray-600">Total Piutang:</span>
                                                    <span class="font-semibold ml-2">Rp {{ number_format($selectedPiutang->jumlah_piutang ?? 0, 0, ',', '.') }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-600">Sisa Piutang:</span>
                                                    <span class="font-semibold ml-2 text-orange-600">Rp {{ number_format($selectedPiutang->sisa_piutang ?? 0, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-2">
                                                Jumlah Pemotongan <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs font-medium">Rp</span>
                                                <input
                                                    type="text"
                                                    id="piutang_amount_display"
                                                    value="{{ number_format($piutangForm['amount'] ?? 0, 0, ',', '.') }}"
                                                    class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
                                                    placeholder="0"
                                                    oninput="formatCurrencyPiutang(this, 'piutang_amount_hidden')"
                                                    onblur="autoSavePiutang()"
                                                >
                                            </div>
                                            <input type="hidden" wire:model.defer="piutangForm.amount" id="piutang_amount_hidden">
                                            <p class="text-xs text-gray-500 mt-1">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Maksimal: Rp {{ number_format($selectedPiutang->sisa_piutang ?? 0, 0, ',', '.') }}
                                            </p>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                                            <textarea
                                                wire:model="piutangForm.notes"
                                                rows="2"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
                                                placeholder="Tambahkan catatan untuk pemotongan ini..."
                                            ></textarea>
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                                    <p class="text-xs text-yellow-800 flex items-start">
                                        <i class="fas fa-exclamation-triangle mr-2 mt-0.5"></i>
                                        <span>
                                            <strong>Perhatian:</strong> Jumlah pemotongan akan otomatis dikurangkan dari pembayaran dan dicatat sebagai pembayaran piutang ketika approval disetujui.
                                        </span>
                                    </p>
                                </div>

                                <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded-md">
                                    <p class="text-xs text-green-800 flex items-center">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        <span>Perubahan akan tersimpan otomatis saat Anda selesai mengetik</span>
                                    </p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Refraksi Pembayaran --}}
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow-sm border border-green-200">
                        <div class="border-b border-green-200 bg-green-100 px-6 py-4">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-hand-holding-usd text-green-600 mr-3"></i>
                                Refraksi Pembayaran (Supplier)
                            </h2>
                            <p class="text-sm text-green-700 mt-1">Refraksi yang dikenakan kepada supplier</p>
                        </div>
                        <div class="p-6">
                            @if($approval->refraksi_type && $approval->refraksi_value > 0)
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="text-xs font-medium text-green-700">Jenis</label>
                                        <p class="mt-1 text-sm font-semibold">
                                            @if($approval->refraksi_type === 'qty')
                                                <i class="fas fa-percentage text-green-600 mr-1"></i>Qty ({{ number_format($approval->refraksi_value, 2) }}%)
                                            @elseif($approval->refraksi_type === 'rupiah')
                                                <i class="fas fa-money-bill text-green-600 mr-1"></i>Rp {{ number_format($approval->refraksi_value, 0, ',', '.') }}/kg
                                            @elseif($approval->refraksi_type === 'lainnya')
                                                <i class="fas fa-calculator text-green-600 mr-1"></i>Lainnya (Rp {{ number_format($approval->refraksi_value, 0, ',', '.') }})
                                            @else
                                                <i class="fas fa-calculator text-green-600 mr-1"></i>{{ ucfirst($approval->refraksi_type) }}
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-green-700">Potongan</label>
                                        <p class="mt-1 text-sm text-red-600 font-bold">- Rp {{ number_format($approval->refraksi_amount, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="col-span-2">
                                        <label class="text-xs font-medium text-green-700">Total Pembayaran</label>
                                        <p class="mt-1 text-lg text-green-600 font-bold">Rp {{ number_format($approval->amount_after_refraksi, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            @else
                                <div class="bg-white border border-green-200 rounded-lg p-4 mb-4 text-center">
                                    <p class="text-sm text-gray-500">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Tidak ada refraksi pembayaran diterapkan
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">Pembayaran menggunakan harga penuh tanpa potongan refraksi</p>
                                </div>
                            @endif

                            {{-- Edit Refraksi Form --}}
                            @if($canManage && $approval->status === 'pending')
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                                    <i class="fas fa-edit text-yellow-600 mr-2"></i>
                                    Edit Refraksi Pembayaran (Opsional - Isi 0 untuk tanpa refraksi)
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-2">Jenis Refraksi</label>
                                        <select wire:model="refraksiForm.type" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-green-500 focus:border-green-500">
                                            <option value="qty">Qty (%)</option>
                                            <option value="rupiah">Rupiah (Rp/kg)</option>
                                            <option value="lainnya">Refraksi Lainnya (Input Manual)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-2">
                                            Nilai Refraksi (0 = tanpa refraksi)
                                            @if($refraksiForm['type'] === 'qty')
                                                <span class="text-gray-500">(dalam %)</span>
                                            @elseif($refraksiForm['type'] === 'rupiah')
                                                <span class="text-gray-500">(Rp per kg)</span>
                                            @else
                                                <span class="text-gray-500">(Total potongan dalam Rp)</span>
                                            @endif
                                        </label>
                                        <div class="relative">
                                            @if($refraksiForm['type'] === 'qty')
                                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs font-medium">%</span>
                                            @elseif($refraksiForm['type'] === 'rupiah' || $refraksiForm['type'] === 'lainnya')
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs font-medium">Rp</span>
                                            @endif
                                            <input
                                                type="text"
                                                id="refraksi_value_display"
                                                value="{{ number_format($refraksiForm['value'] ?? 0, $refraksiForm['type'] === 'qty' ? 2 : 0, ',', '.') }}"
                                                class="w-full {{ $refraksiForm['type'] === 'qty' ? 'pr-8' : 'pl-9' }} px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-green-500 focus:border-green-500"
                                                placeholder="0"
                                                oninput="formatCurrencyRefraksi(this, 'refraksi_value_hidden', '{{ $refraksiForm['type'] }}')"
                                                onblur="autoSaveRefraksi()"
                                            >
                                        </div>
                                        <input type="hidden" wire:model.defer="refraksiForm.value" id="refraksi_value_hidden">
                                    </div>
                                </div>

                                {{-- Info berdasarkan jenis refraksi --}}
                                <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                    <p class="text-xs text-blue-800 flex items-start">
                                        <i class="fas fa-info-circle mr-2 mt-0.5"></i>
                                        <span>
                                            @if($refraksiForm['type'] === 'qty')
                                                <strong>Refraksi Qty:</strong> Potongan berdasarkan persentase dari total qty. Sistem akan menghitung potongan rupiah secara otomatis.
                                            @elseif($refraksiForm['type'] === 'rupiah')
                                                <strong>Refraksi Rupiah:</strong> Potongan harga per kilogram. Total potongan = nilai refraksi × total qty pengiriman.
                                            @else
                                                <strong>Refraksi Lainnya:</strong> Masukkan nominal potongan secara manual dalam rupiah. Nominal yang dimasukkan akan langsung menjadi total potongan pembayaran.
                                            @endif
                                        </span>
                                    </p>
                                </div>

                                <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded-md">
                                    <p class="text-xs text-green-800 flex items-center">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        <span>Perubahan akan tersimpan otomatis saat Anda selesai mengetik</span>
                                    </p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Right Column: Approval Actions --}}
                <div class="lg:col-span-1">
                    <div class="sticky top-6 space-y-6">
                        {{-- Ringkasan Pembayaran Akhir --}}
                        @php
                            $piutangPotongan = $approval->piutang_amount ?? 0;
                            $refraksiPotongan = $approval->refraksi_amount ?? 0;
                            $totalPembayaran = max(0, ($approval->amount_after_refraksi ?? 0) - $piutangPotongan);
                        @endphp

                        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                                <h2 class="text-base font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-wallet text-gray-600 mr-2"></i>
                                    Ringkasan Pembayaran
                                </h2>
                            </div>
                            <div class="p-5 space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Total Awal Pengiriman</span>
                                    <span class="text-sm font-semibold text-gray-900">Rp {{ number_format($pengiriman->total_harga_kirim ?? 0, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Potongan Refraksi</span>
                                    <span class="text-sm font-semibold text-red-600">- Rp {{ number_format($refraksiPotongan, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Potongan Piutang Supplier</span>
                                    <span class="text-sm font-semibold text-red-600">- Rp {{ number_format($piutangPotongan, 0, ',', '.') }}</span>
                                </div>
                                <div class="border-t border-gray-200 pt-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-base font-bold text-gray-900">Total Dibayarkan</span>
                                        <span class="text-xl font-extrabold text-green-600">Rp {{ number_format($totalPembayaran, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Approval Card --}}
                        <div class="bg-white rounded-lg shadow-lg border border-gray-200">
                            <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4 rounded-t-lg">
                                <h2 class="text-lg font-bold text-white flex items-center">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Approval Action
                                </h2>
                            </div>
                            <div class="p-6">
                                {{-- Approval Info --}}
                                <div class="mb-6 space-y-3">
                                    <div>
                                        <label class="text-xs font-medium text-gray-500">Status Approval</label>
                                        <p class="mt-1 text-sm font-semibold text-gray-900">
                                            @if($approval->status === 'pending')
                                                <i class="fas fa-clock text-blue-600 mr-1"></i>Menunggu Approval
                                            @elseif($approval->status === 'completed')
                                                <i class="fas fa-check-circle text-green-600 mr-1"></i>Selesai
                                            @elseif($approval->status === 'rejected')
                                                <i class="fas fa-times-circle text-red-600 mr-1"></i>Ditolak
                                            @else
                                                -
                                            @endif
                                        </p>
                                    </div>

                                    @if($approval->staff)
                                        <div>
                                            <label class="text-xs font-medium text-gray-500">Disetujui Oleh</label>
                                            <p class="mt-1 text-sm text-gray-900">
                                                <i class="fas fa-check text-green-500 mr-1"></i>
                                                {{ $approval->staff->nama }} (Staff Accounting)
                                            </p>
                                        </div>
                                    @endif

                                    @if($approval->manager)
                                        <div>
                                            <label class="text-xs font-medium text-gray-500">Disetujui Oleh</label>
                                            <p class="mt-1 text-sm text-gray-900">
                                                <i class="fas fa-check text-green-500 mr-1"></i>
                                                {{ $approval->manager->nama }} (Manager Accounting)
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Notes --}}
                                @if($canManage && $approval->status === 'pending')
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                                        <textarea
                                            wire:model="notes"
                                            rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-green-500 focus:border-green-500"
                                            placeholder="Tambahkan catatan untuk approval..."
                                        ></textarea>
                                    </div>
                                @elseif(!$canManage)
                                    <div class="mb-4 p-3 bg-gray-50 border border-gray-200 rounded-md text-sm text-gray-600">
                                        Hanya tim accounting yang dapat menambahkan catatan approval.
                                    </div>
                                @endif

                                {{-- Upload Bukti Pembayaran (Wajib untuk semua anggota keuangan) --}}
                                @if($canManage && $approval->status === 'pending')
                                    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                        <label class="flex items-center text-sm font-semibold text-gray-900 mb-2">
                                            <i class="fas fa-file-upload text-blue-600 mr-2"></i>
                                            Bukti Pembayaran <span class="text-red-500 ml-1">*</span>
                                        </label>
                                        <p class="text-xs text-gray-600 mb-3">
                                            Upload bukti pembayaran (multiple files) dalam format JPG, PNG, atau PDF (Total Max: 20MB)
                                        </p>

                                        <input
                                            type="file"
                                            wire:model="buktiPembayaran"
                                            accept=".jpg,.jpeg,.png,.pdf"
                                            multiple
                                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 file:cursor-pointer"
                                        >

                                        {{-- Upload Progress Indicator --}}
                                        <div wire:loading wire:target="buktiPembayaran" class="mt-3">
                                            <div class="flex items-center text-blue-600">
                                                <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <span class="text-sm font-medium">Mengunggah file...</span>
                                            </div>
                                        </div>

                                        @error('buktiPembayaran.*')
                                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                        @enderror

                                        {{-- Preview --}}
                                        @if($buktiPembayaran && count($buktiPembayaran) > 0)
                                            <div class="mt-3 space-y-2">
                                                @php
                                                    $totalSize = 0;
                                                    foreach($buktiPembayaran as $file) {
                                                        $totalSize += $file->getSize();
                                                    }
                                                @endphp

                                                <div class="p-3 bg-white border border-blue-200 rounded-md">
                                                    <p class="text-xs font-medium text-gray-700 mb-2">
                                                        <i class="fas fa-files text-blue-600 mr-2"></i>
                                                        {{ count($buktiPembayaran) }} file dipilih
                                                        <span class="text-gray-500 ml-2">
                                                            (Total: {{ number_format($totalSize / 1024 / 1024, 2) }} MB)
                                                        </span>
                                                    </p>

                                                    @if($totalSize > 20 * 1024 * 1024)
                                                        <p class="text-xs text-red-600 font-semibold">
                                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                                            Total ukuran file melebihi 20 MB!
                                                        </p>
                                                    @endif
                                                </div>

                                                @foreach($buktiPembayaran as $index => $file)
                                                    <div class="p-3 bg-white border border-gray-200 rounded-md">
                                                        <p class="text-xs font-medium text-gray-700 mb-1">
                                                            <i class="fas fa-file text-blue-600 mr-2"></i>
                                                            {{ $file->getClientOriginalName() }}
                                                        </p>
                                                        <p class="text-xs text-gray-500">
                                                            Ukuran: {{ number_format($file->getSize() / 1024, 2) }} KB
                                                        </p>

                                                        {{-- Image Preview --}}
                                                        @if(in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png']))
                                                            <div class="mt-2">
                                                                <img src="{{ $file->temporaryUrl() }}" alt="Preview" class="w-full h-auto rounded border border-gray-200 max-h-48 object-contain">
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div class="mt-2 flex items-start">
                                            <i class="fas fa-info-circle text-blue-500 text-xs mt-0.5 mr-1"></i>
                                            <p class="text-xs text-blue-700">
                                                Bukti pembayaran wajib diupload untuk menyelesaikan approval. Anda dapat mengupload multiple files dengan total maksimal 20 MB.
                                            </p>
                                        </div>
                                    @elseif($approval->bukti_pembayaran)
                                        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                            <label class="flex items-center text-sm font-semibold text-gray-900 mb-2">
                                                <i class="fas fa-file text-blue-600 mr-2"></i>
                                                Bukti Pembayaran
                                            </label>

                                            @php
                                                // Check if bukti_pembayaran is JSON array or single file
                                                $buktiFiles = [];
                                                try {
                                                    $decoded = json_decode($approval->bukti_pembayaran, true);
                                                    if (is_array($decoded)) {
                                                        $buktiFiles = $decoded;
                                                    } else {
                                                        $buktiFiles = [$approval->bukti_pembayaran];
                                                    }
                                                } catch (\Exception $e) {
                                                    $buktiFiles = [$approval->bukti_pembayaran];
                                                }
                                            @endphp

                                            @if(count($buktiFiles) > 1)
                                                <p class="text-xs text-gray-600 mb-3">{{ count($buktiFiles) }} file terupload</p>
                                            @endif

                                            <div class="space-y-2">
                                                @foreach($buktiFiles as $index => $filePath)
                                                    <a
                                                        href="{{ Storage::url($filePath) }}"
                                                        target="_blank"
                                                        class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors"
                                                    >
                                                        <i class="fas fa-download mr-2"></i>
                                                        @if(count($buktiFiles) > 1)
                                                            Lihat Bukti Pembayaran #{{ $index + 1 }}
                                                        @else
                                                            Lihat Bukti Pembayaran
                                                        @endif
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Action Buttons --}}
                                @if($canManage && $approval->status === 'pending')
                                    <div class="space-y-3">
                                        <button
                                            wire:click="approve"
                                            wire:confirm="Apakah Anda yakin ingin menyetujui approval ini?"
                                            wire:loading.attr="disabled"
                                            wire:target="buktiPembayaran"
                                            class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <span wire:loading.remove wire:target="buktiPembayaran">
                                                <i class="fas fa-check-circle mr-2"></i>
                                                Approve
                                            </span>
                                            <span wire:loading wire:target="buktiPembayaran" class="flex items-center">
                                                <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Mengunggah...
                                            </span>
                                        </button>
                                        <button
                                            wire:click="reject"
                                            wire:confirm="Apakah Anda yakin ingin menolak approval ini?"
                                            wire:loading.attr="disabled"
                                            wire:target="buktiPembayaran"
                                            class="w-full px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <span wire:loading.remove wire:target="buktiPembayaran">
                                                <i class="fas fa-times-circle mr-2"></i>
                                                Reject
                                            </span>
                                            <span wire:loading wire:target="buktiPembayaran" class="flex items-center">
                                                <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Mengunggah...
                                            </span>
                                        </button>
                                    </div>
                                @elseif(!$canManage && $approval->status === 'pending')
                                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-md text-sm text-gray-600">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Approval hanya dapat dilakukan oleh tim accounting (Staff Accounting, Manager Accounting, Direktur, atau Superadmin).
                                    </div>
                                @elseif($approval->status === 'completed')
                                    <div class="text-center py-4">
                                        <p class="text-sm text-gray-500">
                                            <i class="fas fa-check-circle text-green-500 text-2xl mb-2"></i>
                                            <br>Approval sudah selesai
                                        </p>
                                    </div>
                                @elseif($approval->status === 'rejected')
                                    <div class="text-center py-4">
                                        <p class="text-sm text-gray-500">
                                            <i class="fas fa-times-circle text-red-500 text-2xl mb-2"></i>
                                            <br>Approval ditolak
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        @else
            <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-3"></i>
                <p class="text-red-700 font-medium">Data approval tidak ditemukan</p>
            </div>
        @endif
    </div>
</div>

<script>
    if (typeof window.downloadImage !== 'function') {
        window.downloadImage = function(imageSrc, imageName = 'bukti_foto_bongkar.jpg') {
            const link = document.createElement('a');
            link.href = imageSrc;
            link.download = imageName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        };
    }

    // Format currency for piutang amount
    function formatCurrencyPiutang(displayInput, hiddenInputId) {
        let value = displayInput.value.replace(/[^0-9]/g, '');

        let hiddenInput = document.getElementById(hiddenInputId);
        if (hiddenInput) {
            hiddenInput.value = value;
            hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
        }

        if (value) {
            displayInput.value = parseInt(value).toLocaleString('id-ID');
        } else {
            displayInput.value = '';
        }
    }

    // Format currency for refraksi value
    function formatCurrencyRefraksi(displayInput, hiddenInputId, type) {
        let value = displayInput.value.replace(/[^0-9.,]/g, '');

        // For qty type, allow decimals
        if (type === 'qty') {
            // Replace comma with dot for decimal
            value = value.replace(',', '.');
            // Keep only first dot
            let parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }

            let hiddenInput = document.getElementById(hiddenInputId);
            if (hiddenInput) {
                hiddenInput.value = value;
                hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
            }

            displayInput.value = value;
        } else {
            // For rupiah and lainnya, remove all non-digits
            value = value.replace(/[^0-9]/g, '');

            let hiddenInput = document.getElementById(hiddenInputId);
            if (hiddenInput) {
                hiddenInput.value = value;
                hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
            }

            if (value) {
                displayInput.value = parseInt(value).toLocaleString('id-ID');
            } else {
                displayInput.value = '';
            }
        }
    }

    // Auto-save piutang
    let piutangSaveTimeout;
    function autoSavePiutang() {
        clearTimeout(piutangSaveTimeout);
        piutangSaveTimeout = setTimeout(function() {
            @this.call('updatePiutang');
        }, 500);
    }

    // Auto-save refraksi
    let refraksiSaveTimeout;
    function autoSaveRefraksi() {
        clearTimeout(refraksiSaveTimeout);
        refraksiSaveTimeout = setTimeout(function() {
            @this.call('updateRefraksi');
        }, 500);
    }

    // Listen for Livewire updates to refresh display values
    document.addEventListener('livewire:initialized', () => {
        Livewire.hook('morph.updated', ({ el, component }) => {
            // Update piutang display when Livewire updates
            const piutangDisplay = document.getElementById('piutang_amount_display');
            const piutangHidden = document.getElementById('piutang_amount_hidden');
            if (piutangDisplay && piutangHidden && piutangHidden.value) {
                const value = piutangHidden.value.replace(/[^0-9]/g, '');
                if (value) {
                    piutangDisplay.value = parseInt(value).toLocaleString('id-ID');
                }
            }

            // Update refraksi display when Livewire updates
            const refraksiDisplay = document.getElementById('refraksi_value_display');
            const refraksiHidden = document.getElementById('refraksi_value_hidden');
            if (refraksiDisplay && refraksiHidden && refraksiHidden.value) {
                const refraksiType = @this.get('refraksiForm.type');
                const value = refraksiHidden.value;

                if (refraksiType === 'qty') {
                    refraksiDisplay.value = value;
                } else {
                    const numValue = value.replace(/[^0-9]/g, '');
                    if (numValue) {
                        refraksiDisplay.value = parseInt(numValue).toLocaleString('id-ID');
                    }
                }
            }
        });
    });
</script>
