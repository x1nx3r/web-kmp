<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #000;
            padding: 20px;
        }

        .header-section {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .header-section td {
            vertical-align: middle;
            padding: 10px 0;
        }

        .logo-cell {
            width: 50%;
            text-align: left;
        }

        .invoice-title {
            width: 50%;
            text-align: right;
        }

        .invoice-title h1 {
            color: #1C46F4;
            font-size: 36pt;
            font-weight: bold;
            margin: 0;
            letter-spacing: 3px;
        }

        .company-info {
            font-size: 9pt;
            line-height: 1.6;
            color: #333;
        }

        .customer-section {
            text-align: right;
            font-size: 10pt;
        }

        .customer-label {
            margin-bottom: 5px;
            font-weight: normal;
        }

        .customer-name {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 3px;
        }

        .customer-phone {
            font-size: 9pt;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .info-table th {
            background-color: #2AB672;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 9pt;
            font-weight: bold;
            border: 1px solid #2AB672;
        }

        .info-table td {
            background-color: white;
            padding: 10px;
            border: 1px solid #ddd;
            font-size: 9pt;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .items-table th {
            background-color: #2AB672;
            color: white;
            padding: 10px 8px;
            font-size: 9pt;
            font-weight: bold;
            border: 1px solid #2AB672;
        }

        .items-table td {
            padding: 10px 8px;
            border: 1px solid #ddd;
            font-size: 9pt;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .summary-section {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .summary-spacer {
            width: 50%;
        }

        .summary-content {
            width: 50%;
            vertical-align: top;
        }

        .summary-row {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        .summary-row td {
            padding: 8px 10px;
            font-size: 10pt;
        }

        .summary-label {
            text-align: right;
            font-weight: bold;
            width: 50%;
        }

        .summary-value {
            text-align: right;
            width: 50%;
        }

        .total-row {
            background-color: #1C46F4;
            color: white;
            font-weight: bold;
            margin-top: 5px;
        }

        .total-row td {
            padding: 10px;
            font-size: 11pt;
        }

        .signature-section {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }

        .signature-spacer {
            width: 50%;
        }

        .signature-content {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .company-box {
            background-color: #1C46F4;
            color: white;
            padding: 10px 15px;
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 80px;
            text-align: center;
        }

        .signature-line {
            text-align: center;
        }

        .signature-name {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 3px;
        }

        .signature-title {
            font-size: 9pt;
            text-transform: uppercase;
        }

        .payment-section {
            margin-top: 40px;
            font-size: 9pt;
            line-height: 1.6;
        }

        .payment-section strong {
            font-weight: bold;
        }

        .bank-account {
            color: #1C46F4;
        }

        .footer-thankyou {
            text-align: center;
            margin-top: 50px;
            font-size: 10pt;
            font-weight: bold;
            color: white;
            background-color: #1C46F4;
            padding: 15px;
        }
    </style>
</head>
<body>
    {{-- Header with Logo and Invoice Title --}}
    <table class="header-section">
        <tr>
            <td class="logo-cell">
                <img src="{{ public_path('assets/image/logo/ptkmp-logo.png') }}" alt="KMP Logo" style="width: 150px; height: auto;">
            </td>
            <td class="invoice-title">
                <h1>INVOICE</h1>
            </td>
        </tr>
    </table>

    {{-- Company Information and Customer Section --}}
    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <div class="company-info">
                    <strong>PT KAMIL MAJU PERSADA</strong><br>
                    @if($company && $company->address)
                        {{ $company->address }}<br>
                    @else
                        Pergudangan Bumi Benowo Sukses Sejahtera B-20<br>
                        Romokalisari, Ds. Prambangan, Kec. Kebomas, Gresik<br>
                    @endif
                    @if($company && $company->email)
                        Email : {{ $company->email }}<br>
                    @else
                        Email : kamilmajupersada@gmail.com<br>
                    @endif
                    @if($company && $company->phone)
                        Telp : {{ $company->phone }}
                    @else
                        Telp : 085606614900
                    @endif
                </div>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <div class="customer-section" style="margin: 0;">
                    <div class="customer-label">Kepada Yth:</div>
                    <div class="customer-name">{{ $pengiriman->purchaseOrder->klien->nama ?? $invoice->customer_name }}</div>
                    {{-- @if($pengiriman->purchaseOrder->klien->cabang)
                        <div style="font-size: 10pt; margin-top: 5px;">
                            <strong>Plant:</strong> {{ $pengiriman->purchaseOrder->klien->cabang }}
                        </div>
                    @endif --}}
                    @if($pengiriman->purchaseOrder->klien->alamat_lengkap)
                        <div style="font-size: 9pt; margin-top: 3px; line-height: 1.4;">
                            {{ $pengiriman->purchaseOrder->klien->alamat_lengkap }}
                        </div>
                    @endif
                    @if($pengiriman->purchaseOrder->klien->contactPerson)
                        <div class="customer-phone" style="margin-top: 5px;">Contact: {{ $pengiriman->purchaseOrder->klien->contactPerson->nama }}</div>
                        <div class="customer-phone">Telp: {{ $pengiriman->purchaseOrder->klien->contactPerson->nomor_hp }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

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
                <td>{{ $pengiriman->purchaseOrder->po_number ?? $pengiriman->purchaseOrder->no_order ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->locale('id')->isoFormat('D MMMM YYYY') }}</td>
                <td>{{ \Carbon\Carbon::parse($invoice->due_date)->locale('id')->isoFormat('D MMMM YYYY') }}</td>
            </tr>
        </tbody>
    </table>

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
                @php
                    $hargaJual = $detail->orderDetail->harga_jual ?? 0;
                    $qtyKirim = $detail->qty_kirim ?? 0;
                    $totalHargaItem = $qtyKirim * $hargaJual;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $detail->orderDetail->nama_material_po ?? $detail->purchaseOrderBahanBaku->bahanBakuKlien->nama_bahan_baku ?? $detail->bahanBakuSupplier->nama ?? '-' }}</td>
                    <td class="text-center">{{ number_format($qtyKirim, 2, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($hargaJual, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totalHargaItem, 0, ',', '.') }}</td>
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
                        <td class="summary-value">Rp {{ number_format($invoice->amount_before_refraksi ?? $pengiriman->total_harga_kirim, 0, ',', '.') }}</td>
                    </tr>
                </table>

                <table class="summary-row">
                    <tr>
                        <td class="summary-label">Refraksi</td>
                        <td class="summary-value">
                            @if($invoice->refraksi_value > 0)
                                Rp {{ number_format($invoice->refraksi_amount, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
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
                {{-- <div style="margin-bottom: 10px;">
                    <img src="{{ public_path('assets/image/signature/vitol.jpg') }}" alt="Signature" style="width: 150px; height: auto;">
                </div> --}}
                <div class="signature-line">
                    <div class="signature-name">MAHENDA ABDILLAH KAMIL</div>
                    <div class="signature-title">Direktur</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Payment Information --}}
    <div class="payment-section">
        @if($invoice->bank_name)
            <div style="margin-bottom: 5px;">Pembayaran dapat dilakukan melalui <strong class="bank-account">MSF</strong></div>
            <div>
                Transfer <strong>Via {{ $invoice->bank_name }}</strong><br>
                a/n <strong class="bank-account">{{ $invoice->bank_account_name }}</strong><br>
                No. Rek : <strong class="bank-account">{{ $invoice->bank_account_number }}</strong>
            </div>
        @else
            <div style="margin-bottom: 5px;">Pembayaran dapat dilakukan melalui <strong class="bank-account">MSF</strong></div>
            <div>
                Transfer <strong>Via Mandiri</strong><br>
                a/n <strong class="bank-account">PT KAMIL MAJU PERSADA</strong><br>
                No. Rek : <strong class="bank-account">141-0080998883</strong>
            </div>
        @endif
    </div>

    {{-- Footer Thank You --}}
    <div class="footer-thankyou">
        Thank You For Your Business!
    </div>
</body>
</html>
