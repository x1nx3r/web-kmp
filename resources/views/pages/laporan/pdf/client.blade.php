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
            font-size: 9px;
            line-height: 1.3;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #333;
        }
        
        .header h1 {
            font-size: 16px;
            margin-bottom: 3px;
            color: #1a1a1a;
        }
        
        .header p {
            font-size: 9px;
            color: #666;
        }
        
        .filter-info {
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 3px;
            padding: 6px 10px;
            margin-bottom: 10px;
            font-size: 9px;
        }
        
        .filter-info strong {
            color: #0369a1;
        }
        
        .summary-box {
            display: table;
            width: 100%;
            margin-bottom: 12px;
            border: 1px solid #ddd;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .summary-row {
            display: table-row;
        }
        
        .summary-cell {
            display: table-cell;
            padding: 8px;
            border-right: 1px solid #ddd;
            text-align: center;
            background-color: #f9f9f9;
            width: 20%;
        }
        
        .summary-cell:last-child {
            border-right: none;
        }
        
        .summary-label {
            font-size: 7px;
            color: #666;
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        
        .summary-value {
            font-size: 12px;
            font-weight: bold;
            color: #1a1a1a;
        }
        
        .summary-value.blue { color: #2563eb; }
        .summary-value.green { color: #059669; }
        .summary-value.purple { color: #7c3aed; }
        .summary-value.orange { color: #ea580c; }
        .summary-value.teal { color: #0d9488; }
        
        .client-section {
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 3px;
            page-break-inside: avoid;
        }
        
        .client-header {
            background: linear-gradient(90deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            padding: 8px 10px;
        }
        
        .client-header h3 {
            font-size: 11px;
            margin-bottom: 2px;
        }
        
        .client-header .cabang {
            font-size: 8px;
            opacity: 0.85;
        }
        
        .client-stats {
            display: table;
            width: 100%;
            background-color: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .client-stats-row {
            display: table-row;
        }
        
        .client-stat {
            display: table-cell;
            padding: 6px 8px;
            text-align: center;
            border-right: 1px solid #e5e7eb;
            font-size: 8px;
        }
        
        .client-stat:last-child {
            border-right: none;
        }
        
        .client-stat .label {
            color: #64748b;
            font-size: 7px;
        }
        
        .client-stat .value {
            font-weight: bold;
            color: #1e293b;
            font-size: 9px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 7px;
            font-weight: bold;
        }
        
        .status-dikonfirmasi { background: #fef3c7; color: #92400e; }
        .status-diproses { background: #dbeafe; color: #1e40af; }
        .status-selesai { background: #d1fae5; color: #065f46; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }
        
        thead {
            background-color: #f1f5f9;
        }
        
        th {
            padding: 5px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 7px;
            text-transform: uppercase;
            border-bottom: 1px solid #cbd5e1;
            color: #475569;
        }
        
        th.text-right, td.text-right { text-align: right; }
        th.text-center, td.text-center { text-align: center; }
        
        tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }
        
        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        td {
            padding: 4px 6px;
            font-size: 8px;
            color: #374151;
        }
        
        td.po-number {
            font-weight: bold;
            color: #2563eb;
        }
        
        .priority-tinggi { background: #fee2e2; color: #991b1b; }
        .priority-sedang { background: #ffedd5; color: #9a3412; }
        .priority-rendah { background: #f3f4f6; color: #4b5563; }
        
        .footer {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
            font-size: 7px;
            color: #666;
            text-align: center;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .grand-total {
            margin-top: 15px;
            background: linear-gradient(90deg, #1e293b 0%, #334155 100%);
            color: white;
            padding: 10px;
            border-radius: 3px;
        }
        
        .grand-total-row {
            display: table;
            width: 100%;
        }
        
        .grand-total-cell {
            display: table-cell;
            text-align: center;
            padding: 5px;
        }
        
        .grand-total-label {
            font-size: 7px;
            opacity: 0.8;
        }
        
        .grand-total-value {
            font-size: 12px;
            font-weight: bold;
        }
        
        @page {
            margin: 10mm;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>LAPORAN PURCHASE ORDER BERDASARKAN KLIEN</h1>
        <p>Akumulasi Purchase Order per Pabrik/Klien (Lengkap dengan Detail)</p>
        <p style="margin-top: 3px;">Dicetak: {{ $generatedAt }}</p>
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
                        Rp {{ number_format($totalNilai / 1000000000, 2, ',', '.') }} M
                    @elseif($totalNilai >= 1000000)
                        Rp {{ number_format($totalNilai / 1000000, 1, ',', '.') }} Jt
                    @else
                        Rp {{ number_format($totalNilai, 0, ',', '.') }}
                    @endif
                </div>
            </div>
            <div class="summary-cell">
                <div class="summary-label">Outstanding</div>
                <div class="summary-value orange">
                    @if($totalOutstanding >= 1000000000)
                        Rp {{ number_format($totalOutstanding / 1000000000, 2, ',', '.') }} M
                    @elseif($totalOutstanding >= 1000000)
                        Rp {{ number_format($totalOutstanding / 1000000, 1, ',', '.') }} Jt
                    @else
                        Rp {{ number_format($totalOutstanding, 0, ',', '.') }}
                    @endif
                </div>
            </div>
            <div class="summary-cell">
                <div class="summary-label">Rata-rata/PO</div>
                <div class="summary-value teal">
                    @if($avgPerPO >= 1000000)
                        Rp {{ number_format($avgPerPO / 1000000, 1, ',', '.') }} Jt
                    @else
                        Rp {{ number_format($avgPerPO, 0, ',', '.') }}
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    {{-- Client Sections --}}
    @php $no = 1; @endphp
    @foreach($poByClient as $client)
        <div class="client-section">
            {{-- Client Header --}}
            <div class="client-header">
                <h3>{{ $no++ }}. {{ $client->klien_nama }}</h3>
                @if($client->cabang)
                    <span class="cabang">{{ $client->cabang }}</span>
                @endif
            </div>
            
            {{-- Client Stats --}}
            <div class="client-stats">
                <div class="client-stats-row">
                    <div class="client-stat">
                        <div class="label">Total PO</div>
                        <div class="value">{{ $client->total_po }}</div>
                    </div>
                    <div class="client-stat">
                        <div class="label">Total Nilai</div>
                        <div class="value">Rp {{ number_format($client->total_nilai, 0, ',', '.') }}</div>
                    </div>
                    <div class="client-stat">
                        <div class="label">Outstanding</div>
                        <div class="value" style="color: #ea580c;">Rp {{ number_format($client->outstanding_amount, 0, ',', '.') }}</div>
                    </div>
                    <div class="client-stat">
                        <div class="label">Rata-rata/PO</div>
                        <div class="value">Rp {{ number_format($client->avg_nilai_per_po, 0, ',', '.') }}</div>
                    </div>
                    <div class="client-stat">
                        <div class="label">Kontribusi</div>
                        <div class="value">{{ number_format($client->percentage, 1, ',', '.') }}%</div>
                    </div>
                    <div class="client-stat">
                        <div class="label">Status</div>
                        <div class="value">
                            @if($client->status_dikonfirmasi > 0)
                                <span class="status-badge status-dikonfirmasi">{{ $client->status_dikonfirmasi }}</span>
                            @endif
                            @if($client->status_diproses > 0)
                                <span class="status-badge status-diproses">{{ $client->status_diproses }}</span>
                            @endif
                            @if($client->status_selesai > 0)
                                <span class="status-badge status-selesai">{{ $client->status_selesai }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- PO Details Table --}}
            @if(isset($poDetailsByClient[$client->klien_id]) && count($poDetailsByClient[$client->klien_id]) > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 12%;">No. PO</th>
                        <th style="width: 10%;">Tanggal</th>
                        <th style="width: 30%;">Material</th>
                        <th style="width: 10%;" class="text-center">Status</th>
                        <th style="width: 10%;" class="text-center">Prioritas</th>
                        <th style="width: 12%;" class="text-right">Qty</th>
                        <th style="width: 16%;" class="text-right">Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($poDetailsByClient[$client->klien_id] as $po)
                    <tr>
                        <td class="po-number">{{ $po['po_number'] }}</td>
                        <td>{{ $po['tanggal_order'] }}</td>
                        <td>{{ Str::limit($po['materials'], 50) }}</td>
                        <td class="text-center">
                            <span class="status-badge status-{{ $po['status'] }}">
                                {{ ucfirst($po['status']) }}
                            </span>
                        </td>
                        <td class="text-center">
                            @php
                                $priorityClass = match($po['priority']) {
                                    'tinggi' => 'priority-tinggi',
                                    'sedang' => 'priority-sedang',
                                    default => 'priority-rendah',
                                };
                            @endphp
                            <span class="status-badge {{ $priorityClass }}">
                                {{ ucfirst($po['priority'] ?? '-') }}
                            </span>
                        </td>
                        <td class="text-right">{{ number_format($po['total_qty'], 0, ',', '.') }} kg</td>
                        <td class="text-right" style="font-weight: bold;">Rp {{ number_format($po['total_amount'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    @endforeach
    
    {{-- Grand Total --}}
    <div class="grand-total">
        <div class="grand-total-row">
            <div class="grand-total-cell">
                <div class="grand-total-label">Total Klien</div>
                <div class="grand-total-value">{{ $totalKlien }}</div>
            </div>
            <div class="grand-total-cell">
                <div class="grand-total-label">Total PO</div>
                <div class="grand-total-value">{{ number_format($totalPO, 0, ',', '.') }}</div>
            </div>
            <div class="grand-total-cell">
                <div class="grand-total-label">Total Nilai</div>
                <div class="grand-total-value">Rp {{ number_format($totalNilai, 0, ',', '.') }}</div>
            </div>
            <div class="grand-total-cell">
                <div class="grand-total-label">Total Outstanding</div>
                <div class="grand-total-value" style="color: #fbbf24;">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    
    {{-- Footer --}}
    <div class="footer">
        <p>Laporan ini digenerate secara otomatis oleh sistem KMP</p>
        <p>© {{ date('Y') }} PT. Kamil Maju Persada - All Rights Reserved</p>
    </div>
</body>
</html>
