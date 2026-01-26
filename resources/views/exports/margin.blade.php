<table>
    {{-- Title & Info Section --}}
    <thead>
        <tr>
            <th colspan="15" style="text-align: center; font-weight: bold; font-size: 16px;">
                LAPORAN ANALISIS MARGIN
            </th>
        </tr>
        <tr>
            <th colspan="15" style="text-align: center; font-size: 11px;">
                PT. Kamil Maju Persada
            </th>
        </tr>
        <tr><td colspan="15"></td></tr>
        
        {{-- Filter Info --}}
        @if(!empty($filters['start_date']) || !empty($filters['end_date']))
        <tr>
            <td colspan="2" style="font-weight: bold;">Periode:</td>
            <td colspan="13">
                @if(!empty($filters['start_date']) && !empty($filters['end_date']))
                    {{ \Carbon\Carbon::parse($filters['start_date'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($filters['end_date'])->format('d/m/Y') }}
                @elseif(!empty($filters['start_date']))
                    Dari {{ \Carbon\Carbon::parse($filters['start_date'])->format('d/m/Y') }}
                @else
                    Sampai {{ \Carbon\Carbon::parse($filters['end_date'])->format('d/m/Y') }}
                @endif
            </td>
        </tr>
        @endif
        
        @if(!empty($filters['pic_purchasing_name']))
        <tr>
            <td colspan="2" style="font-weight: bold;">PIC Procurement:</td>
            <td colspan="13">{{ $filters['pic_purchasing_name'] }}</td>
        </tr>
        @endif
        
        @if(!empty($filters['pic_marketing_name']))
        <tr>
            <td colspan="2" style="font-weight: bold;">PIC Marketing:</td>
            <td colspan="13">{{ $filters['pic_marketing_name'] }}</td>
        </tr>
        @endif
        
        @if(!empty($filters['klien_name']))
        <tr>
            <td colspan="2" style="font-weight: bold;">Klien:</td>
            <td colspan="13">{{ $filters['klien_name'] }}</td>
        </tr>
        @endif
        
        @if(!empty($filters['supplier_name']))
        <tr>
            <td colspan="2" style="font-weight: bold;">Supplier:</td>
            <td colspan="13">{{ $filters['supplier_name'] }}</td>
        </tr>
        @endif
        
        @if(!empty($filters['bahan_baku_name']))
        <tr>
            <td colspan="2" style="font-weight: bold;">Bahan Baku:</td>
            <td colspan="13">{{ $filters['bahan_baku_name'] }}</td>
        </tr>
        @endif
        
        <tr><td colspan="15"></td></tr>
        
        {{-- Summary Section --}}
        <tr style="background-color: #E5E7EB;">
            <td colspan="3" style="font-weight: bold;">Total Transaksi</td>
            <td colspan="3" style="font-weight: bold;">Total Harga Beli</td>
            <td colspan="3" style="font-weight: bold;">Total Harga Jual</td>
            <td colspan="3" style="font-weight: bold;">Gross Margin</td>
            <td colspan="3" style="font-weight: bold;">Status</td>
        </tr>
        <tr style="background-color: #F9FAFB;">
            <td colspan="3">{{ count($marginData) }} transaksi</td>
            <td colspan="3">Rp {{ number_format($totalHargaBeli, 2, ',', '.') }}</td>
            <td colspan="3">Rp {{ number_format($totalHargaJual, 2, ',', '.') }}</td>
            <td colspan="3">{{ $totalMargin < 0 ? '-' : '' }}{{ number_format(abs($grossMarginPercentage), 2, ',', '.') }}%</td>
            <td colspan="3">{{ $profitCount }} Profit | {{ $lossCount }} Loss</td>
        </tr>
        
        <tr><td colspan="15"></td></tr>
        
        {{-- Column Headers --}}
        <tr style="background-color: #4F46E5; color: #FFFFFF; font-weight: bold;">
            <td style="text-align: center;">No</td>
            <td style="text-align: center;">Tanggal</td>
            <td style="text-align: center;">No Pengiriman</td>
            <td style="text-align: center;">PIC Procurement</td>
            <td style="text-align: center;">PIC Marketing</td>
            <td style="text-align: center;">Klien</td>
            <td style="text-align: center;">Supplier</td>
            <td style="text-align: center;">Bahan Baku</td>
            <td style="text-align: center;">Qty (kg)</td>
            <td style="text-align: center;">Harga Beli/kg</td>
            <td style="text-align: center;">Total Beli</td>
            <td style="text-align: center;">Harga Jual/kg</td>
            <td style="text-align: center;">Total Jual</td>
            <td style="text-align: center;">Margin (Rp)</td>
            <td style="text-align: center;">Margin (%)</td>
        </tr>
    </thead>
    
    <tbody>
        @forelse($marginData as $index => $item)
        <tr>
            <td style="text-align: center;">{{ $index + 1 }}</td>
            <td>{{ $item['tanggal_kirim'] }}</td>
            <td>{{ $item['no_pengiriman'] }}{{ $item['has_refraksi'] ? ' (*)' : '' }}</td>
            <td>{{ $item['pic_purchasing'] }}</td>
            <td>{{ $item['pic_marketing'] }}</td>
            <td>{{ $item['klien'] }}</td>
            <td>{{ $item['supplier'] }}</td>
            <td>{{ $item['bahan_baku'] }}</td>
            <td style="text-align: right;">{{ number_format($item['qty'], 2, ',', '.') }}</td>
            <td style="text-align: right;">{{ number_format($item['harga_beli_per_kg'], 2, ',', '.') }}</td>
            <td style="text-align: right;">{{ number_format($item['harga_beli_total'], 2, ',', '.') }}</td>
            <td style="text-align: right;">{{ $item['harga_jual_per_kg'] > 0 ? number_format($item['harga_jual_per_kg'], 2, ',', '.') : '-' }}</td>
            <td style="text-align: right;">{{ $item['harga_jual_total'] > 0 ? number_format($item['harga_jual_total'], 2, ',', '.') : '-' }}</td>
            <td style="text-align: right;">{{ $item['margin'] < 0 ? '-' : '' }}{{ number_format(abs($item['margin']), 2, ',', '.') }}</td>
            <td style="text-align: right;">{{ $item['margin'] < 0 ? '-' : '' }}{{ number_format(abs($item['margin_percentage']), 2, ',', '.') }}%</td>
        </tr>
        @empty
        <tr>
            <td colspan="15" style="text-align: center; padding: 20px;">Tidak ada data</td>
        </tr>
        @endforelse
    </tbody>
    
    @if(count($marginData) > 0)
    <tfoot>
        <tr style="background-color: #F3F4F6; font-weight: bold;">
            <td colspan="8" style="text-align: right;">TOTAL:</td>
            <td style="text-align: right;">{{ number_format($totalQty, 2, ',', '.') }}</td>
            <td style="text-align: right;">-</td>
            <td style="text-align: right;">{{ number_format($totalHargaBeli, 2, ',', '.') }}</td>
            <td style="text-align: right;">-</td>
            <td style="text-align: right;">{{ number_format($totalHargaJual, 2, ',', '.') }}</td>
            <td style="text-align: right;">{{ $totalMargin < 0 ? '-' : '' }}{{ number_format(abs($totalMargin), 2, ',', '.') }}</td>
            <td style="text-align: right;">{{ $totalMargin < 0 ? '-' : '' }}{{ number_format(abs($grossMarginPercentage), 2, ',', '.') }}%</td>
        </tr>
    </tfoot>
    @endif
</table>
