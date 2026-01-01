<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Trend PO 12 Bulan Terakhir</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #3B82F6;
        }
        .header h1 {
            font-size: 20px;
            color: #1E40AF;
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
            background: #EFF6FF;
            border: 2px solid #3B82F6;
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
            border-right: 1px solid #BFDBFE;
        }
        .summary-item:last-child {
            border-right: none;
        }
        .summary-label {
            font-size: 10px;
            color: #1E40AF;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .summary-value {
            font-size: 16px;
            color: #1E3A8A;
            font-weight: bold;
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
            padding: 10px 8px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            color: #374151;
            text-transform: uppercase;
            border-bottom: 2px solid #E5E7EB;
            letter-spacing: 0.5px;
        }
        td {
            padding: 10px 8px;
            font-size: 10px;
            border-bottom: 1px solid #F3F4F6;
        }
        tbody tr:hover {
            background: #F9FAFB;
        }
        tbody tr:last-child td {
            border-bottom: none;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        tfoot {
            background: #F3F4F6;
            font-weight: bold;
        }
        tfoot td {
            border-top: 2px solid #D1D5DB;
            padding: 12px 8px;
        }
        .grand-total {
            background: #DBEAFE;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
            border: 2px solid #3B82F6;
        }
        .grand-total-row {
            display: table;
            width: 100%;
        }
        .grand-total-label {
            display: table-cell;
            font-size: 14px;
            font-weight: bold;
            color: #1E40AF;
            text-transform: uppercase;
            vertical-align: middle;
        }
        .grand-total-value {
            display: table-cell;
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            color: #1E3A8A;
            vertical-align: middle;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #E5E7EB;
            text-align: center;
            font-size: 9px;
            color: #9CA3AF;
        }
        .highlight-row {
            background: #FEF3C7 !important;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>Laporan Trend PO 12 Bulan Terakhir</h1>
        <div class="subtitle">Rincian Jumlah dan Nilai Purchase Order per Bulan</div>
        <div class="meta">
            <strong>Dicetak:</strong> {{ $generatedAt }}
        </div>
    </div>

    {{-- Summary --}}
    <div class="summary-box">
        <div class="summary-item">
            <div class="summary-label">Periode</div>
            <div class="summary-value">12 Bulan</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total PO</div>
            <div class="summary-value">{{ number_format($totalPO, 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Nilai</div>
            <div class="summary-value">Rp {{ number_format($totalNilai, 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Rata-rata per PO</div>
            <div class="summary-value">Rp {{ number_format($avgPerPO, 0, ',', '.') }}</div>
        </div>
    </div>

    {{-- Table --}}
    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 20%">Bulan</th>
                <th style="width: 15%" class="text-center">Jumlah PO</th>
                <th style="width: 30%" class="text-right">Total Nilai</th>
                <th style="width: 30%" class="text-right">Rata-rata per PO</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $no = 1;
                $maxPO = max(array_column($poTrendByMonth, 'total_po'));
                $maxNilai = max(array_column($poTrendByMonth, 'total_nilai'));
            @endphp
            @foreach($poTrendByMonth as $trend)
                @php
                    $avgPerPOMonth = $trend['total_po'] > 0 ? $trend['total_nilai'] / $trend['total_po'] : 0;
                    $isHighlighted = $trend['total_po'] == $maxPO || $trend['total_nilai'] == $maxNilai;
                @endphp
                <tr class="{{ $isHighlighted ? 'highlight-row' : '' }}">
                    <td class="text-center">{{ $no++ }}</td>
                    <td class="font-bold">{{ $trend['month'] }}</td>
                    <td class="text-center font-bold">{{ number_format($trend['total_po'], 0, ',', '.') }}</td>
                    <td class="text-right font-bold">Rp {{ number_format($trend['total_nilai'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($avgPerPOMonth, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-right">TOTAL</td>
                <td class="text-center">{{ number_format($totalPO, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totalNilai, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($avgPerPO, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Analysis Summary --}}
    <div style="background: #F3F4F6; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
        <h3 style="font-size: 12px; font-weight: bold; margin-bottom: 10px; color: #374151;">Ringkasan Analisis:</h3>
        <ul style="list-style: none; padding-left: 0; font-size: 10px; color: #6B7280;">
            @php
                $maxPOMonth = collect($poTrendByMonth)->firstWhere('total_po', $maxPO);
                $maxNilaiMonth = collect($poTrendByMonth)->firstWhere('total_nilai', $maxNilai);
                $minPO = min(array_column($poTrendByMonth, 'total_po'));
                $minPOMonth = collect($poTrendByMonth)->firstWhere('total_po', $minPO);
            @endphp
            <li style="margin-bottom: 8px;">
                <strong>✓ Bulan dengan PO Terbanyak:</strong> {{ $maxPOMonth['month'] }} ({{ number_format($maxPOMonth['total_po'], 0, ',', '.') }} PO)
            </li>
            <li style="margin-bottom: 8px;">
                <strong>✓ Bulan dengan Nilai Tertinggi:</strong> {{ $maxNilaiMonth['month'] }} (Rp {{ number_format($maxNilaiMonth['total_nilai'], 0, ',', '.') }})
            </li>
            <li style="margin-bottom: 8px;">
                <strong>✓ Bulan dengan PO Terendah:</strong> {{ $minPOMonth['month'] }} ({{ number_format($minPOMonth['total_po'], 0, ',', '.') }} PO)
            </li>
            <li>
                <strong>✓ Rata-rata PO per Bulan:</strong> {{ number_format($totalPO / 12, 1, ',', '.') }} PO
            </li>
        </ul>
    </div>

    {{-- Grand Total --}}
    <div class="grand-total">
        <div class="grand-total-row">
            <div class="grand-total-label">GRAND TOTAL (12 BULAN)</div>
            <div class="grand-total-value">Rp {{ number_format($totalNilai, 0, ',', '.') }}</div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        Dokumen ini digenerate secara otomatis oleh sistem<br>
        PT Kamil Maju Persada © {{ date('Y') }}
    </div>
</body>
</html>
