<div class="relative">
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
                            <span class="text-gray-900 font-medium">Detail</span>
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
                <h1 class="text-2xl font-bold text-gray-900">Detail Approval Pembayaran</h1>
            </div>

            {{-- Status Badge --}}
            @if($approval)
                <span class="px-4 py-2 text-sm font-semibold rounded-full
                    @if($approval->status === 'pending') bg-yellow-100 text-yellow-800
                    @elseif($approval->status === 'approved') bg-green-100 text-green-800
                    @elseif($approval->status === 'rejected') bg-red-100 text-red-800
                    @else bg-gray-100 text-gray-800
                    @endif">
                    <i class="fas fa-circle text-xs mr-1"></i>
                    {{ ucfirst($approval->status) }}
                </span>
            @endif
        </div>

        @if($approval && $pengiriman)
            {{-- Informasi Pengiriman --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                <div class="border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-shipping-fast text-blue-600 mr-3"></i>
                        Informasi Pengiriman
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                        <div class="md:col-span-2">
                            <label class="text-sm font-medium text-gray-500">Total Qty Kirim</label>
                            <p class="mt-1 text-base text-gray-900 font-semibold">{{ number_format($pengiriman->total_qty_kirim, 2, ',', '.') }} kg</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-sm font-medium text-gray-500">Total Harga (Original)</label>
                            <p class="mt-1 text-xl text-gray-900 font-bold">Rp {{ number_format($pengiriman->total_harga_kirim, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    {{-- Pengiriman Details --}}
                    @if($pengiriman->pengirimanDetails && count($pengiriman->pengirimanDetails) > 0)
                        <div class="mt-6">
                            <h3 class="text-sm font-semibold text-gray-700 mb-3">Detail Pengiriman</h3>
                            <div class="space-y-4">
                                @foreach($pengiriman->pengirimanDetails as $detail)
                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="text-xs font-medium text-gray-500">Supplier</label>
                                                <p class="mt-1 text-sm text-gray-900">{{ $detail->bahanBakuSupplier->supplier->nama ?? '-' }}</p>
                                            </div>
                                            <div>
                                                <label class="text-xs font-medium text-gray-500">Bahan Baku</label>
                                                <p class="mt-1 text-sm text-gray-900">{{ $detail->bahanBakuSupplier->nama ?? '-' }}</p>
                                            </div>
                                            <div>
                                                <label class="text-xs font-medium text-gray-500">Qty Kirim</label>
                                                <p class="mt-1 text-sm text-gray-900 font-semibold">{{ number_format($detail->qty_kirim, 2, ',', '.') }} kg</p>
                                            </div>
                                            <div>
                                                <label class="text-xs font-medium text-gray-500">Harga Satuan</label>
                                                <p class="mt-1 text-sm text-gray-900">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}/kg</p>
                                            </div>
                                            <div class="md:col-span-2">
                                                <label class="text-xs font-medium text-gray-500">Total Harga</label>
                                                <p class="mt-1 text-base text-gray-900 font-bold">Rp {{ number_format($detail->total_harga, 0, ',', '.') }}</p>
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
            @if($invoicePenagihan && $invoicePenagihan->refraksi_type)
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg shadow-sm border border-purple-200 mb-6">
                    <div class="border-b border-purple-200 bg-purple-100 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-file-invoice text-purple-600 mr-3"></i>
                            Refraksi Penagihan (Customer)
                        </h2>
                        <p class="text-sm text-purple-700 mt-1">Refraksi yang dikenakan kepada customer</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="text-sm font-medium text-purple-700">Jenis Refraksi</label>
                                <p class="mt-1 text-base text-gray-900 font-semibold">
                                    @if($invoicePenagihan->refraksi_type === 'qty')
                                        <i class="fas fa-percentage text-purple-600 mr-2"></i>Qty (%)
                                    @else
                                        <i class="fas fa-money-bill text-purple-600 mr-2"></i>Rupiah (Rp/kg)
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-purple-700">Nilai Refraksi</label>
                                <p class="mt-1 text-base text-gray-900 font-semibold">
                                    @if($invoicePenagihan->refraksi_type === 'qty')
                                        {{ number_format($invoicePenagihan->refraksi_value, 2, ',', '.') }}%
                                    @else
                                        Rp {{ number_format($invoicePenagihan->refraksi_value, 0, ',', '.') }}/kg
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-purple-700">Qty Sebelum Refraksi</label>
                                <p class="mt-1 text-base text-gray-900">{{ number_format($invoicePenagihan->qty_before_refraksi, 2, ',', '.') }} kg</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-purple-700">Qty Setelah Refraksi</label>
                                <p class="mt-1 text-base text-gray-900 font-semibold">{{ number_format($invoicePenagihan->qty_after_refraksi, 2, ',', '.') }} kg</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-purple-700">Potongan Refraksi</label>
                                <p class="mt-1 text-xl text-red-600 font-bold">
                                    - Rp {{ number_format($invoicePenagihan->refraksi_amount, 0, ',', '.') }}
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-purple-700">Total Invoice Penagihan</label>
                                <p class="mt-1 text-xl text-green-600 font-bold">
                                    Rp {{ number_format($invoicePenagihan->amount_after_refraksi ?? $pengiriman->total_harga_kirim, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-purple-50 rounded-lg border border-purple-200 p-4 mb-6">
                    <p class="text-purple-700 text-sm flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        Belum ada refraksi penagihan untuk pengiriman ini
                    </p>
                </div>
            @endif

            {{-- Refraksi Pembayaran (dari Approval) --}}
            @if($approval->refraksi_type || $approval->piutang_amount > 0)
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow-sm border border-green-200 mb-6">
                    <div class="border-b border-green-200 bg-green-100 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-hand-holding-usd text-green-600 mr-3"></i>
                            Refraksi Pembayaran (Supplier)
                        </h2>
                        <p class="text-sm text-green-700 mt-1">Refraksi dan potongan piutang yang dikenakan kepada supplier</p>
                    </div>
                    <div class="p-6">
                        @if($approval->refraksi_type)
                            <div class="mb-6">
                                <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                    <i class="fas fa-percent text-green-600 mr-2"></i>
                                    Refraksi
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="text-sm font-medium text-green-700">Jenis Refraksi</label>
                                        <p class="mt-1 text-base text-gray-900 font-semibold">
                                            @if($approval->refraksi_type === 'qty')
                                                <i class="fas fa-percentage text-green-600 mr-2"></i>Qty (%)
                                            @elseif($approval->refraksi_type === 'rupiah')
                                                <i class="fas fa-money-bill text-green-600 mr-2"></i>Rupiah (Rp/kg)
                                            @else
                                                <i class="fas fa-calculator text-green-600 mr-2"></i>Refraksi Lainnya
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-green-700">Nilai Refraksi</label>
                                        <p class="mt-1 text-base text-gray-900 font-semibold">
                                            @if($approval->refraksi_type === 'qty')
                                                {{ number_format($approval->refraksi_value, 2, ',', '.') }}%
                                            @elseif($approval->refraksi_type === 'rupiah')
                                                Rp {{ number_format($approval->refraksi_value, 0, ',', '.') }}/kg
                                            @else
                                                Rp {{ number_format($approval->refraksi_value, 0, ',', '.') }}
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-green-700">Qty Sebelum Refraksi</label>
                                        <p class="mt-1 text-base text-gray-900">{{ number_format($approval->qty_before_refraksi, 2, ',', '.') }} kg</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-green-700">Qty Setelah Refraksi</label>
                                        <p class="mt-1 text-base text-gray-900 font-semibold">{{ number_format($approval->qty_after_refraksi, 2, ',', '.') }} kg</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-green-700">Potongan Refraksi</label>
                                        <p class="mt-1 text-xl text-red-600 font-bold">
                                            - Rp {{ number_format($approval->refraksi_amount, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($approval->piutang_amount > 0)
                            <div class="@if($approval->refraksi_type) border-t border-green-200 pt-6 @endif">
                                <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                    <i class="fas fa-file-invoice-dollar text-blue-600 mr-2"></i>
                                    Potongan Piutang Supplier
                                </h3>
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @if($approval->catatanPiutang)
                                            <div>
                                                <label class="text-xs font-medium text-blue-700">ID Piutang</label>
                                                <p class="mt-1 text-sm text-gray-900 font-semibold">
                                                    #{{ $approval->catatanPiutang->id }}
                                                </p>
                                            </div>
                                            <div>
                                                <label class="text-xs font-medium text-blue-700">Supplier</label>
                                                <p class="mt-1 text-sm text-gray-900">
                                                    {{ $approval->catatanPiutang->supplier->nama ?? '-' }}
                                                </p>
                                            </div>
                                            <div>
                                                <label class="text-xs font-medium text-blue-700">Tanggal Piutang</label>
                                                <p class="mt-1 text-sm text-gray-900">
                                                    {{ $approval->catatanPiutang->tanggal_piutang ? \Carbon\Carbon::parse($approval->catatanPiutang->tanggal_piutang)->format('d M Y') : '-' }}
                                                </p>
                                            </div>
                                            <div>
                                                <label class="text-xs font-medium text-blue-700">Sisa Piutang Sebelum Potong</label>
                                                <p class="mt-1 text-sm text-gray-900 font-semibold">
                                                    Rp {{ number_format($approval->catatanPiutang->sisa_piutang + $approval->piutang_amount, 0, ',', '.') }}
                                                </p>
                                            </div>
                                        @endif
                                        <div>
                                            <label class="text-xs font-medium text-blue-700">Jumlah Dipotong</label>
                                            <p class="mt-1 text-lg text-red-600 font-bold">
                                                - Rp {{ number_format($approval->piutang_amount, 0, ',', '.') }}
                                            </p>
                                        </div>
                                        @if($approval->catatanPiutang)
                                            <div>
                                                <label class="text-xs font-medium text-blue-700">Sisa Piutang Setelah Potong</label>
                                                <p class="mt-1 text-lg text-orange-600 font-bold">
                                                    Rp {{ number_format($approval->catatanPiutang->sisa_piutang, 0, ',', '.') }}
                                                </p>
                                            </div>
                                        @endif
                                        @if($approval->piutang_notes)
                                            <div class="md:col-span-2">
                                                <label class="text-xs font-medium text-blue-700">Catatan Pemotongan</label>
                                                <p class="mt-1 text-sm text-gray-700 bg-white p-3 rounded border border-blue-200">
                                                    <i class="fas fa-sticky-note text-blue-500 mr-2"></i>{{ $approval->piutang_notes }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="@if($approval->refraksi_type || $approval->piutang_amount > 0) border-t border-green-200 pt-6 mt-6 @endif">
                            <div class="bg-green-100 border-2 border-green-300 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="text-sm font-medium text-green-700">Total Pembayaran ke Supplier</label>
                                        <p class="text-xs text-gray-600 mt-1">
                                            Harga Awal
                                            @if($approval->refraksi_amount > 0)
                                                - Refraksi
                                            @endif
                                            @if($approval->piutang_amount > 0)
                                                - Piutang
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-2xl text-green-600 font-extrabold">
                                            Rp {{ number_format(($approval->amount_after_refraksi ?? $pengiriman->total_harga_kirim) - ($approval->piutang_amount ?? 0), 0, ',', '.') }}
                                        </p>
                                        <p class="text-xs text-gray-600 mt-1">
                                            <span class="line-through">Rp {{ number_format($pengiriman->total_harga_kirim, 0, ',', '.') }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-green-50 rounded-lg border border-green-200 p-4 mb-6">
                    <p class="text-green-700 text-sm flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        Belum ada refraksi pembayaran untuk pengiriman ini
                    </p>
                </div>
            @endif

            {{-- Bukti Foto Bongkar --}}
            @if($pengiriman->bukti_foto_bongkar_raw)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
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
                                                class="w-full h-48 object-cover rounded-lg border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity"
                                                onclick="window.open('{{ $photoUrl }}', '_blank')"
                                                onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgdmlld0JveD0iMCAwIDQwMCAzMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSI0MDAiIGhlaWdodD0iMzAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxjaXJjbGUgY3g9IjIwMCIgY3k9IjEyMCIgcj0iMzAiIGZpbGw9IiM5Q0EzQUYiLz4KPHRleHQgeD0iMjAwIiB5PSIyNjAiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNiIgZmlsbD0iIzZCNzI4MCIgdGV4dC1hbmNob3I9Im1pZGRsZSI+R2FtYmFyIHRpZGFrIGRpdGVtdWthbjwvdGV4dD4KPC9zdmc+'; this.classList.add('opacity-50');">
                                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 rounded-lg transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                                                <div class="flex space-x-2">
                                                    <button onclick="window.open('{{ $photoUrl }}', '_blank')"
                                                            class="bg-white text-blue-600 p-2 rounded-full shadow-lg hover:bg-blue-50 transition-all"
                                                            title="Lihat gambar">
                                                        <i class="fas fa-eye text-sm"></i>
                                                    </button>
                                                    <button onclick="event.stopPropagation(); downloadImage('{{ $photoUrl }}', '{{ $photo }}');"
                                                            class="bg-white text-green-600 p-2 rounded-full shadow-lg hover:bg-green-50 transition-all"
                                                            title="Download gambar">
                                                        <i class="fas fa-download text-sm"></i>
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
            </script>

            {{-- Bukti Pembayaran --}}
            @if($approval->bukti_pembayaran)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                    <div class="border-b border-gray-200 bg-gradient-to-r from-green-50 to-green-100 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-receipt text-green-600 mr-3"></i>
                            Bukti Pembayaran
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-col items-center">
                            @php
                                $extension = pathinfo($approval->bukti_pembayaran, PATHINFO_EXTENSION);
                                $isPdf = strtolower($extension) === 'pdf';
                            @endphp

                            @if($isPdf)
                                {{-- PDF Preview --}}
                                <div class="w-full max-w-2xl">
                                    <div class="bg-gray-50 border-2 border-gray-200 rounded-lg p-6 text-center">
                                        <i class="fas fa-file-pdf text-red-500 text-6xl mb-4"></i>
                                        <p class="text-gray-700 font-medium mb-2">Dokumen PDF</p>
                                        <p class="text-sm text-gray-500 mb-4">Bukti pembayaran dalam format PDF</p>
                                        <a
                                            href="{{ Storage::url($approval->bukti_pembayaran) }}"
                                            target="_blank"
                                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors"
                                        >
                                            <i class="fas fa-external-link-alt mr-2"></i>
                                            Buka PDF
                                        </a>
                                        <a
                                            href="{{ Storage::url($approval->bukti_pembayaran) }}"
                                            download
                                            class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium transition-colors ml-2"
                                        >
                                            <i class="fas fa-download mr-2"></i>
                                            Download
                                        </a>
                                    </div>
                                </div>
                            @else
                                {{-- Image Preview --}}
                                <div class="relative w-full max-w-2xl bg-white p-4 rounded-lg">
                                    <img
                                        src="{{ Storage::url($approval->bukti_pembayaran) }}"
                                        alt="Bukti Pembayaran"
                                        class="w-full h-auto rounded-lg shadow-md border-2 border-gray-200 hover:border-green-400 transition-all cursor-pointer"
                                        onclick="openPaymentModal('{{ Storage::url($approval->bukti_pembayaran) }}')"
                                    >
                                </div>
                                <p class="mt-3 text-sm text-gray-500 flex items-center justify-center">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Klik gambar untuk memperbesar
                                </p>
                            @endif

                            <div class="mt-4 text-center">
                                <p class="text-xs text-gray-500">
                                    <i class="fas fa-user mr-1"></i>
                                    Diupload oleh: {{ $approval->manager->nama ?? '-' }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $approval->manager_approved_at ? $approval->manager_approved_at->format('d M Y, H:i') : '-' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Payment Image Modal --}}
                <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closePaymentModal()">
                    <div class="relative max-w-6xl max-h-full">
                        <button onclick="closePaymentModal(); event.stopPropagation();" class="absolute -top-12 right-0 text-white hover:text-gray-300 bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center z-10">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                        <img id="paymentModalImage" src="" alt="Bukti Pembayaran" class="max-w-full max-h-[90vh] rounded-lg shadow-2xl bg-white">
                    </div>
                </div>

                <script>
                    function openPaymentModal(imageSrc) {
                        const modal = document.getElementById('paymentModal');
                        const modalImage = document.getElementById('paymentModalImage');
                        modalImage.src = imageSrc;
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                        document.body.style.overflow = 'hidden';
                    }

                    function closePaymentModal() {
                        const modal = document.getElementById('paymentModal');
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                        document.body.style.overflow = '';
                    }

                    // Close payment modal with Escape key
                    document.addEventListener('keydown', function(e) {
                        if (e.key === 'Escape') {
                            closePaymentModal();
                        }
                    });
                </script>
            @endif

            {{-- Approval History --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-history text-gray-600 mr-3"></i>
                        Riwayat Approval
                    </h2>
                </div>
                <div class="p-6">
                    @if($approvalHistory && count($approvalHistory) > 0)
                        <div class="space-y-4">
                            @foreach($approvalHistory as $history)
                                <div class="border-l-4 pl-4 py-2
                                    @if($history->action === 'approved') border-green-500 bg-green-50
                                    @elseif($history->action === 'rejected') border-red-500 bg-red-50
                                    @else border-blue-500 bg-blue-50
                                    @endif">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3">
                                                <span class="font-semibold text-gray-900">
                                                    {{ $history->user->nama ?? 'System' }}
                                                </span>
                                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                                    @if($history->action === 'approved') bg-green-200 text-green-800
                                                    @elseif($history->action === 'rejected') bg-red-200 text-red-800
                                                    @else bg-blue-200 text-blue-800
                                                    @endif">
                                                    {{ ucfirst($history->action) }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-600 mt-1">
                                                Role: <span class="font-medium">
                                                    @if($history->role === 'staff')
                                                        Staff Accounting
                                                    @elseif($history->role === 'manager_keuangan')
                                                        Manager Keuangan
                                                    @elseif($history->role === 'superadmin')
                                                        Direktur
                                                    @endif
                                                </span>
                                            </p>
                                            @if($history->catatan)
                                                <p class="text-sm text-gray-700 mt-2 bg-white p-2 rounded border border-gray-200">
                                                    <i class="fas fa-comment-alt text-gray-400 mr-2"></i>{{ $history->catatan }}
                                                </p>
                                            @endif
                                        </div>
                                        <div class="text-right text-sm text-gray-500 ml-4">
                                            <i class="fas fa-clock mr-1"></i>
                                            {{ \Carbon\Carbon::parse($history->created_at)->format('d M Y H:i') }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">
                            <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                            <br>Belum ada riwayat approval
                        </p>
                    @endif
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
