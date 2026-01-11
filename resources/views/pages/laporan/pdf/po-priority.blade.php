<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan PO Berdasarkan Prioritas</title>
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
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-tinggi {
            background: #FEE2E2;
            color: #991B1B;
        }
        .badge-sedang {
            background: #FEF3C7;
            color: #92400E;
        }
        .badge-rendah {
            background: #F3F4F6;
            color: #374151;
        }
        .badge-dikonfirmasi {
            background: #DBEAFE;
            color: #1E40AF;
        }
        .badge-diproses {
            background: #FEF3C7;
            color: #92400E;
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
        .highlight-tinggi {
            background: #FEE2E2 !important;
        }
        .highlight-sedang {
            background: #FEF3C7 !important;
        }
        .highlight-rendah {
            background: #F3F4F6 !important;
        }
        .analysis-box {
            background: #F3F4F6;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .analysis-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #374151;
        }
        .analysis-list {
            list-style: none;
            padding-left: 0;
            font-size: 10px;
            color: #6B7280;
        }
        .analysis-list li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>Laporan PO Berdasarkan Prioritas</h1>
        <div class="subtitle">Distribusi Purchase Order Berdasarkan Tingkat Prioritas</div>
        <div class="meta">
            <strong>Dicetak:</strong> {{ $generatedAt }}
        </div>
    </div>

    {{-- Summary --}}
    <div class="summary-box">
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

    {{-- Table Summary --}}
    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 20%">Prioritas</th>
                <th style="width: 15%" class="text-center">Jumlah PO</th>
                <th style="width: 25%" class="text-right">Total Nilai</th>
                <th style="width: 20%" class="text-right">Rata-rata per PO</th>
                <th style="width: 15%" class="text-center">Persentase</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $no = 1;
            @endphp
            @foreach($poByPriority as $priority)
                @php
                    $avgPerPOPriority = $priority->total > 0 ? $priority->nilai / $priority->total : 0;
                    $percentage = $totalNilai > 0 ? ($priority->nilai / $totalNilai) * 100 : 0;
                    
                    // Set badge class
                    $badgeClass = match($priority->priority) {
                        'tinggi' => 'badge-tinggi',
                        'sedang' => 'badge-sedang',
                        'rendah' => 'badge-rendah',
                        default => 'badge-rendah'
                    };
                    
                    // Set row highlight
                    $rowClass = match($priority->priority) {
                        'tinggi' => 'highlight-tinggi',
                        'sedang' => 'highlight-sedang',
                        'rendah' => 'highlight-rendah',
                        default => ''
                    };
                @endphp
                <tr class="{{ $rowClass }}">
                    <td class="text-center">{{ $no++ }}</td>
                    <td>
                        <span class="badge {{ $badgeClass }}">{{ ucfirst($priority->priority) }}</span>
                    </td>
                    <td class="text-center font-bold">{{ number_format($priority->total, 0, ',', '.') }}</td>
                    <td class="text-right font-bold">Rp {{ number_format($priority->nilai, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($avgPerPOPriority, 0, ',', '.') }}</td>
                    <td class="text-center font-bold">{{ number_format($percentage, 1, ',', '.') }}%</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-right">TOTAL</td>
                <td class="text-center">{{ number_format($totalPO, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totalNilai, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($avgPerPO, 0, ',', '.') }}</td>
                <td class="text-center">100.0%</td>
            </tr>
        </tfoot>
    </table>

    {{-- Detail PO Per Priority --}}
    <div style="margin-top: 25px;">
        <h2 style="font-size: 14px; font-weight: bold; color: #1E40AF; margin-bottom: 15px; border-bottom: 2px solid #3B82F6; padding-bottom: 8px;">
            DETAIL PURCHASE ORDER PER PRIORITAS
        </h2>
        
        @foreach($poByPriority as $priority)
            @php
                $headerBg = match($priority->priority) {
                    'tinggi' => '#FEE2E2',
                    'sedang' => '#FEF3C7',
                    'rendah' => '#F3F4F6',
                    default => '#EFF6FF'
                };
                
                $headerColor = match($priority->priority) {
                    'tinggi' => '#991B1B',
                    'sedang' => '#92400E',
                    'rendah' => '#374151',
                    default => '#1E40AF'
                };
            @endphp
            
            {{-- Priority Section Header --}}
            <div style="background: {{ $headerBg }}; padding: 12px; margin-bottom: 10px; border-radius: 6px; border-left: 4px solid {{ $headerColor }};">
                <div style="display: table; width: 100%;">
                    <div style="display: table-cell; vertical-align: middle;">
                        <span style="font-size: 13px; font-weight: bold; color: {{ $headerColor }}; text-transform: uppercase;">
                            📌 PRIORITAS {{ strtoupper($priority->priority) }}
                        </span>
                        <span style="font-size: 11px; color: {{ $headerColor }}; margin-left: 10px;">
                            ({{ number_format($priority->total, 0, ',', '.') }} PO)
                        </span>
                    </div>
                    <div style="display: table-cell; vertical-align: middle; text-align: right;">
                        <span style="font-size: 12px; font-weight: bold; color: {{ $headerColor }};">
                            Total: Rp {{ number_format($priority->nilai, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
            
            {{-- PO Details Table --}}
            @if(isset($poDetailsByPriority[$priority->priority]) && count($poDetailsByPriority[$priority->priority]) > 0)
            <table style="margin-bottom: 20px;">
                <thead>
                    <tr>
                        <th style="width: 4%">No</th>
                        <th style="width: 14%">No. PO</th>
                        <th style="width: 22%">Klien</th>
                        <th style="width: 14%">Cabang</th>
                        <th style="width: 10%">Tanggal</th>
                        <th style="width: 8%" class="text-center">Qty</th>
                        <th style="width: 18%" class="text-right">Nilai</th>
                        <th style="width: 10%" class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($poDetailsByPriority[$priority->priority] as $index => $po)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="font-bold" style="color: #2563EB;">{{ $po['po_number'] }}</td>
                        <td>{{ $po['klien_nama'] }}</td>
                        <td>{{ $po['cabang'] }}</td>
                        <td class="text-center">{{ $po['tanggal_order'] }}</td>
                        <td class="text-center">{{ number_format($po['total_qty'], 0, ',', '.') }}</td>
                        <td class="text-right font-bold">Rp {{ number_format($po['total_amount'], 0, ',', '.') }}</td>
                        <td class="text-center">
                            <span class="badge {{ $po['status'] == 'dikonfirmasi' ? 'badge-tinggi' : 'badge-sedang' }}" style="font-size: 8px;">
                                {{ ucfirst($po['status']) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-right font-bold">Subtotal {{ ucfirst($priority->priority) }}:</td>
                        <td class="text-center font-bold">
                            {{ number_format(collect($poDetailsByPriority[$priority->priority])->sum('total_qty'), 0, ',', '.') }}
                        </td>
                        <td class="text-right font-bold">
                            Rp {{ number_format(collect($poDetailsByPriority[$priority->priority])->sum('total_amount'), 0, ',', '.') }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            @else
            <div style="background: #F9FAFB; padding: 15px; text-align: center; color: #6B7280; font-size: 10px; margin-bottom: 20px; border-radius: 4px;">
                Tidak ada PO untuk prioritas ini
            </div>
            @endif
        @endforeach
    </div>

    {{-- Analysis Summary --}}
    <div class="analysis-box">
        <h3 class="analysis-title">Ringkasan Analisis:</h3>
        <ul class="analysis-list">
            @php
                $tinggiData = $poByPriority->where('priority', 'tinggi')->first();
                $sedangData = $poByPriority->where('priority', 'sedang')->first();
                $rendahData = $poByPriority->where('priority', 'rendah')->first();
            @endphp
            @if($tinggiData)
            <li>
                <strong>✓ Prioritas Tinggi:</strong> {{ number_format($tinggiData->total, 0, ',', '.') }} PO dengan total nilai Rp {{ number_format($tinggiData->nilai, 0, ',', '.') }}
                ({{ number_format($totalNilai > 0 ? ($tinggiData->nilai / $totalNilai) * 100 : 0, 1, ',', '.') }}% dari total nilai)
            </li>
            @endif
            @if($sedangData)
            <li>
                <strong>✓ Prioritas Sedang:</strong> {{ number_format($sedangData->total, 0, ',', '.') }} PO dengan total nilai Rp {{ number_format($sedangData->nilai, 0, ',', '.') }}
                ({{ number_format($totalNilai > 0 ? ($sedangData->nilai / $totalNilai) * 100 : 0, 1, ',', '.') }}% dari total nilai)
            </li>
            @endif
            @if($rendahData)
            <li>
                <strong>✓ Prioritas Rendah:</strong> {{ number_format($rendahData->total, 0, ',', '.') }} PO dengan total nilai Rp {{ number_format($rendahData->nilai, 0, ',', '.') }}
                ({{ number_format($totalNilai > 0 ? ($rendahData->nilai / $totalNilai) * 100 : 0, 1, ',', '.') }}% dari total nilai)
            </li>
            @endif
            <li>
                <strong>✓ Distribusi PO:</strong> Status dikonfirmasi dan diproses dari semua prioritas
            </li>
        </ul>
    </div>

    {{-- Grand Total --}}
    <div class="grand-total">
        <div class="grand-total-row">
            <div class="grand-total-label">GRAND TOTAL</div>
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
