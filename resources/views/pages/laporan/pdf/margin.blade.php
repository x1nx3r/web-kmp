<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Analisis Margin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 9px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #8B5CF6;
        }
        .header h1 {
            font-size: 20px;
            color: #6D28D9;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header .subtitle {
            font-size: 12px;
            color: #6B7280;
            margin-bottom: 8px;
        }
        .header .meta {
            font-size: 10px;
            color: #9CA3AF;
        }
        .summary-box {
            background: #F5F3FF;
            border: 2px solid #8B5CF6;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            display: table;
            width: 100%;
        }
        .summary-item {
            display: table-cell;
            text-align: center;
            padding: 8px;
            border-right: 1px solid #DDD6FE;
        }
        .summary-item:last-child {
            border-right: none;
        }
        .summary-label {
            font-size: 9px;
            color: #6D28D9;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .summary-value {
            font-size: 14px;
            color: #5B21B6;
            font-weight: bold;
        }
        .summary-value.positive {
            color: #047857;
        }
        .summary-value.negative {
            color: #DC2626;
        }
        .filter-box {
            background: #FEF3C7;
            border: 1px solid #FCD34D;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 15px;
            font-size: 9px;
        }
        .filter-box strong {
            color: #92400E;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #E5E7EB;
        }
        thead {
            background: #F9FAFB;
        }
        th {
            padding: 7px 4px;
            text-align: left;
            font-weight: bold;
            color: #374151;
            border: 1px solid #E5E7EB;
            font-size: 8px;
            text-transform: uppercase;
        }
        th.text-right {
            text-align: right;
        }
        td {
            padding: 6px 4px;
            border: 1px solid #E5E7EB;
            font-size: 8px;
        }
        td.text-right {
            text-align: right;
        }
        tbody tr:nth-child(even) {
            background: #F9FAFB;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 7px;
            font-weight: bold;
        }
        .badge-success {
            background: #D1FAE5;
            color: #065F46;
        }
        .badge-danger {
            background: #FEE2E2;
            color: #991B1B;
        }
        .text-green {
            color: #047857;
            font-weight: bold;
        }
        .text-red {
            color: #DC2626;
            font-weight: bold;
        }
        .text-blue {
            color: #2563EB;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #E5E7EB;
            text-align: center;
            font-size: 8px;
            color: #6B7280;
        }
        tfoot {
            background: #F3F4F6;
            font-weight: bold;
        }
        tfoot td {
            padding: 8px 4px;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Analisis Margin</h1>
        <div class="subtitle">Periode: {{ $startDate }} - {{ $endDate }}</div>
        @if($filterDesc)
            <div class="meta">Filter: {{ $filterDesc }}</div>
        @endif
        <div class="meta">Dicetak pada: {{ $generatedAt }}</div>
    </div>

    {{-- Summary Box --}}
    <div class="summary-box">
        <div class="summary-item">
            <div class="summary-label">Total Transaksi</div>
            <div class="summary-value">{{ number_format(count($marginData)) }}</div>
            <div style="font-size: 7px; color: #6B7280; margin-top: 2px;">
                <span style="color: #047857;">{{ $profitCount }} Profit</span> • 
                <span style="color: #DC2626;">{{ $lossCount }} Loss</span>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Harga Beli</div>
            <div class="summary-value" style="color: #DC2626;">
                Rp {{ number_format($totalHargaBeli, 2, ',', '.') }}
            </div>
            <div style="font-size: 7px; color: #6B7280; margin-top: 2px;">
                {{ number_format($totalQty, 2, ',', '.') }} kg
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Harga Jual</div>
            <div class="summary-value" style="color: #047857;">
                Rp {{ number_format($totalHargaJual, 2, ',', '.') }}
            </div>
            <div style="font-size: 7px; color: #6B7280; margin-top: 2px;">
                {{ number_format($totalQty, 2, ',', '.') }} kg
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Gross Margin</div>
            <div class="summary-value {{ $totalMargin >= 0 ? 'positive' : 'negative' }}">
                {{ $totalMargin >= 0 ? '+' : '' }}{{ number_format($grossMarginPercentage, 2) }}%
            </div>
            <div style="font-size: 7px; margin-top: 2px;" class="{{ $totalMargin >= 0 ? 'text-green' : 'text-red' }}">
                {{ $totalMargin >= 0 ? '+' : '' }}Rp {{ number_format(abs($totalMargin), 2, ',', '.') }}
            </div>
        </div>
    </div>

    {{-- Detail Table --}}
    @if(count($marginData) > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 3%;">No</th>
                    <th style="width: 7%;">Tanggal</th>
                    <th style="width: 9%;">No Kirim</th>
                    <th style="width: 10%;">PIC</th>
                    <th style="width: 12%;">Klien</th>
                    <th style="width: 11%;">Supplier</th>
                    <th style="width: 11%;">Bahan Baku</th>
                    <th class="text-right" style="width: 6%;">Qty</th>
                    <th class="text-right" style="width: 8%;">H.Beli/kg</th>
                    <th class="text-right" style="width: 8%;">H.Jual/kg</th>
                    <th class="text-right" style="width: 9%;">Margin</th>
                    <th class="text-right" style="width: 6%;">%</th>
                </tr>
            </thead>
            <tbody>
                @foreach($marginData as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item['tanggal_kirim'] }}</td>
                        <td>{{ $item['no_pengiriman'] }}</td>
                        <td>{{ $item['pic_purchasing'] }}</td>
                        <td>{{ $item['klien'] }}</td>
                        <td>{{ $item['supplier'] }}</td>
                        <td>{{ $item['bahan_baku'] }}</td>
                        <td class="text-right">{{ number_format($item['qty'], 2, ',', '.') }}</td>
                        <td class="text-right" style="color: #DC2626;">{{ number_format($item['harga_beli_per_kg'], 2, ',', '.') }}</td>
                        <td class="text-right" style="color: #047857;">{{ number_format($item['harga_jual_per_kg'], 2, ',', '.') }}</td>
                        <td class="text-right">
                            <span class="{{ $item['margin'] >= 0 ? 'text-blue' : 'text-red' }}">
                                {{ $item['margin'] >= 0 ? '+' : '' }}{{ number_format($item['margin'], 2, ',', '.') }}
                            </span>
                        </td>
                        <td class="text-right">
                            <span class="badge {{ $item['margin'] >= 0 ? 'badge-success' : 'badge-danger' }}">
                                {{ $item['margin'] >= 0 ? '+' : '' }}{{ number_format($item['margin_percentage'], 1) }}%
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7" style="text-align: right;">TOTAL:</td>
                    <td class="text-right">{{ number_format($totalQty, 2, ',', '.') }}</td>
                    <td colspan="2"></td>
                    <td class="text-right">
                        <span class="{{ $totalMargin >= 0 ? 'text-blue' : 'text-red' }}">
                            {{ $totalMargin >= 0 ? '+' : '' }}Rp {{ number_format(abs($totalMargin), 2, ',', '.') }}
                        </span>
                    </td>
                    <td class="text-right">
                        <span class="{{ $totalMargin >= 0 ? 'text-green' : 'text-red' }}">
                            {{ $totalMargin >= 0 ? '+' : '' }}{{ number_format($grossMarginPercentage, 2) }}%
                        </span>
                    </td>
                </tr>
            </tfoot>
        </table>
    @else
        <div style="text-align: center; padding: 40px 0; color: #9CA3AF;">
            <p style="font-size: 14px;">Tidak ada data margin untuk periode dan filter yang dipilih</p>
        </div>
    @endif

    <div class="footer">
        <p><strong>KMP - Sistem Manajemen</strong></p>
        <p>Laporan ini digenerate secara otomatis oleh sistem</p>
    </div>
</body>
</html>
