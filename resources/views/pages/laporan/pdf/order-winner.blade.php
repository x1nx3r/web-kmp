<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Order Winners</title>
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
        .marketing-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .marketing-header {
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
            color: white;
            padding: 12px 15px;
            border-radius: 6px 6px 0 0;
            margin-bottom: 0;
        }
        .marketing-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .marketing-stats {
            font-size: 10px;
            opacity: 0.9;
        }
        .marketing-stats span {
            margin-right: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
            border: 1px solid #E5E7EB;
            border-top: none;
        }
        thead {
            background: #F9FAFB;
        }
        th {
            padding: 8px 10px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            color: #374151;
            text-transform: uppercase;
            border-bottom: 2px solid #E5E7EB;
            letter-spacing: 0.5px;
        }
        td {
            padding: 8px 10px;
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
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-selesai {
            background: #D1FAE5;
            color: #065F46;
        }
        .status-diproses {
            background: #DBEAFE;
            color: #1E40AF;
        }
        .status-dikonfirmasi {
            background: #FEF3C7;
            color: #92400E;
        }
        .subtotal-row {
            background: #F3F4F6;
            font-weight: bold;
            border-top: 2px solid #D1D5DB;
        }
        .grand-total {
            background: #DBEAFE;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
            border: 2px solid #3B82F6;
        }
        .grand-total-label {
            font-size: 14px;
            font-weight: bold;
            color: #1E40AF;
            text-transform: uppercase;
        }
        .grand-total-value {
            font-size: 18px;
            font-weight: bold;
            color: #1E3A8A;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #E5E7EB;
            text-align: center;
            font-size: 9px;
            color: #9CA3AF;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>Laporan Order Winners</h1>
        <div class="subtitle">Rincian Purchase Order per Marketing</div>
        <div class="meta">
            <strong>Periode:</strong> {{ $filterInfo }} | 
            <strong>Dicetak:</strong> {{ $generatedAt }}
        </div>
    </div>

    {{-- Summary --}}
    <div class="summary-box">
        <div class="summary-item">
            <div class="summary-label">Total Marketing</div>
            <div class="summary-value">{{ count($groupedData) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total PO</div>
            <div class="summary-value">{{ number_format($totalPO, 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Nilai</div>
            <div class="summary-value">
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

    {{-- Data per Marketing > Klien > Cabang > PO --}}
    @foreach($groupedData as $marketing)
        <div class="marketing-section">
            <div class="marketing-header">
                <div class="marketing-name">{{ $marketing['marketing_nama'] }}</div>
                <div class="marketing-stats">
                    <span><strong>{{ $marketing['total_po'] }}</strong> PO</span>
                    <span><strong>Rp {{ number_format($marketing['total_nilai'] / 1000000, 1, ',', '.') }} Jt</strong></span>
                </div>
            </div>
            
            @foreach($marketing['kliens'] as $klien)
                {{-- Klien Header --}}
                <div style="background: #F3F4F6; padding: 10px 15px; border-left: 4px solid #6B7280; margin: 15px 0 10px 0;">
                    <div style="font-weight: bold; font-size: 12px; color: #374151; margin-bottom: 3px;">
                        <span style="color: #6B7280;">▸</span> {{ $klien['klien_nama'] }}
                    </div>
                    <div style="font-size: 10px; color: #6B7280;">
                        {{ $klien['total_po'] }} PO • Rp {{ number_format($klien['total_nilai'] / 1000000, 1, ',', '.') }} Jt
                    </div>
                </div>
                
                @foreach($klien['cabangs'] as $cabang)
                    {{-- Cabang Header --}}
                    <div style="background: #FFFFFF; padding: 8px 15px; border-left: 3px solid #F59E0B; margin: 8px 0 5px 20px;">
                        <div style="font-weight: 600; font-size: 11px; color: #92400E;">
                            <span style="color: #F59E0B;">📍</span> {{ $cabang['cabang_nama'] }}
                        </div>
                        <div style="font-size: 9px; color: #78716C;">
                            {{ $cabang['total_po'] }} PO • Rp {{ number_format($cabang['total_nilai'] / 1000000, 1, ',', '.') }} Jt
                        </div>
                    </div>
                    
                    {{-- Orders Table --}}
                    <table style="margin-left: 20px; width: calc(100% - 20px); margin-bottom: 10px;">
                        <thead>
                            <tr>
                                <th style="width: 5%">No</th>
                                <th style="width: 25%">No PO</th>
                                <th style="width: 20%" class="text-center">Tanggal Order</th>
                                <th style="width: 15%" class="text-center">Qty</th>
                                <th style="width: 15%" class="text-center">Status</th>
                                <th style="width: 20%" class="text-right">Nilai PO</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cabang['orders'] as $index => $order)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="font-bold">{{ $order['po_number'] }}</td>
                                <td class="text-center">{{ $order['tanggal_order'] }}</td>
                                <td class="text-center">{{ number_format($order['total_qty'], 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <span class="status-badge status-{{ $order['order_status'] }}">
                                        {{ ucfirst($order['order_status']) }}
                                    </span>
                                </td>
                                <td class="text-right font-bold">
                                    Rp {{ number_format($order['total_nilai'] / 1000000, 1, ',', '.') }} Jt
                                </td>
                            </tr>
                            @endforeach
                            <tr style="background: #FEF3C7; font-weight: bold;">
                                <td colspan="5" style="text-align: right; padding: 8px 10px;">
                                    SUBTOTAL {{ strtoupper($cabang['cabang_nama']) }}
                                </td>
                                <td class="text-right" style="padding: 8px 10px;">
                                    Rp {{ number_format($cabang['total_nilai'] / 1000000, 1, ',', '.') }} Jt
                                </td>
                            </tr>
                        </tbody>
                    </table>
                @endforeach
                
                {{-- Klien Subtotal --}}
                <div style="background: #E5E7EB; padding: 8px 15px; margin: 10px 0 15px 0; font-weight: bold; text-align: right;">
                    SUBTOTAL {{ strtoupper($klien['klien_nama']) }}: 
                    <span style="color: #1F2937;">Rp {{ number_format($klien['total_nilai'] / 1000000, 1, ',', '.') }} Jt</span>
                </div>
            @endforeach
            
            {{-- Marketing Subtotal --}}
            <div style="background: #BFDBFE; padding: 10px 15px; margin: 10px 0; font-weight: bold; font-size: 12px; text-align: right; border-radius: 0 0 6px 6px;">
                TOTAL {{ strtoupper($marketing['marketing_nama']) }}: 
                <span style="color: #1E40AF;">Rp {{ number_format($marketing['total_nilai'] / 1000000, 1, ',', '.') }} Jt</span>
            </div>
        </div>
    @endforeach

    {{-- Grand Total --}}
    <div class="grand-total">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none; padding: 0;">
                    <span class="grand-total-label">GRAND TOTAL</span>
                </td>
                <td style="border: none; padding: 0;" class="text-right">
                    <span class="grand-total-value">
                        Rp {{ number_format($totalNilai / 1000000, 1, ',', '.') }} Jt
                    </span>
                </td>
            </tr>
        </table>
    </div>

    {{-- Footer --}}
    <div class="footer">
        Dokumen ini digenerate secara otomatis oleh sistem<br>
        PT Kamil Maju Persada © {{ date('Y') }}
    </div>
</body>
</html>
