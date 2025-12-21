<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Omset Marketing</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .total-row {
            background-color: #e8f4f8;
            font-weight: bold;
        }
        .marketing-header {
            background-color: #3B82F6;
            color: white;
            padding: 8px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Omset Marketing</h2>
        <p>
            @if($periode == 'tahun_ini')
                Periode: Tahun {{ date('Y') }}
            @elseif($periode == 'bulan_ini')
                Periode: Bulan {{ date('F Y') }}
            @elseif($periode == 'custom')
                Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            @else
                Periode: Semua Data
            @endif
        </p>
        <p>Tanggal Cetak: {{ date('d/m/Y H:i') }}</p>
    </div>

    @foreach($groupedData as $marketingNama => $details)
        <div class="marketing-header">
            <strong>Marketing: {{ $marketingNama }}</strong>
            <span style="float: right;">
                Total: Rp {{ number_format($details->sum('total_nilai'), 0, ',', '.') }}
            </span>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="15%">No PO</th>
                    <th width="35%">Nama Pabrik</th>
                    <th width="30%" style="text-align: right;">Nilai</th>
                </tr>
            </thead>
            <tbody>
                @foreach($details as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->po_number }}</td>
                        <td>{{ $item->klien_nama }}</td>
                        <td style="text-align: right;">Rp {{ number_format($item->total_nilai, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;"><strong>Subtotal {{ $marketingNama }}:</strong></td>
                    <td style="text-align: right;"><strong>Rp {{ number_format($details->sum('total_nilai'), 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>
    @endforeach

    <table style="margin-top: 30px;">
        <tr class="total-row">
            <td style="text-align: right; font-size: 14px;"><strong>TOTAL KESELURUHAN:</strong></td>
            <td style="text-align: right; width: 30%; font-size: 14px;"><strong>Rp {{ number_format($totalOverall, 0, ',', '.') }}</strong></td>
        </tr>
    </table>
</body>
</html>
