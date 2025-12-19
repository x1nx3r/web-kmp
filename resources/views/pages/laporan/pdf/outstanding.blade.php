<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Outstanding PO</title>
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
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
            color: #1a1a1a;
        }
        
        .header p {
            font-size: 10px;
            color: #666;
        }
        
        .summary-box {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .summary-row {
            display: table-row;
        }
        
        .summary-cell {
            display: table-cell;
            padding: 10px;
            border-right: 1px solid #ddd;
            text-align: center;
            background-color: #f9f9f9;
        }
        
        .summary-cell:last-child {
            border-right: none;
        }
        
        .summary-label {
            font-size: 9px;
            color: #666;
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #1a1a1a;
        }
        
        .summary-value.red {
            color: #dc2626;
        }
        
        .summary-value.blue {
            color: #2563eb;
        }
        
        .summary-value.orange {
            color: #ea580c;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        thead {
            background-color: #f3f4f6;
        }
        
        th {
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            border-bottom: 2px solid #ddd;
            color: #374151;
        }
        
        th.text-right, td.text-right {
            text-align: right;
        }
        
        th.text-center, td.text-center {
            text-align: center;
        }
        
        tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }
        
        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        tbody tr:hover {
            background-color: #f3f4f6;
        }
        
        td {
            padding: 6px 6px;
            font-size: 9px;
            color: #1f2937;
        }
        
        td.po-number {
            font-weight: bold;
            color: #1e40af;
        }
        
        td.cabang {
            color: #6b7280;
            font-size: 8px;
        }
        
        tfoot {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        
        tfoot td {
            padding: 8px 6px;
            border-top: 2px solid #333;
            font-size: 10px;
        }
        
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 8px;
            color: #666;
            text-align: center;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        @page {
            margin: 15mm;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>LAPORAN OUTSTANDING PURCHASE ORDER</h1>
        <p>Daftar Item yang Masih Outstanding</p>
        <p style="margin-top: 5px;">Dicetak: {{ $generatedAt }}</p>
    </div>
    
    {{-- Summary --}}
    <div class="summary-box">
        <div class="summary-row">
            <div class="summary-cell">
                <div class="summary-label">Total Outstanding</div>
                <div class="summary-value red">
                    Rp {{ number_format($totalNilai, 0, ',', '.') }}
                </div>
            </div>
            <div class="summary-cell">
                <div class="summary-label">Total PO</div>
                <div class="summary-value blue">{{ $totalPO }}</div>
            </div>
            <div class="summary-cell">
                <div class="summary-label">Total Quantity</div>
                <div class="summary-value orange">{{ number_format($totalQty, 0, ',', '.') }} kg</div>
            </div>
            <div class="summary-cell">
                <div class="summary-label">Total Item</div>
                <div class="summary-value">{{ $outstandingDetails->count() }}</div>
            </div>
        </div>
    </div>
    
    {{-- Table --}}
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 4%;">No</th>
                <th style="width: 12%;">PO</th>
                <th style="width: 20%;">Pabrik</th>
                <th style="width: 28%;">Item</th>
                <th class="text-right" style="width: 12%;">Qty (kg)</th>
                <th class="text-right" style="width: 12%;">Harga (Rp/kg)</th>
                <th class="text-right" style="width: 12%;">Total (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($outstandingDetails as $detail)
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td class="po-number">{{ $detail->po_number ?: $detail->no_order }}</td>
                    <td>
                        {{ $detail->klien_nama }}
                        @if($detail->klien_cabang)
                            <br><span class="cabang">({{ $detail->klien_cabang }})</span>
                        @endif
                    </td>
                    <td>{{ $detail->material_nama ?: '-' }}</td>
                    <td class="text-right">{{ number_format($detail->qty, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($detail->harga_jual, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($detail->total_harga, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right" style="padding-right: 10px;">TOTAL:</td>
                <td class="text-right">{{ number_format($totalQty, 2, ',', '.') }}</td>
                <td class="text-right">-</td>
                <td class="text-right">{{ number_format($totalNilai, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    
    {{-- Footer --}}
    <div class="footer">
        <p>Laporan ini digenerate secara otomatis oleh sistem KMP</p>
        <p>© {{ date('Y') }} PT. Kamil Maju Persada - All Rights Reserved</p>
    </div>
</body>
</html>
