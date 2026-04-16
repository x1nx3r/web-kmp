@extends('pages.laporan.base')

@section('report-content')
<div class="bg-white rounded-xl shadow-lg border border-gray-100 p-4 sm:p-6 mb-6">
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 w-full">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500" />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500" />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500">
                    <option value="">Semua</option>
                    @foreach(['pending','menunggu_fisik','menunggu_verifikasi','berhasil','gagal'] as $st)
                        <option value="{{ $st }}" {{ $status===$st ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ', $st)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">PIC Purchasing</label>
                <select name="purchasing" class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500">
                    <option value="">Semua</option>
                    @foreach($purchasingUsers as $u)
                        <option value="{{ $u->id }}" {{ (string)$purchasing===(string)$u->id ? 'selected' : '' }}>{{ $u->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Pabrik (Klien)</label>
                <select name="pabrik" class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500">
                    <option value="">Semua</option>
                    @foreach($pabrikList as $k)
                        <option value="{{ $k->id }}" {{ (string)$pabrik===(string)$k->id ? 'selected' : '' }}>{{ $k->nama }}{{ $k->cabang ? ' - '.$k->cabang : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Supplier</label>
                <select name="supplier" class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500">
                    <option value="">Semua</option>
                    @foreach($supplierList as $s)
                        <option value="{{ $s->id }}" {{ (string)$supplier===(string)$s->id ? 'selected' : '' }}>{{ $s->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 lg:col-span-4">
                <label class="block text-xs font-medium text-gray-700 mb-1">Search (No Pengiriman / PO)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Ketik no pengiriman atau no PO" class="w-full pl-10 rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500" />
                </div>
            </div>

            <div class="sm:col-span-2 lg:col-span-2 flex items-end gap-2">
                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700">
                    <i class="fas fa-filter mr-2"></i> Terapkan
                </button>
                <a href="{{ route('laporan.evaluasiProcurement') }}" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200">
                    Reset
                </a>
            </div>
        </form>

        <div class="flex-shrink-0">
            <form method="POST" action="{{ route('laporan.evaluasiProcurement.export') }}">
                @csrf
                <input type="hidden" name="start_date" value="{{ $startDate }}" />
                <input type="hidden" name="end_date" value="{{ $endDate }}" />
                <input type="hidden" name="status" value="{{ $status }}" />
                <input type="hidden" name="purchasing" value="{{ $purchasing }}" />
                <input type="hidden" name="pabrik" value="{{ $pabrik }}" />
                <input type="hidden" name="supplier" value="{{ $supplier }}" />
                <input type="hidden" name="search" value="{{ $search }}" />
                <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                    <i class="fas fa-file-excel mr-2"></i> Export Excel
                </button>
            </form>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
    <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900">Data Evaluasi Procurement</h3>
        <div class="text-xs text-gray-500">Menampilkan {{ $pengirimanData->count() }} dari {{ $pengirimanData->total() }} data</div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Hari</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Purchasing</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Supplier</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Klien - Cabang</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Produk</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Qty</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Jumlah</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Keterangan</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @foreach($pengirimanData as $p)
                    @php
                        $details = collect($p->pengirimanDetails ?? []);
                        $supplierNames = $details->map(fn($d) => optional(optional($d->bahanBakuSupplier)->supplier)->nama)->filter()->unique()->values();
                        $produkNames = $details->map(fn($d) => optional($d->bahanBakuSupplier)->nama)->filter()->unique()->values();

                        $supplierDisplay = $supplierNames->isNotEmpty() ? $supplierNames->implode(', ') : 'N/A';
                        $produkDisplay = $produkNames->isNotEmpty() ? $produkNames->implode(', ') : ($details->isNotEmpty() ? 'N/A' : 'Tidak ada detail');

                        // Tanggal & hari: ambil dari forecast (tanggal_forecast min/terawal)
                        $displayTanggal = 'N/A';
                        $displayHari = 'N/A';
                        if (!empty($p->tanggal_forecast_min)) {
                            $displayTanggal = \Carbon\Carbon::parse($p->tanggal_forecast_min)->format('d/m/Y');
                        }
                        if (!empty($p->hari_kirim_forecast_min)) {
                            $displayHari = $p->hari_kirim_forecast_min;
                        } elseif (!empty($p->tanggal_forecast_min)) {
                            $displayHari = \Carbon\Carbon::parse($p->tanggal_forecast_min)->locale('id')->isoFormat('dddd');
                        }

                        // Keterangan: selain gagal => default 'Done' (kalau tidak ada catatan)
                        if (($p->status ?? null) === 'gagal') {
                            $keterangan = $p->catatan ?: '-';
                        } else {
                            $keterangan = $p->catatan ?: 'Done';
                        }
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $displayTanggal }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $displayHari }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ optional($p->purchasing)->nama ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $supplierDisplay }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ (optional(optional($p->order)->klien)->nama ?? 'N/A') }} - {{ (optional(optional($p->order)->klien)->cabang ?? 'N/A') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $produkDisplay }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 text-right">{{ number_format((float)($p->display_qty ?? $p->total_qty_kirim ?? 0), 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 text-right">Rp {{ number_format((float)($p->display_harga ?? $p->total_harga_kirim ?? 0), 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 max-w-[300px] truncate" title="{{ $keterangan }}">{{ $keterangan }}</td>
                    </tr>
                @endforeach

                @if($pengirimanData->isEmpty())
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-sm text-gray-500">Tidak ada data untuk filter yang dipilih.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="px-4 sm:px-6 py-4 border-t border-gray-100">
        {{ $pengirimanData->links() }}
    </div>
</div>
@endsection
