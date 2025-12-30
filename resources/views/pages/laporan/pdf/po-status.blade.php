<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan PO Berdasarkan Status</title>
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
            padding-bottom: 10px;
            border-bottom: 2px solid #3B82F6;
        }
        .header h1 {
            font-size: 16px;
            color: #1E40AF;
            margin-bottom: 3px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header .subtitle {
            font-size: 10px;
            color: #6B7280;
            margin-bottom: 5px;
        }
        .header .meta {
            font-size: 8px;
            color: #9CA3AF;
        }
        .summary-box {
            background: #EFF6FF;
            border: 1px solid #3B82F6;
            border-radius: 4px;
            padding: 8px;
            margin-bottom: 15px;
        }
        .summary-grid {
            display: flex;
            justify-content: space-around;
            text-align: center;
        }
        .summary-item {
            flex: 1;
        }
        .summary-label {
            font-size: 8px;
            color: #1E40AF;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .summary-value {
            font-size: 12px;
            color: #1E3A8A;
            font-weight: bold;
        }
        .status-section {
            page-break-inside: avoid;
            margin-bottom: 15px;
            border: 1px solid #E5E7EB;
            border-radius: 4px;
        }
        .status-header {
            padding: 6px 8px;
            font-weight: bold;
            font-size: 10px;
            border-bottom: 1px solid #E5E7EB;
        }
        .status-header-draft {
            background: #F3F4F6;
            color: #374151;
        }
        .status-header-dikonfirmasi {
            background: #FEF3C7;
            color: #92400E;
        }
        .status-header-diproses {
            background: #DBEAFE;
            color: #1E40AF;
        }
        .status-header-selesai {
            background: #D1FAE5;
            color: #065F46;
        }
        .status-header-dibatalkan {
            background: #FEE2E2;
            color: #991B1B;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            padding: 5px 4px;
            text-align: left;
            font-size: 7px;
            font-weight: bold;
            color: #374151;
            text-transform: uppercase;
            background: #F9FAFB;
            border-bottom: 1px solid #E5E7EB;
        }
        td {
            padding: 4px;
            font-size: 8px;
            border-bottom: 1px solid #F3F4F6;
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
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 8px;
            font-size: 7px;
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
        .empty-state {
            padding: 15px;
            text-align: center;
            color: #9CA3AF;
            font-style: italic;
        }
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
        .highlight-draft {
            background: #F9FAFB !important;
        }
        .highlight-dikonfirmasi {
            background: #FFFBEB !important;
        }
        .highlight-diproses {
            background: #EFF6FF !important;
        }
        .highlight-selesai {
            background: #ECFDF5 !important;
        }
        .highlight-dibatalkan {
            background: #FEF2F2 !important;
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
        .status-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .status-card {
            display: table-cell;
            padding: 10px;
            width: 20%;
        }
        .status-card-inner {
            border: 2px solid #E5E7EB;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
        }
        .status-icon {
            font-size: 24px;
            margin-bottom: 8px;
        }
        .status-label {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .status-value {
            font-size: 16px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>Laporan PO Berdasarkan Status</h1>
        <div class="subtitle">Distribusi Purchase Order Berdasarkan Status</div>
        <div class="meta">
            <strong>Dicetak:</strong> {{ $generatedAt }}
        </div>
    </div>

    {{-- Summary --}}
    <div class="summary-box">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total PO</div>
                <div class="summary-value">{{ number_format($totalPO, 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Nilai</div>
                <div class="summary-value">Rp {{ number_format($totalNilai / 1000000, 0, ',', '.') }}Jt</div>
            </div>
        </div>
    </div>

    {{-- Detail PO by Status --}}
    @foreach($poByStatus as $status)
        @php
            $statusLabel = match($status->status) {
                'draft' => 'Draft',
                'dikonfirmasi' => 'Dikonfirmasi',
                'diproses' => 'Diproses',
                'selesai' => 'Selesai',
                'dibatalkan' => 'Dibatalkan',
                default => ucfirst($status->status)
            };
            $headerClass = 'status-header-' . $status->status;
            $poDetails = $poDetailsByStatus[$status->status] ?? [];
            $percentage = $totalPO > 0 ? ($status->total / $totalPO) * 100 : 0;
        @endphp
        
        <div class="status-section">
            {{-- Status Header --}}
            <div class="status-header {{ $headerClass }}">
                {{ $statusLabel }} - {{ number_format($status->total, 0, ',', '.') }} PO 
                ({{ number_format($percentage, 1, ',', '.') }}%) 
                • Total: Rp {{ number_format($status->nilai, 0, ',', '.') }}
            </div>
            
            {{-- PO Table --}}
            @if(count($poDetails) > 0)
                <table>
                    <thead>
                        <tr>
                            <th style="width: 25%">No PO</th>
                            <th style="width: 55%">Klien</th>
                            <th style="width: 20%">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($poDetails as $po)
                            <tr>
                                <td class="font-bold">{{ $po['po_number'] }}</td>
                                <td>{{ $po['klien_nama'] }}</td>
                                <td>{{ $po['tanggal_order'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">Tidak ada PO dengan status ini</div>
            @endif
        </div>
    @endforeach

    {{-- Footer --}}
    <div style="margin-top: 20px; padding-top: 10px; border-top: 1px solid #E5E7EB; text-align: center; font-size: 7px; color: #9CA3AF;">
        Dokumen ini digenerate secara otomatis oleh sistem<br>
        &copy; {{ date('Y') }} - KMP Management System
    </div>
</body>
</html>
        PT Kamil Maju Persada © {{ date('Y') }}
    </div>
</body>
</html>
