<div class="relative">
    {{-- Navigation Breadcrumb --}}
    <div class="bg-white border-b border-gray-200 mb-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-4">
                        <li>
                            <div>
                                <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-500">
                                    <i class="fas fa-home"></i>
                                    <span class="sr-only">Home</span>
                                </a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <i class="fas fa-chevron-right text-gray-300 mr-4"></i>
                                <span class="text-gray-500 text-sm">Accounting</span>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <i class="fas fa-chevron-right text-gray-300 mr-4"></i>
                                <a href="{{ route('accounting.approval-pembayaran') }}" class="text-gray-500 hover:text-gray-700 text-sm">Approval Pembayaran</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <i class="fas fa-chevron-right text-gray-300 mr-4"></i>
                                <span class="text-gray-900 text-sm font-medium">Detail</span>
                            </div>
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
                                                <p class="mt-1 text-sm text-gray-900">{{ $detail->bahanBakuSupplier->bahanBaku->nama ?? '-' }}</p>
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
            @if($approval->refraksi_type)
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow-sm border border-green-200 mb-6">
                    <div class="border-b border-green-200 bg-green-100 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-hand-holding-usd text-green-600 mr-3"></i>
                            Refraksi Pembayaran (Supplier)
                        </h2>
                        <p class="text-sm text-green-700 mt-1">Refraksi yang dikenakan kepada supplier</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="text-sm font-medium text-green-700">Jenis Refraksi</label>
                                <p class="mt-1 text-base text-gray-900 font-semibold">
                                    @if($approval->refraksi_type === 'qty')
                                        <i class="fas fa-percentage text-green-600 mr-2"></i>Qty (%)
                                    @else
                                        <i class="fas fa-money-bill text-green-600 mr-2"></i>Rupiah (Rp/kg)
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-green-700">Nilai Refraksi</label>
                                <p class="mt-1 text-base text-gray-900 font-semibold">
                                    @if($approval->refraksi_type === 'qty')
                                        {{ number_format($approval->refraksi_value, 2, ',', '.') }}%
                                    @else
                                        Rp {{ number_format($approval->refraksi_value, 0, ',', '.') }}/kg
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
                            <div>
                                <label class="text-sm font-medium text-green-700">Total Pembayaran ke Supplier</label>
                                <p class="mt-1 text-xl text-green-600 font-bold">
                                    Rp {{ number_format($approval->amount_after_refraksi ?? $pengiriman->total_harga_kirim, 0, ',', '.') }}
                                </p>
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
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                <div class="border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-camera text-blue-600 mr-3"></i>
                        Bukti Foto Bongkar
                    </h2>
                </div>
                <div class="p-6">
                    @if($pengiriman->bukti_foto_bongkar)
                        <div class="flex flex-col items-center">
                            <div class="relative group w-full max-w-2xl">
                                <img
                                    src="{{ asset('storage/' . $pengiriman->bukti_foto_bongkar) }}"
                                    alt="Bukti Foto Bongkar"
                                    class="w-full h-auto rounded-lg shadow-md border-2 border-gray-200 hover:border-blue-400 transition-all cursor-pointer"
                                    onclick="openImageModal('{{ asset('storage/' . $pengiriman->bukti_foto_bongkar) }}')"
                                >
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all rounded-lg flex items-center justify-center">
                                    <i class="fas fa-search-plus text-white text-3xl opacity-0 group-hover:opacity-100 transition-all"></i>
                                </div>
                            </div>
                            <p class="mt-3 text-sm text-gray-500 flex items-center">
                                <i class="fas fa-info-circle mr-2"></i>
                                Klik gambar untuk memperbesar
                            </p>
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

            {{-- Image Modal --}}
            <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden items-center justify-center p-4" onclick="closeImageModal()">
                <div class="relative max-w-6xl max-h-full">
                    <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                    <img id="modalImage" src="" alt="Bukti Foto Bongkar" class="max-w-full max-h-[90vh] rounded-lg shadow-2xl">
                </div>
            </div>

            <script>
                function openImageModal(imageSrc) {
                    const modal = document.getElementById('imageModal');
                    const modalImage = document.getElementById('modalImage');
                    modalImage.src = imageSrc;
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    document.body.style.overflow = 'hidden';
                }

                function closeImageModal() {
                    const modal = document.getElementById('imageModal');
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    document.body.style.overflow = '';
                }

                // Close modal with Escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        closeImageModal();
                    }
                });
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
                                            href="{{ asset('storage/' . $approval->bukti_pembayaran) }}"
                                            target="_blank"
                                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors"
                                        >
                                            <i class="fas fa-external-link-alt mr-2"></i>
                                            Buka PDF
                                        </a>
                                        <a
                                            href="{{ asset('storage/' . $approval->bukti_pembayaran) }}"
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
                                <div class="relative group w-full max-w-2xl">
                                    <img
                                        src="{{ asset('storage/' . $approval->bukti_pembayaran) }}"
                                        alt="Bukti Pembayaran"
                                        class="w-full h-auto rounded-lg shadow-md border-2 border-gray-200 hover:border-green-400 transition-all cursor-pointer"
                                        onclick="openPaymentModal('{{ asset('storage/' . $approval->bukti_pembayaran) }}')"
                                    >
                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all rounded-lg flex items-center justify-center">
                                        <i class="fas fa-search-plus text-white text-3xl opacity-0 group-hover:opacity-100 transition-all"></i>
                                    </div>
                                </div>
                                <p class="mt-3 text-sm text-gray-500 flex items-center">
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
                <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden items-center justify-center p-4" onclick="closePaymentModal()">
                    <div class="relative max-w-6xl max-h-full">
                        <button onclick="closePaymentModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                        <img id="paymentModalImage" src="" alt="Bukti Pembayaran" class="max-w-full max-h-[90vh] rounded-lg shadow-2xl">
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
