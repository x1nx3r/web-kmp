<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan PO Berdasarkan Klien</title>
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
        
        .filter-info {
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 10px;
        }
        
        .filter-info strong {
            color: #0369a1;
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
            padding: 12px;
            border-right: 1px solid #ddd;
            text-align: center;
            background-color: #f9f9f9;
            width: 33.33%;
        }
        
        .summary-cell:last-child {
            border-right: none;
        }
        
        .summary-label {
            font-size: 9px;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #1a1a1a;
        }
        
        .summary-value.blue {
            color: #2563eb;
        }
        
        .summary-value.green {
            color: #059669;
        }
        
        .summary-value.purple {
            color: #7c3aed;
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
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
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
        
        td {
            padding: 8px 8px;
            font-size: 10px;
            color: #1f2937;
        }
        
        td.client-name {
            font-weight: bold;
            color: #1e40af;
        }
        
        td.cabang {
            color: #6b7280;
            font-size: 9px;
            font-style: italic;
        }
        
        tfoot {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        
        tfoot td {
            padding: 10px 8px;
            border-top: 2px solid #333;
            font-size: 11px;
        }
        
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 8px;
            color: #666;
            text-align: center;
        }
        
        @page {
            margin: 15mm;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>LAPORAN PURCHASE ORDER BERDASARKAN KLIEN</h1>
        <p>Akumulasi Purchase Order per Pabrik/Klien</p>
        <p style="margin-top: 5px;">Dicetak: {{ $generatedAt }}</p>
    </div>
    
    {{-- Filter Info --}}
    @if($filterInfo)
        <div class="filter-info">
            <strong>Filter Periode:</strong> {{ $filterInfo }}
        </div>
    @endif
    
    {{-- Summary --}}
    <div class="summary-box">
        <div class="summary-row">
            <div class="summary-cell">
                <div class="summary-label">Total Klien</div>
                <div class="summary-value blue">{{ $totalKlien }}</div>
            </div>
            <div class="summary-cell">
                <div class="summary-label">Total PO</div>
                <div class="summary-value green">{{ number_format($totalPO, 0, ',', '.') }}</div>
            </div>
            <div class="summary-cell">
                <div class="summary-label">Total Nilai</div>
                <div class="summary-value purple">
                    @if($totalNilai >= 1000000000)
                        Rp {{ number_format($totalNilai / 1000000000, 1, ',', '.') }} M
                    @elseif($totalNilai >= 1000000)
                        Rp {{ number_format($totalNilai / 1000000, 1, ',', '.') }} Jt
                    @else
                        Rp {{ number_format($totalNilai, 0, ',', '.') }}
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    {{-- Table --}}
    <table>
        <thead>
            <tr>
                <th style="width: 8%;" class="text-center">No</th>
                <th style="width: 52%;">Pabrik</th>
                <th style="width: 15%;" class="text-center">Jumlah PO</th>
                <th style="width: 25%;" class="text-right">Total Harga Semua PO</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($poByClient as $client)
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>
                        <div class="client-name">{{ $client->klien_nama }}</div>
                        @if($client->cabang)
                            <div class="cabang">{{ $client->cabang }}</div>
                        @endif
                    </td>
                    <td class="text-center" style="font-weight: bold;">
                        {{ number_format($client->total_po, 0, ',', '.') }}
                    </td>
                    <td class="text-right" style="font-weight: bold;">
                        Rp {{ number_format($client->total_nilai, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-right" style="padding-right: 15px;">TOTAL:</td>
                <td class="text-center">{{ number_format($totalPO, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totalNilai, 0, ',', '.') }}</td>
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
