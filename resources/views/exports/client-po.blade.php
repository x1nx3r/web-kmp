<table>
    {{-- Title --}}
    <tr>
        <td colspan="12" style="text-align: center; font-weight: bold; font-size: 16px;">
            LAPORAN PURCHASE ORDER BERDASARKAN KLIEN
        </td>
    </tr>
    <tr>
        <td colspan="12" style="text-align: center;">
            @if($filterInfo)
                Filter: {{ $filterInfo }}
            @else
                Semua Data
            @endif
        </td>
    </tr>
    <tr><td colspan="12"></td></tr>
    
    {{-- Summary --}}
    <tr style="background-color: #E5E7EB;">
        <td colspan="2"><strong>Total Klien:</strong> {{ $totalKlien }}</td>
        <td colspan="2"><strong>Total PO:</strong> {{ number_format($totalPO, 0, ',', '.') }}</td>
        <td colspan="2"><strong>Total Nilai:</strong> Rp {{ number_format($totalNilai, 0, ',', '.') }}</td>
        <td colspan="2"><strong>Outstanding:</strong> Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</td>
        <td colspan="4"><strong>Rata-rata/PO:</strong> Rp {{ number_format($avgPerPO, 0, ',', '.') }}</td>
    </tr>
    <tr><td colspan="12"></td></tr>
    <tr><td colspan="12"></td></tr>
    
    {{-- Column Headers --}}
    <tr style="background-color: #2563EB; color: white;">
        <th style="text-align: center;">No</th>
        <th>Klien</th>
        <th>Cabang</th>
        <th style="text-align: center;">Total PO</th>
        <th style="text-align: right;">Total Nilai</th>
        <th style="text-align: right;">Outstanding</th>
        <th style="text-align: right;">Avg/PO</th>
        <th style="text-align: center;">Kontribusi</th>
        <th style="text-align: center;">Dikonfirmasi</th>
        <th style="text-align: center;">Diproses</th>
        <th style="text-align: center;">Selesai</th>
        <th style="text-align: center;">Last Order</th>
    </tr>
    
    {{-- Data Rows --}}
    @php $no = 1; @endphp
    @foreach($poByClient as $client)
        <tr>
            <td style="text-align: center;">{{ $no++ }}</td>
            <td style="font-weight: bold;">{{ $client->klien_nama }}</td>
            <td>{{ $client->cabang ?? '-' }}</td>
            <td style="text-align: center;">{{ $client->total_po }}</td>
            <td style="text-align: right;">{{ number_format($client->total_nilai, 0, ',', '.') }}</td>
            <td style="text-align: right;">{{ number_format($client->outstanding_amount, 0, ',', '.') }}</td>
            <td style="text-align: right;">{{ number_format($client->avg_nilai_per_po, 0, ',', '.') }}</td>
            <td style="text-align: center;">{{ number_format($client->percentage, 1, ',', '.') }}%</td>
            <td style="text-align: center;">{{ $client->status_dikonfirmasi }}</td>
            <td style="text-align: center;">{{ $client->status_diproses }}</td>
            <td style="text-align: center;">{{ $client->status_selesai }}</td>
            <td style="text-align: center;">{{ $client->last_order_date ? \Carbon\Carbon::parse($client->last_order_date)->format('d/m/Y') : '-' }}</td>
        </tr>
    @endforeach
    
    {{-- Footer Total --}}
    <tr style="background-color: #F3F4F6; font-weight: bold;">
        <td colspan="3" style="text-align: right;">TOTAL:</td>
        <td style="text-align: center;">{{ number_format($totalPO, 0, ',', '.') }}</td>
        <td style="text-align: right;">{{ number_format($totalNilai, 0, ',', '.') }}</td>
        <td style="text-align: right;">{{ number_format($totalOutstanding, 0, ',', '.') }}</td>
        <td style="text-align: right;">{{ number_format($avgPerPO, 0, ',', '.') }}</td>
        <td style="text-align: center;">100%</td>
        <td style="text-align: center;">{{ $poByClient->sum('status_dikonfirmasi') }}</td>
        <td style="text-align: center;">{{ $poByClient->sum('status_diproses') }}</td>
        <td style="text-align: center;">{{ $poByClient->sum('status_selesai') }}</td>
        <td></td>
    </tr>
</table>

{{-- Add a second sheet with detailed PO list --}}
<table style="margin-top: 30px;">
    <tr><td colspan="9"></td></tr>
    <tr><td colspan="9"></td></tr>
    <tr>
        <td colspan="9" style="text-align: center; font-weight: bold; font-size: 14px;">
            DETAIL PO PER KLIEN
        </td>
    </tr>
    <tr><td colspan="9"></td></tr>
    
    @foreach($poByClient as $client)
        {{-- Client Header --}}
        <tr style="background-color: #2563EB; color: white;">
            <td colspan="9" style="font-weight: bold;">
                {{ $client->klien_nama }}
                @if($client->cabang) ({{ $client->cabang }}) @endif
                - {{ $client->total_po }} PO - Rp {{ number_format($client->total_nilai, 0, ',', '.') }}
            </td>
        </tr>
        
        {{-- PO Details Header --}}
        <tr style="background-color: #E5E7EB;">
            <th>No. PO</th>
            <th>Tanggal</th>
            <th>Material</th>
            <th style="text-align: center;">Status</th>
            <th style="text-align: center;">Prioritas</th>
            <th style="text-align: right;">Qty (kg)</th>
            <th style="text-align: right;">Nilai (Rp)</th>
        </tr>
        
        {{-- PO Details --}}
        @if(isset($poDetailsByClient[$client->klien_id]))
            @foreach($poDetailsByClient[$client->klien_id] as $po)
                <tr>
                    <td style="color: #2563EB; font-weight: bold;">{{ $po['po_number'] }}</td>
                    <td>{{ $po['tanggal_order'] }}</td>
                    <td>{{ Str::limit($po['materials'], 60) }}</td>
                    <td style="text-align: center;">{{ ucfirst($po['status']) }}</td>
                    <td style="text-align: center;">{{ ucfirst($po['priority'] ?? '-') }}</td>
                    <td style="text-align: right;">{{ number_format($po['total_qty'], 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($po['total_amount'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        @endif
        
        <tr><td colspan="9"></td></tr>
    @endforeach
</table>
