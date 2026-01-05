<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportTitle }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #2563eb;
        }
        
        .header h1 {
            font-size: 18px;
            color: #1e40af;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .header .subtitle {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 3px;
        }
        
        .header .period {
            font-size: 11px;
            color: #374151;
            font-weight: 600;
        }
        
        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        
        .summary-card {
            display: table-cell;
            width: 25%;
            padding: 12px;
            text-align: center;
            border: 2px solid #e5e7eb;
            background-color: #f9fafb;
        }
        
        .summary-card.normal {
            border-color: #10b981;
            background-color: #d1fae5;
        }
        
        .summary-card.bongkar {
            border-color: #f59e0b;
            background-color: #fef3c7;
        }
        
        .summary-card.gagal {
            border-color: #ef4444;
            background-color: #fee2e2;
        }
        
        .summary-card.total {
            border-color: #8b5cf6;
            background-color: #ede9fe;
        }
        
        .summary-card .label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .summary-card.normal .label { color: #065f46; }
        .summary-card.bongkar .label { color: #92400e; }
        .summary-card.gagal .label { color: #991b1b; }
        .summary-card.total .label { color: #5b21b6; }
        
        .summary-card .value {
            font-size: 22px;
            font-weight: bold;
            color: #111827;
        }
        
        .summary-card.normal .value { color: #059669; }
        .summary-card.bongkar .value { color: #d97706; }
        .summary-card.gagal .value { color: #dc2626; }
        .summary-card.total .value { color: #7c3aed; }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .data-table thead {
            background-color: #2563eb;
            color: white;
        }
        
        .data-table th {
            padding: 10px 8px;
            text-align: left;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            border: 1px solid #1e40af;
            color: white;
            background-color: #2563eb;
        }
        
        .data-table th.text-right {
            text-align: right;
        }
        
        .data-table th.text-center {
            text-align: center;
        }
        
        .data-table tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }
        
        .data-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .data-table tbody tr:hover {
            background-color: #eff6ff;
        }
        
        .data-table td {
            padding: 8px;
            font-size: 9px;
            color: #374151;
            border-left: 1px solid #e5e7eb;
            border-right: 1px solid #e5e7eb;
        }
        
        .data-table td:first-child {
            font-weight: 600;
            color: #111827;
        }
        
        .data-table td.text-right {
            text-align: right;
            font-weight: 600;
        }
        
        .data-table td.text-center {
            text-align: center;
        }
        
        .data-table td.po-number {
            color: #4f46e5;
            font-weight: 600;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: 700;
            border: 1px solid;
        }
        
        .badge.normal {
            background-color: #d1fae5;
            color: #065f46;
            border-color: #10b981;
        }
        
        .badge.bongkar {
            background-color: #fef3c7;
            color: #92400e;
            border-color: #f59e0b;
        }
        
        .badge.gagal {
            background-color: #fee2e2;
            color: #991b1b;
            border-color: #ef4444;
        }
        
        .footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 8px;
            color: #6b7280;
        }
        
        .footer .generated {
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .footer .company {
            color: #1e40af;
            font-weight: 700;
        }
        
        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: #9ca3af;
            font-size: 12px;
        }
        
        /* Page break control */
        .page-break {
            page-break-after: always;
        }
        
        /* Prevent page break inside table rows */
        .data-table tbody tr {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ $reportTitle }}</h1>
        <div class="subtitle">Laporan Detail Status Pengiriman</div>
        <div class="period">Periode: {{ $reportPeriod }}</div>
    </div>
    
    <!-- Summary Cards -->
    <table class="summary-cards">
        <tr>
            <td class="summary-card normal">
                <div class="label">Normal (&gt;70%)</div>
                <div class="value">{{ $summary['normal'] }}</div>
            </td>
            <td class="summary-card bongkar">
                <div class="label">Bongkar Sebagian</div>
                <div class="value">{{ $summary['bongkar'] }}</div>
            </td>
            <td class="summary-card gagal">
                <div class="label">Ditolak</div>
                <div class="value">{{ $summary['gagal'] }}</div>
            </td>
            <td class="summary-card total">
                <div class="label">Total</div>
                <div class="value">{{ $summary['total'] }}</div>
            </td>
        </tr>
    </table>
    
    <!-- Data Table -->
    @if(count($details) > 0)
    <table class="data-table">
        <thead>
            <tr style="background-color: #2563eb;">
                <th style="width: 5%; background-color: #2563eb; color: white; border: 1px solid #1e40af; padding: 10px 8px; text-align: left; font-size: 9px; font-weight: 700;">No</th>
                <th style="width: 15%; background-color: #2563eb; color: white; border: 1px solid #1e40af; padding: 10px 8px; text-align: left; font-size: 9px; font-weight: 700;">No PO</th>
                <th style="width: 12%; background-color: #2563eb; color: white; border: 1px solid #1e40af; padding: 10px 8px; text-align: left; font-size: 9px; font-weight: 700;">Tanggal</th>
                <th style="width: 20%; background-color: #2563eb; color: white; border: 1px solid #1e40af; padding: 10px 8px; text-align: left; font-size: 9px; font-weight: 700;">Supplier</th>
                <th style="width: 12%; background-color: #2563eb; color: white; border: 1px solid #1e40af; padding: 10px 8px; text-align: right; font-size: 9px; font-weight: 700;">Qty Forecast (Kg)</th>
                <th style="width: 12%; background-color: #2563eb; color: white; border: 1px solid #1e40af; padding: 10px 8px; text-align: right; font-size: 9px; font-weight: 700;">Qty Pengiriman (Kg)</th>
                <th style="width: 24%; background-color: #2563eb; color: white; border: 1px solid #1e40af; padding: 10px 8px; text-align: center; font-size: 9px; font-weight: 700;">Status Pengiriman</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $index => $detail)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="po-number">{{ $detail['po_number'] }}</td>
                <td>{{ $detail['tanggal_kirim'] }}</td>
                <td>{{ $detail['supplier'] }}</td>
                <td class="text-right">{{ number_format($detail['qty_forecast'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($detail['qty_pengiriman'], 0, ',', '.') }}</td>
                <td class="text-center">
                    <span class="badge {{ $detail['kategori'] }}">
                        {{ $detail['status_label'] }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">
        <p><strong>Tidak ada data pengiriman</strong></p>
        <p>Tidak ada data untuk periode yang dipilih</p>
    </div>
    @endif
    
    <!-- Footer -->
    <div class="footer">
        <div class="generated">Dokumen dibuat pada: {{ $generatedAt }}</div>
        <div class="company">PT Kamil Maju Persada</div>
    </div>
</body>
</html>
