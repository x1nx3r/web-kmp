<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        @page {
            margin: 20mm 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 10px;
            color: #000;
            line-height: 1.4;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Header Section */
        .header-section {
            width: 100%;
            margin-bottom: 15px;
        }

        .logo-cell {
            width: 150px;
            vertical-align: top;
        }

        .logo-kmp {
            font-size: 45px;
            font-weight: bold;
            line-height: 0.9;
            letter-spacing: -2px;
            margin-bottom: 2px;
        }

        .logo-kmp .k { color: #4169E1; }
        .logo-kmp .m { color: #00CED1; }
        .logo-kmp .p { color: #32CD32; }

        .logo-subtitle {
            font-size: 7px;
            color: #DC143C;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .invoice-title {
            text-align: right;
            vertical-align: top;
        }

        .invoice-title h1 {
            font-size: 45px;
            color: #4169E1;
            font-weight: bold;
            letter-spacing: 8px;
            margin: 0;
        }

        /* Company Info */
        .company-info {
            font-size: 9px;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .company-info strong {
            font-weight: bold;
        }

        /* Info Table */
        .info-table {
            margin: 15px 0;
        }

        .info-table th {
            background-color: #5DBAAF;
            color: white;
            padding: 8px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            border-right: 2px solid white;
            text-align: left;
        }

        .info-table th:last-child {
            border-right: none;
        }

        .info-table td {
            padding: 8px;
            font-size: 9px;
            border: 1px solid #5DBAAF;
            border-top: none;
        }

        /* Customer Section */
        .customer-section {
            margin: 15px 0;
        }

        .customer-label {
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .customer-name {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .customer-phone {
            font-size: 9px;
        }

        /* Items Table */
        .items-table {
            margin: 15px 0;
        }

        .items-table thead th {
            background-color: #5DBAAF;
            color: white;
            padding: 8px 6px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid white;
        }

        .items-table tbody td {
            padding: 6px;
            font-size: 9px;
            border: 1px solid #ddd;
        }

        .items-table tbody tr:last-child td {
            border-bottom: 1px solid #5DBAAF;
        }

        .items-table tbody td:first-child {
            border-left: 1px solid #5DBAAF;
        }

        .items-table tbody td:last-child {
            border-right: 1px solid #5DBAAF;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* Summary Section */
        .summary-section {
            width: 100%;
            margin-top: 20px;
        }

        .summary-spacer {
            width: 60%;
        }

        .summary-content {
            width: 40%;
            vertical-align: top;
        }

        .summary-row {
            width: 100%;
            margin-bottom: 8px;
        }

        .summary-row td {
            padding: 5px 8px;
            font-size: 9px;
            font-weight: 600;
        }

        .summary-label {
            text-transform: uppercase;
            width: 60%;
        }

        .summary-value {
            text-align: right;
            width: 40%;
        }

        .total-row td {
            background-color: #5DBAAF;
            color: white;
            font-weight: bold;
            font-size: 10px;
            padding: 10px 8px;
        }

        /* Signature Section */
        .signature-section {
            width: 100%;
            margin-top: 50px;
        }

        .signature-spacer {
            width: 60%;
        }

        .signature-content {
            width: 40%;
            text-align: center;
            vertical-align: top;
        }

        .company-box {
            display: inline-block;
            background-color: #4169E1;
            color: white;
            padding: 10px 40px;
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 70px;
        }

        .signature-line {
            border-top: 1.5px solid #000;
            padding-top: 5px;
            margin: 0 auto;
            width: 80%;
        }

        .signature-name {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .signature-title {
            font-size: 9px;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* Payment Info */
        .payment-section {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #ccc;
            font-size: 9px;
            line-height: 1.6;
        }

        .payment-section strong {
            font-weight: bold;
        }

        .bank-account {
            color: #4169E1;
            font-weight: bold;
        }
    </style>
</head>
<body>
    {{-- Header with Logo and Invoice Title --}}
    <table class="header-section">
        <tr>
            <td class="logo-cell">
                <div class="logo-kmp">
                    <span class="k">K</span><span class="m">M</span><span class="p">P</span>
                </div>
                <div class="logo-subtitle">PT. KAMIL MAJU PERSADA</div>
            </td>
            <td class="invoice-title">
                <h1>INVOICE</h1>
            </td>
        </tr>
    </table>

    {{-- Company Information --}}
    <div class="company-info">
        <strong>PT KAMIL MAJU PERSADA</strong><br>
        @if($company && $company->address)
            {{ $company->address }}<br>
        @else
            Pengadangan Bumi Makmur Sukses Sejahtera B-20<br>
            Ronto Kalisari, Ds. Prambongan, Kec. Katonas, Gresik<br>
        @endif
        @if($company && $company->email)
            Email : {{ $company->email }}<br>
        @else
            Email : kamilmajupersada@gmail.com<br>
        @endif
        @if($company && $company->phone)
            Telp : {{ $company->phone }}
        @else
            Telp : 085606614300
        @endif
    </div>

    {{-- Invoice Information Table --}}
    <table class="info-table">
        <thead>
            <tr>
                <th style="width: 25%;">NO. INVOICE</th>
                <th style="width: 25%;">NO. PO</th>
                <th style="width: 25%;">TANGGAL INVOICE</th>
                <th style="width: 25%;">JATUH TEMPO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $invoice->invoice_number }}</td>
                <td>{{ $pengiriman->purchaseOrder->no_po ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->locale('id')->isoFormat('D MMMM YYYY') }}</td>
                <td>{{ \Carbon\Carbon::parse($invoice->due_date)->locale('id')->isoFormat('D MMMM YYYY') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Customer Information --}}
    <div class="customer-section">
        <div class="customer-label">Kepada Yth:</div>
        <div class="customer-name">{{ $invoice->customer_name }}</div>
        @if($invoice->customer_phone)
            <div class="customer-phone">Telp : {{ $invoice->customer_phone }}</div>
        @endif
    </div>

    {{-- Items Table --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">NO</th>
                <th style="width: 40%;">DESKRIPSI</th>
                <th style="width: 15%;" class="text-center">QTY PER KG</th>
                <th style="width: 18%;" class="text-right">HARGA SATUAN<br>PER-KG</th>
                <th style="width: 22%;" class="text-right">TOTAL HARGA</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pengiriman->details as $index => $detail)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $detail->bahanBakuKlien->nama_bahan_baku }}</td>
                    <td class="text-center">{{ number_format($detail->qty_kirim, 2, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($detail->harga_kirim, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($detail->total_harga, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 15px; color: #999;">Detail pengiriman tidak tersedia</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Summary Section --}}
    <table class="summary-section">
        <tr>
            <td class="summary-spacer"></td>
            <td class="summary-content">
                <table class="summary-row">
                    <tr>
                        <td class="summary-label">Total Harga</td>
                        <td class="summary-value">Rp {{ number_format($pengiriman->total_harga_kirim, 0, ',', '.') }}</td>
                    </tr>
                </table>

                @if($invoice->refraksi_value > 0)
                    <table class="summary-row">
                        <tr>
                            <td class="summary-label">Refraksi</td>
                            <td class="summary-value">Rp {{ number_format($invoice->refraksi_amount, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                @endif

                <table class="summary-row">
                    <tr>
                        <td class="summary-label">Biaya Kirim</td>
                        <td class="summary-value">Rp {{ number_format($invoice->subtotal - $pengiriman->total_harga_kirim + ($invoice->refraksi_amount ?? 0), 0, ',', '.') }}</td>
                    </tr>
                </table>

                <table class="summary-row total-row">
                    <tr>
                        <td class="summary-label">Total Tagihan</td>
                        <td class="summary-value">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Signature Section --}}
    <table class="signature-section">
        <tr>
            <td class="signature-spacer"></td>
            <td class="signature-content">
                <div class="company-box">PT KAMIL MAJU PERSADA</div>
                <div class="signature-line">
                    <div class="signature-name">
                        @if($approval->manager)
                            {{ strtoupper($approval->manager->nama) }}
                        @else
                            MAHENDA ABDILLAH KAMIL
                        @endif
                    </div>
                    <div class="signature-title">Direktur</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Payment Information --}}
    <div class="payment-section">
        <div style="margin-bottom: 5px;">Pembayaran dapat dilakukan melalui <strong>MSF</strong></div>
        <div>
            Transfer <strong>Via Mandiri</strong><br>
            a/n <strong class="bank-account">
                @if($company && $company->bank_account_name)
                    {{ strtoupper($company->bank_account_name) }}
                @else
                    PT KAMIL MAJU PERSADA
                @endif
            </strong><br>
            No. Rek : <strong class="bank-account">
                @if($company && $company->bank_account_number)
                    {{ $company->bank_account_number }}
                @else
                    141-00809998883
                @endif
            </strong>
        </div>
    </div>
</body>
</html>
