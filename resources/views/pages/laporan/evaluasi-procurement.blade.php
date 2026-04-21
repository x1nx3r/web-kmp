@extends('pages.laporan.base')

@section('report-content')

{{-- ── Filter ────────────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mb-5">

    <form method="GET" id="filter-form">

        {{-- Baris 1: Tanggal --}}
        <div class="flex flex-wrap items-end gap-3 mb-4">
            <div class="flex items-end gap-2">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Tanggal mulai</label>
                    <input type="date" name="start_date" value="{{ $startDate }}"
                           class="h-9 px-3 rounded-lg border border-gray-200 text-sm text-gray-700 focus:outline-none focus:border-gray-400 focus:ring-0 bg-gray-50" />
                </div>
                <span class="text-gray-300 pb-2 text-sm">—</span>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Tanggal selesai</label>
                    <input type="date" name="end_date" value="{{ $endDate }}"
                           class="h-9 px-3 rounded-lg border border-gray-200 text-sm text-gray-700 focus:outline-none focus:border-gray-400 focus:ring-0 bg-gray-50" />
                </div>
            </div>
        </div>

        {{-- Baris 2: Dropdown filter --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Status pengiriman</label>
                <select name="status"
                        class="w-full h-9 px-3 rounded-lg border border-gray-200 text-sm text-gray-700 bg-gray-50 focus:outline-none focus:border-gray-400 focus:ring-0">
                    <option value="">Semua status</option>
                    @foreach(['pending','menunggu_fisik','menunggu_verifikasi','berhasil','gagal'] as $st)
                        <option value="{{ $st }}" {{ $status===$st ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_',' ', $st)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">PIC procurement</label>
                <select name="purchasing"
                        class="w-full h-9 px-3 rounded-lg border border-gray-200 text-sm text-gray-700 bg-gray-50 focus:outline-none focus:border-gray-400 focus:ring-0">
                    <option value="">Semua PIC</option>
                    @foreach($purchasingUsers as $u)
                        <option value="{{ $u->id }}" {{ (string)$purchasing===(string)$u->id ? 'selected' : '' }}>
                            {{ $u->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Pabrik (klien)</label>
                <select name="pabrik"
                        class="w-full h-9 px-3 rounded-lg border border-gray-200 text-sm text-gray-700 bg-gray-50 focus:outline-none focus:border-gray-400 focus:ring-0">
                    <option value="">Semua pabrik</option>
                    @foreach($pabrikList as $k)
                        <option value="{{ $k->id }}" {{ (string)$pabrik===(string)$k->id ? 'selected' : '' }}>
                            {{ $k->nama }}{{ $k->cabang ? ' - '.$k->cabang : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Supplier</label>
                <select name="supplier"
                        class="w-full h-9 px-3 rounded-lg border border-gray-200 text-sm text-gray-700 bg-gray-50 focus:outline-none focus:border-gray-400 focus:ring-0">
                    <option value="">Semua supplier</option>
                    @foreach($supplierList as $s)
                        <option value="{{ $s->id }}" {{ (string)$supplier===(string)$s->id ? 'selected' : '' }}>
                            {{ $s->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Baris 3: Search + tombol --}}
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Cari nama pabrik / no PO</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input type="text" name="search" value="{{ $search }}"
                           placeholder="Ketik nama pabrik atau no PO..."
                           class="w-full h-9 pl-9 pr-3 rounded-lg border border-gray-200 text-sm text-gray-700 bg-gray-50 focus:outline-none focus:border-gray-400 focus:ring-0" />
                </div>
            </div>

            <div class="flex items-center gap-2 pb-0.5">
                <button type="submit"
                        class="h-9 px-4 rounded-lg bg-gray-800 text-white text-sm font-medium hover:bg-gray-700 transition-colors">
                    Terapkan
                </button>
                <a href="{{ route('laporan.evaluasiProcurement') }}"
                   class="h-9 px-4 rounded-lg border border-gray-200 text-gray-600 text-sm hover:bg-gray-50 transition-colors inline-flex items-center">
                    Reset
                </a>
            </div>

            {{-- Tombol Export — submit ke form export terpisah via JS --}}
            <div class="pb-0.5">
                <button type="button" onclick="submitExport()"
                        class="h-9 px-4 rounded-lg border border-emerald-300 text-emerald-700 text-sm hover:bg-emerald-50 transition-colors inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 15V3m0 12-4-4m4 4 4-4M2 17l.621 2.485A2 2 0 0 0 4.561 21h14.878a2 2 0 0 0 1.94-1.515L22 17"/>
                    </svg>
                    Export Excel
                </button>
            </div>
        </div>

    </form>

    {{-- Active filter chips --}}
    @php
        $activeFilters = [];
        if ($status)    $activeFilters[] = ['label' => 'Status', 'value' => ucfirst(str_replace('_', ' ', $status)), 'param' => 'status'];
        if ($purchasing) {
            $picName = $purchasingUsers->find($purchasing)->nama ?? 'PIC';
            $activeFilters[] = ['label' => 'PIC', 'value' => $picName, 'param' => 'purchasing'];
        }
        if ($pabrik) {
            $pabrikName = $pabrikList->find($pabrik)->nama ?? 'Pabrik';
            $activeFilters[] = ['label' => 'Pabrik', 'value' => $pabrikName, 'param' => 'pabrik'];
        }
        if ($supplier) {
            $supplierName = $supplierList->find($supplier)->nama ?? 'Supplier';
            $activeFilters[] = ['label' => 'Supplier', 'value' => $supplierName, 'param' => 'supplier'];
        }
        if ($search) {
            $activeFilters[] = ['label' => 'Cari', 'value' => $search, 'param' => 'search'];
        }
    @endphp

    @if(count($activeFilters))
        <div class="flex flex-wrap gap-1.5 mt-4 pt-4 border-t border-gray-100">
            <span class="text-xs text-gray-400 self-center mr-1">Filter aktif:</span>
            @foreach($activeFilters as $af)
                <a href="{{ request()->fullUrlWithQuery([$af['param'] => '']) }}"
                   class="inline-flex items-center gap-1 h-6 px-2.5 rounded-full bg-gray-100 border border-gray-200 text-xs text-gray-600 hover:bg-gray-200 transition-colors">
                    <span class="text-gray-400">{{ $af['label'] }}:</span>
                    {{ $af['value'] }}
                    <svg class="w-2.5 h-2.5 text-gray-400 ml-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M18 6 6 18M6 6l12 12"/>
                    </svg>
                </a>
            @endforeach
        </div>
    @endif

</div>

{{-- ── Form Export (terpisah, di luar form filter) ────────────────────────── --}}
<form method="POST" action="{{ route('laporan.evaluasiProcurement.export') }}" id="export-form" style="display:none;">
    @csrf
    <input type="hidden" name="start_date" id="exp_start_date" value="{{ $startDate }}" />
    <input type="hidden" name="end_date"   id="exp_end_date"   value="{{ $endDate }}" />
    <input type="hidden" name="status"     id="exp_status"     value="{{ $status }}" />
    <input type="hidden" name="purchasing" id="exp_purchasing" value="{{ $purchasing }}" />
    <input type="hidden" name="pabrik"     id="exp_pabrik"     value="{{ $pabrik }}" />
    <input type="hidden" name="supplier"   id="exp_supplier"   value="{{ $supplier }}" />
    <input type="hidden" name="search"     id="exp_search"     value="{{ $search }}" />
</form>

{{-- ── Summary 3 Angka ─────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">Target Omset forecasting</p>
        <p class="text-2xl font-semibold text-gray-800">
            Rp {{ number_format((float)$omsetForecasting, 2, ',', '.') }}
        </p>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">Omset realisasi</p>
        <p class="text-2xl font-semibold text-gray-800">
            Rp {{ number_format((float)$omsetRealisasi, 2, ',', '.') }}
        </p>
    </div>

    <div class="bg-white rounded-xl border border-yellow-100 shadow-sm p-5">
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">Pengiriman tambahan</p>
        <p class="text-2xl font-semibold text-yellow-700">
            Rp {{ number_format((float)$omsetTambahan, 2, ',', '.') }}
        </p>
    </div>

</div>

{{-- ── Tabel Data ──────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-800">Data evaluasi procurement</h3>
        <span class="text-xs text-gray-400">{{ $forecastData->count() }} data</span>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 whitespace-nowrap">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 whitespace-nowrap">Hari</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 whitespace-nowrap">PIC</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 whitespace-nowrap">Supplier</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 whitespace-nowrap">Bahan baku</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 whitespace-nowrap">Klien</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 whitespace-nowrap">Qty forecast</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 whitespace-nowrap">Harga jual</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 whitespace-nowrap">Total forecast</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 whitespace-nowrap">Qty kirim</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 whitespace-nowrap">Total kirim</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 whitespace-nowrap">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($forecastData as $f)
                    @php
                        $displayTanggal = $f->display_tanggal
                            ? \Carbon\Carbon::parse($f->display_tanggal)->format('d/m/Y')
                            : 'N/A';
                        $displayHari = $f->display_tanggal
                            ? \Carbon\Carbon::parse($f->display_tanggal)->locale('id')->isoFormat('dddd')
                            : 'N/A';

                        $details     = $f->forecastDetails ?? collect();
                        $qtyForecast = $details->sum('qty_forecast');
                        $firstDetail = $details->first();

                        $supplierNama  = optional(optional(optional($firstDetail)->bahanBakuSupplier)->supplier)->nama ?? 'N/A';
                        $bahanBakuNama = optional(optional($firstDetail)->bahanBakuSupplier)->nama ?? 'N/A';

                        $klien       = optional(optional($f->purchaseOrder)->klien);
                        $klienNama   = $klien->nama   ?? 'N/A';
                        $klienCabang = $klien->cabang ?? 'N/A';

                        $hargaJual = (float) (optional(optional($firstDetail)->orderDetail)->harga_jual ?? 0);

                        $statusRealisasi = ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'];
                        $pStatus         = $f->pengiriman_status ?? null;
                        $isRealisasi     = in_array($pStatus, $statusRealisasi);

                        if ($isRealisasi) {
                            $qtyKirim = ($pStatus === 'berhasil' && ! is_null($f->invoice_qty))
                                ? (float) $f->invoice_qty
                                : (float) ($f->pengiriman_total_qty_kirim ?? 0);
                            $totalHargaKirim = ($pStatus === 'berhasil' && ! is_null($f->invoice_amount))
                                ? (float) $f->invoice_amount
                                : (float) ($f->pengiriman_total_harga_kirim ?? 0);
                        } else {
                            $qtyKirim        = null;
                            $totalHargaKirim = null;
                        }

                        if ($isRealisasi) {
                            $keterangan     = 'Done';
                            $keteranganType = 'done';
                        } elseif ($pStatus === 'gagal') {
                            $keterangan     = $f->pengiriman_catatan ?? null;
                            $keteranganType = 'gagal';
                        } else {
                            $keterangan     = null;
                            $keteranganType = 'kosong';
                        }

                        $isTambahan = trim((string) $f->catatan) === 'Tambahan';
                    @endphp

                    <tr class="{{ $isTambahan ? 'bg-yellow-50/60' : 'hover:bg-gray-50/60' }} transition-colors">

                        {{-- Tgl --}}
                        <td class="px-4 py-3 text-gray-700 whitespace-nowrap">
                            {{ $displayTanggal }}
                            @if($isTambahan)
                                <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-700 border border-yellow-200">
                                    +Tambahan
                                </span>
                            @endif
                        </td>

                        {{-- Hari --}}
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap text-xs">{{ $displayHari }}</td>

                        {{-- PIC --}}
                        <td class="px-4 py-3 text-gray-700 whitespace-nowrap">
                            {{ optional($f->purchasing)->nama ?? 'N/A' }}
                        </td>

                        {{-- Supplier --}}
                        <td class="px-4 py-3 text-gray-700">{{ $supplierNama }}</td>

                        {{-- Bahan Baku --}}
                        <td class="px-4 py-3 text-gray-700">{{ $bahanBakuNama }}</td>

                        {{-- Klien - Cabang --}}
                        <td class="px-4 py-3 text-gray-700 whitespace-nowrap">
                            {{ $klienNama }}
                            @if($klienCabang && $klienCabang !== 'N/A')
                                <span class="text-gray-400">· {{ $klienCabang }}</span>
                            @endif
                        </td>

                        {{-- Qty Forecast --}}
                        <td class="px-4 py-3 text-gray-700 text-right whitespace-nowrap tabular-nums">
                            {{ number_format((float)$qtyForecast, 2, ',', '.') }}
                        </td>

                        {{-- Harga Jual --}}
                        <td class="px-4 py-3 text-gray-700 text-right whitespace-nowrap tabular-nums">
                            Rp {{ number_format((float)$hargaJual, 2, ',', '.') }}
                        </td>

                        {{-- Total Harga Forecast --}}
                        <td class="px-4 py-3 text-gray-800 text-right whitespace-nowrap font-medium tabular-nums">
                            Rp {{ number_format((float)$f->total_harga_forecast, 2, ',', '.') }}
                        </td>

                        {{-- Qty Kirim --}}
                        <td class="px-4 py-3 text-right whitespace-nowrap tabular-nums">
                            @if(is_null($qtyKirim))
                                <span class="text-gray-300">—</span>
                            @else
                                <span class="text-gray-700">{{ number_format($qtyKirim, 2, ',', '.') }}</span>
                            @endif
                        </td>

                        {{-- Total Harga Kirim --}}
                        <td class="px-4 py-3 text-right whitespace-nowrap tabular-nums">
                            @if(is_null($totalHargaKirim))
                                <span class="text-gray-300">—</span>
                            @else
                                <span class="text-gray-800 font-medium">Rp {{ number_format($totalHargaKirim, 2, ',', '.') }}</span>
                            @endif
                        </td>

                        {{-- Keterangan --}}
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($keteranganType === 'done')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="m5 13 4 4L19 7"/></svg>
                                    Done
                                </span>
                            @elseif($keteranganType === 'gagal' && $keterangan)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-600 border border-red-200">
                                    {{ $keterangan }}
                                </span>
                            @else
                                <span class="text-gray-200">—</span>
                            @endif
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="px-4 py-12 text-center text-sm text-gray-400">
                            Tidak ada data untuk filter yang dipilih.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Script Export ───────────────────────────────────────────────────────── --}}
<script>
function submitExport() {
    const f = document.getElementById('filter-form');
    document.getElementById('exp_start_date').value = f.querySelector('[name=start_date]').value;
    document.getElementById('exp_end_date').value   = f.querySelector('[name=end_date]').value;
    document.getElementById('exp_status').value     = f.querySelector('[name=status]').value;
    document.getElementById('exp_purchasing').value = f.querySelector('[name=purchasing]').value;
    document.getElementById('exp_pabrik').value     = f.querySelector('[name=pabrik]').value;
    document.getElementById('exp_supplier').value   = f.querySelector('[name=supplier]').value;
    document.getElementById('exp_search').value     = f.querySelector('[name=search]').value;
    document.getElementById('export-form').submit();
}
</script>

@endsection