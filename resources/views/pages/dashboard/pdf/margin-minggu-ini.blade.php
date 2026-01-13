<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Margin Minggu Ini</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #10B981;
        }
        .header h1 {
            font-size: 20px;
            color: #059669;
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
            background: #ECFDF5;
            border: 2px solid #10B981;
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
            border-right: 1px solid #A7F3D0;
        }
        .summary-item:last-child {
            border-right: none;
        }
        .summary-label {
            font-size: 10px;
            color: #059669;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .summary-value {
            font-size: 16px;
            color: #047857;
            font-weight: bold;
        }
        .summary-value.positive {
            color: #047857;
        }
        .summary-value.negative {
            color: #DC2626;
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
            padding: 8px 5px;
            text-align: left;
            font-weight: bold;
            color: #374151;
            border: 1px solid #E5E7EB;
            font-size: 9px;
            text-transform: uppercase;
        }
        th.text-right {
            text-align: right;
        }
        td {
            padding: 7px 5px;
            border: 1px solid #E5E7EB;
            font-size: 9px;
        }
        td.text-right {
            text-align: right;
        }
        tbody tr:nth-child(even) {
            background: #F9FAFB;
        }
        tbody tr:hover {
            background: #F3F4F6;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-success {
            background: #D1FAE5;
            color: #065F46;
        }
        .badge-warning {
            background: #FEF3C7;
            color: #92400E;
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
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #E5E7EB;
            text-align: center;
            font-size: 9px;
            color: #6B7280;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Margin Minggu {{ $currentWeek }}</h1>
        <div class="subtitle">Periode: {{ $startDate }} - {{ $endDate }}</div>
        <div class="meta">Dicetak pada: {{ $generatedAt }}</div>
    </div>

    {{-- Summary Box --}}
    <div class="summary-box">
        <div class="summary-item">
            <div class="summary-label">Total Margin</div>
            <div class="summary-value {{ $totalMargin >= 0 ? 'positive' : 'negative' }}">
                Rp {{ number_format($totalMargin, 2, ',', '.') }}
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Harga Beli</div>
            <div class="summary-value">
                Rp {{ number_format($totalHargaBeli, 2, ',', '.') }}
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Harga Jual</div>
            <div class="summary-value">
                Rp {{ number_format($totalHargaJual, 2, ',', '.') }}
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Gross Margin %</div>
            <div class="summary-value {{ $grossMargin >= 0 ? 'positive' : 'negative' }}">
                {{ number_format($grossMargin, 2, ',', '.') }}%
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Jumlah Pengiriman</div>
            <div class="summary-value">
                {{ count($marginData) }}
            </div>
        </div>
    </div>

    {{-- Detail Table --}}
    @if(count($marginData) > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 3%;">No</th>
                    <th style="width: 8%;">Tgl Kirim</th>
                    <th style="width: 12%;">PIC</th>
                    <th style="width: 12%;">Pabrik</th>
                    <th style="width: 12%;">Supplier</th>
                    <th style="width: 13%;">Bahan Baku</th>
                    <th class="text-right" style="width: 7%;">Qty</th>
                    <th class="text-right" style="width: 9%;">H. Beli/Kg</th>
                    <th class="text-right" style="width: 9%;">H. Jual/Kg</th>
                    <th class="text-right" style="width: 10%;">Margin</th>
                    <th class="text-right" style="width: 5%;">%</th>
                </tr>
            </thead>
            <tbody>
                @foreach($marginData as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($item['tanggal_kirim'])->format('d/m/Y') }}</td>
                        <td>{{ $item['pic_purchasing'] }}</td>
                        <td>{{ $item['klien'] }}</td>
                        <td>{{ $item['supplier'] }}</td>
                        <td>{{ $item['bahan_baku'] }}</td>
                        <td class="text-right">{{ number_format($item['qty_kirim'], 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($item['harga_beli_per_kg'], 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($item['harga_jual_per_kg'], 2, ',', '.') }}</td>
                        <td class="text-right">
                            <span class="{{ $item['margin'] >= 0 ? 'text-green' : 'text-red' }}">
                                Rp {{ number_format($item['margin'], 2, ',', '.') }}
                            </span>
                        </td>
                        <td class="text-right">
                            @if($item['margin_percentage'] >= 20)
                                <span class="badge badge-success">{{ number_format($item['margin_percentage'], 2, ',', '.') }}%</span>
                            @elseif($item['margin_percentage'] >= 10)
                                <span class="badge badge-warning">{{ number_format($item['margin_percentage'], 2, ',', '.') }}%</span>
                            @else
                                <span class="badge badge-danger">{{ number_format($item['margin_percentage'], 2, ',', '.') }}%</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: #F3F4F6; font-weight: bold;">
                    <td colspan="6" style="text-align: right; padding-right: 10px;">TOTAL:</td>
                    <td class="text-right">{{ number_format(array_sum(array_column($marginData, 'qty_kirim')), 2, ',', '.') }}</td>
                    <td colspan="2"></td>
                    <td class="text-right">
                        <span class="{{ $totalMargin >= 0 ? 'text-green' : 'text-red' }}">
                            Rp {{ number_format($totalMargin, 2, ',', '.') }}
                        </span>
                    </td>
                    <td class="text-right">
                        <span class="{{ $grossMargin >= 0 ? 'text-green' : 'text-red' }}">
                            {{ number_format($grossMargin, 2, ',', '.') }}%
                        </span>
                    </td>
                </tr>
            </tfoot>
        </table>
    @else
        <div style="text-align: center; padding: 40px 0; color: #9CA3AF;">
            <p style="font-size: 14px;">Tidak ada data margin untuk minggu ini</p>
        </div>
    @endif

    {{-- Gross Margin Bulan Ini Section --}}
    <div style="margin-top: 30px; padding: 15px; background: linear-gradient(135deg, #ECFDF5 0%, #DBEAFE 100%); border: 2px solid #10B981; border-radius: 8px;">
        <div style="text-align: center;">
            <h3 style="font-size: 14px; color: #047857; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px;">
                Prosentase Gross Profit Bulan {{ $currentMonth }}
            </h3>
            <div style="display: inline-block; background: white; padding: 15px 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-top: 10px;">
                <div style="font-size: 32px; font-weight: bold; color: {{ $grossMarginBulanIni >= 0 ? '#047857' : '#DC2626' }}; margin-bottom: 5px;">
                    {{ $grossMarginBulanIni >= 0 ? '+' : '' }}{{ number_format($grossMarginBulanIni, 2, ',', '.') }}%
                </div>
                <div style="font-size: 11px; color: #6B7280; margin-bottom: 5px;">
                    Total Margin: <span style="font-weight: bold; color: {{ $totalMarginBulanIni >= 0 ? '#047857' : '#DC2626' }}">
                        {{ $totalMarginBulanIni >= 0 ? '+' : '' }}Rp {{ number_format(abs($totalMarginBulanIni), 2, ',', '.') }}
                    </span>
                </div>
                
            </div>
        </div>
    </div>

    <div class="footer">
        <p><strong>KMP - Sistem Manajemen</strong></p>
        <p>Laporan ini digenerate secara otomatis oleh sistem</p>
    </div>
</body>
</html>
