<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Piutang Pelanggan - {{ $klien->nama }}</title>
    <style>
        @page {
            margin: 1cm 1.5cm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            color: #333;
            line-height: 1.4;
        }

        .header {
            width: 100%;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #4BCBF7;
        }

        .header-table {
            width: 100%;
            border: none;
        }

        .header-left h1 {
            font-size: 16pt;
            font-weight: bold;
            color: #4BCBF7;
            margin: 0;
            text-transform: uppercase;
        }

        .header-left h2 {
            font-size: 12pt;
            font-weight: normal;
            color: #555;
            margin: 5px 0;
            letter-spacing: 1px;
        }

        .header-right {
            text-align: right;
            vertical-align: top;
        }

        .logo-text {
            font-size: 24pt;
            font-weight: 800;
            color: #4BCBF7;
        }

        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background-color: #fff;
        }

        .table-data th {
            background-color: #4BCBF7;
            color: #ffffff;
            font-weight: bold;
            font-size: 8pt;
            padding: 10px 8px;
            text-transform: uppercase;
            border: 1px solid #4BCBF7;
        }

        .table-data td {
            padding: 8px;
            border: 1px solid #e0e0e0;
            font-size: 8.5pt;
            vertical-align: top;
        }

        .table-data tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .client-row td {
            background-color: #e8f4fc !important;
            color: #1a5f8a;
            font-weight: bold;
            padding: 10px 8px;
            border-bottom: 2px solid #bcdbf3;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }

        .total-row td {
            background-color: #f1f1f1;
            font-weight: bold;
            color: #333;
            border-top: 2px solid #ccc;
        }

        .grand-total-row td {
            background-color: #4BCBF7;
            color: #fff;
            font-weight: bold;
            border: 1px solid #4BCBF7;
        }

        .footer {
            position: fixed;
            bottom: 0px;
            left: 0px;
            right: 0px;
            height: 80px;
            font-size: 8pt;
            color: #666;
        }

        .footer-table {
            width: 100%;
            border: none;
            border-top: 1px solid #aaa;
            margin-top: 10px;
        }

        .footer-table td {
            padding-top: 8px;
            border: none;
            vertical-align: top;
        }

        .page-number:after { content: counter(page); }

    </style>
</head>
<body>

    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-left">
                    <h1>PT. KAMIL MAJU PERSADA</h1>
                    <h2>LAPORAN PIUTANG PELANGGAN</h2>
                    <div style="font-size: 9pt; margin-top: 5px;">
                        Tanggal: <strong>{{ $tanggalCetak }}</strong> <br>
                        <span style="font-size: 8pt; color: #888;">(dalam IDR)</span>
                    </div>
                </td>
                <td class="header-right">
                    @if(file_exists(public_path('assets/image/logo/ptkmp-logo.png')))
                        <img src="{{ public_path('assets/image/logo/ptkmp-logo.png') }}" style="height: 50px;" alt="Logo">
                    @else
                        <div class="logo-text">KMP</div>
                        <div style="font-size: 7pt; font-weight: bold; letter-spacing: 2px;">GROUP</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <table class="table-data">
        <thead>
            <tr>
                <th width="12%">Pelanggan / Tgl</th>
                <th width="12%">Transaksi</th>
                <th width="12%">No. Invoice</th>
                <th width="12%">Jatuh Tempo</th>
                <th>Deskripsi</th>
                <th width="13%" class="text-right">Jumlah</th>
                <th width="13%" class="text-right">Sisa Piutang</th>
            </tr>
        </thead>
        <tbody>
            <tr class="client-row">
                <td colspan="7">{{ $klien->nama }}</td>
            </tr>

            @php
                $subtotalJumlah = 0;
                $subtotalSisa = 0;
            @endphp

            @foreach($data as $row)
            <tr>
                <td>{{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}</td>
                <td>{{ $row['transaksi'] }}</td>
                <td>{{ $row['no_invoice'] }}</td>
                <td>{{ \Carbon\Carbon::parse($row['jatuh_tempo'])->format('d/m/Y') }}</td>
                <td>{{ $row['deskripsi'] }}</td>
                <td class="text-right">{{ number_format($row['jumlah'], 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($row['sisa_piutang'], 2, ',', '.') }}</td>
            </tr>
            @php
                $subtotalJumlah += $row['jumlah'];
                $subtotalSisa += $row['sisa_piutang'];
            @endphp
            @endforeach

            <tr class="total-row">
                <td colspan="5" class="text-right">TOTAL</td>
                <td class="text-right">{{ number_format($subtotalJumlah, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($subtotalSisa, 2, ',', '.') }}</td>
            </tr>

            <tr class="grand-total-row">
                <td colspan="5" class="text-right">GRAND TOTAL</td>
                <td class="text-right">{{ number_format($grandTotal, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($grandSisaPiutang, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <div style="margin-bottom: 5px; font-style: italic;">
            *Menampilkan total dari {{ count($data) }} baris transaksi
        </div>

        <table class="footer-table">
            <tr>
                <td width="60%">
                    Laporan Piutang Pelanggan : <strong>PT. KAMIL MAJU PERSADA</strong><br>
                    Per tanggal {{ $tanggalCetak }}
                </td>
                <td width="40%" class="text-right">
                    Page <span class="page-number"></span> of 1
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
