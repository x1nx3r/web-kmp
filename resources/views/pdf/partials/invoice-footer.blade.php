@php
    $customerName = $freshKlien->nama ?? $invoice->customer_name ?? '';
    $isSreeyaSewu = $customerName === 'PT Sreeya Sewu Indonesia';
@endphp

<div class="payment-section">
    @if($invoice->bank_name)
        <div style="margin-bottom: 5px;">
            Pembayaran dapat dilakukan melalui @if($isSreeyaSewu)<strong class="bank-account">MSF</strong>@endif
        </div>
        <div>
            Transfer <strong>Via {{ $invoice->bank_name }}</strong><br>
            a/n <strong class="bank-account">{{ $invoice->bank_account_name }}</strong><br>
            No. Rek : <strong class="bank-account">{{ $invoice->bank_account_number }}</strong>
        </div>
    @else
        <div style="margin-bottom: 5px;">
            Pembayaran dapat dilakukan melalui @if($isSreeyaSewu)<strong class="bank-account">MSF</strong>@endif
        </div>
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
