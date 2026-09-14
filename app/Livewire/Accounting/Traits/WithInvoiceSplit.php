<?php

namespace App\Livewire\Accounting\Traits;

use App\Models\InvoicePenagihan;
use App\Models\ApprovalPenagihan as ApprovalPenagihanModel;
use App\Models\CompanySetting;
use App\Models\Pengiriman;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait WithInvoiceSplit
{
    /**
     * Cek apakah invoice ini layak dipisah kembali menjadi beberapa invoice.
     * Syarat: gabungan (>1 pengiriman), punya data items[], dan belum ada pembayaran masuk.
     */
    public function canSplitInvoice($invoice): bool
    {
        if (!$invoice) return false;
        if (!$invoice->relationLoaded('pengirimans')) $invoice->load('pengirimans');
        if ($invoice->pengirimans->count() <= 1) return false;
        if (($invoice->payment_status ?? 'unpaid') !== 'unpaid') return false;
        if (empty($invoice->items)) return false;
        return true;
    }

    /**
     * Pisahkan invoice gabungan menjadi beberapa invoice baru,
     * satu invoice baru per item (per pengiriman) berdasarkan data
     * kolom `items` invoice yang aktif sekarang (bukan invoice lama sebelum merge),
     * supaya angka yang dipakai adalah angka terbaru (termasuk kalau sudah ada edit
     * refraksi/expense/total harga jual setelah merge).
     *
     * Tabel `pengiriman` hanya kolom invoice_penagihan_id yang berubah (dipindah
     * ke invoice baru), tidak ada kolom lain yang disentuh.
     *
     * Tidak menambah status baru ke skema: invoice & approval lama di-reuse
     * status 'digabung' yang sudah ada (sama seperti hasil merge), supaya
     * otomatis hilang dari listing tanpa perlu migration. Keterangan "kenapa
     * nonaktif" (dari merge atau dari split) dicatat di history/notes.
     */
    public function splitInvoice($invoiceId)
    {
        if (!$this->ensureCanManage()) return;

        $invoice = InvoicePenagihan::with('pengirimans')->findOrFail($invoiceId);

        if (!$this->canSplitInvoice($invoice)) {
            session()->flash('error', 'Invoice ini tidak bisa dipisah (bukan gabungan, sudah ada pembayaran, atau data item kosong).');
            return;
        }

        $approval = ApprovalPenagihanModel::where('invoice_id', $invoice->id)
            ->whereIn('status', ['pending', 'completed'])
            ->first();

        DB::beginTransaction();
        try {
            $companySetting = CompanySetting::getSettings();
            $shipmentsByPosition = $invoice->pengirimans->values();
            $newInvoiceNumbers = [];

            foreach ($invoice->items as $index => $item) {
                // Cocokkan item ke shipment: pakai pengiriman_id eksplisit kalau ada
                // (invoice gabungan yang dibuat setelah perbaikan prepareInvoiceItems()),
                // fallback ke posisi index untuk invoice gabungan lama.
                $pengiriman = isset($item['pengiriman_id'])
                    ? Pengiriman::find($item['pengiriman_id'])
                    : ($shipmentsByPosition[$index] ?? null);

                if (!$pengiriman) {
                    throw new \Exception("Tidak bisa menemukan data pengiriman untuk item #{$index} ({$item['item_name']})");
                }

                $itemAmount    = floatval($item['amount'] ?? 0);
                $refraksiAmt   = floatval($item['refraksi_amount'] ?? 0);
                $expensesList  = $item['expenses'] ?? [];
                $expensesTotal = collect($expensesList)->sum('amount');

                $qtyTotal  = array_sum(array_column($item['details'] ?? [], 'qty'));
                $subtotal  = $itemAmount - $refraksiAmt;
                $withExp   = $subtotal + $expensesTotal;
                $taxAmount = $withExp * (floatval($companySetting->tax_percentage) / 100);

                // FIX: kolom `items` di invoice_penagihan adalah NOT NULL tanpa default,
                // jadi WAJIB diisi saat create. Invoice hasil split hanya punya 1
                // pengiriman, jadi items-nya adalah array berisi 1 elemen: item itu
                // sendiri (dicopy dari item invoice gabungan asal), dengan
                // pengiriman_id dipastikan ada (invoice gabungan lama sebelum
                // perbaikan prepareInvoiceItems() belum tentu punya field ini di item).
                $newItem = $item;
                $newItem['pengiriman_id'] = $pengiriman->id;
                // Selaraskan angka item dengan angka invoice baru (item sudah
                // merefleksikan data invoice gabungan saat ini, tidak perlu diubah
                // lagi selain memastikan pengiriman_id-nya).

                $newInvoice = InvoicePenagihan::create([
                    'pengiriman_id'   => $pengiriman->id,
                    'invoice_number'  => InvoicePenagihan::generateInvoiceNumber(),
                    'invoice_date'    => now(),
                    'due_date'        => now()->addDays($companySetting->invoice_due_days),
                    'payment_status'  => 'unpaid',
                    'created_by'      => Auth::id(),

                    'customer_name'       => $invoice->customer_name,
                    'customer_address'    => $invoice->customer_address,
                    'customer_phone'      => $invoice->customer_phone,
                    'customer_email'      => $invoice->customer_email,
                    'bank_name'           => $invoice->bank_name,
                    'bank_account_number' => $invoice->bank_account_number,
                    'bank_account_name'   => $invoice->bank_account_name,

                    'qty_before_refraksi'       => $qtyTotal,
                    // NOTE: disederhanakan (belum dikurangi refraksi qty%).
                    // TODO: hitung akurat mengikuti rumus di WithInvoiceCalculations::updateRefraksiPerItem()
                    'qty_after_refraksi'        => $qtyTotal,
                    'amount_before_refraksi'    => $itemAmount,
                    'amount_after_refraksi'     => $subtotal,
                    'refraksi_type'             => $item['refraksi_type'] ?? null,
                    'refraksi_value'            => $item['refraksi_value'] ?? 0,
                    'refraksi_amount'           => $refraksiAmt,
                    'additional_expenses_total' => $expensesTotal,
                    'subtotal'                  => $withExp,
                    'tax_percentage'            => $companySetting->tax_percentage,
                    'tax_amount'                => $taxAmount,
                    'discount_amount'           => 0,
                    'total_amount'              => $withExp + $taxAmount,

                    // <-- INI YANG SEBELUMNYA HILANG DAN MENYEBABKAN ERROR SQL:
                    // "Field 'items' doesn't have a default value"
                    'items' => [$newItem],

                    'notes' => 'Hasil pemisahan dari invoice ' . $invoice->invoice_number,
                ]);

                foreach ($expensesList as $e) {
                    $newInvoice->expenses()->create([
                        'type'   => $e['type'],
                        'amount' => $e['amount'],
                    ]);
                }

                // Satu-satunya perubahan ke tabel pengiriman: pindah invoice_penagihan_id
                $pengiriman->update(['invoice_penagihan_id' => $newInvoice->id]);

                // TODO KEPUTUSAN BISNIS (belum dikonfirmasi ke klien):
                // apakah approval hasil split ikut status invoice asal (di bawah ini),
                // atau selalu 'pending' supaya diapprove ulang manual?
                $newApprovalStatus = ($approval && $approval->status === 'completed') ? 'completed' : 'pending';

                // Dibuat dulu sebelum logInvoiceHistory, karena approval_id di tabel
                // approval_history adalah NOT NULL -- tidak bisa diisi null.
                $newApproval = ApprovalPenagihanModel::create([
                    'invoice_id'          => $newInvoice->id,
                    'pengiriman_id'       => $pengiriman->id,
                    'status'              => $newApprovalStatus,
                    'staff_id'            => $newApprovalStatus === 'completed' ? ($approval->staff_id ?? null) : null,
                    'manager_id'          => $newApprovalStatus === 'completed' ? ($approval->manager_id ?? null) : null,
                    'staff_approved_at'   => $newApprovalStatus === 'completed' ? ($approval->staff_approved_at ?? null) : null,
                    'manager_approved_at' => $newApprovalStatus === 'completed' ? ($approval->manager_approved_at ?? null) : null,
                ]);

                $this->logInvoiceHistory(
                    $newApproval->id,
                    $pengiriman->id,
                    $newInvoice->id,
                    'edited',
                    'Invoice dibuat dari pemisahan invoice ' . $invoice->invoice_number
                );

                $newInvoiceNumbers[] = $newInvoice->invoice_number;
            }

            // Reuse status 'digabung' (tanpa migration baru) untuk menandai invoice/approval
            // lama sebagai nonaktif setelah displit. Kejelasan "kenapa nonaktif" ada di notes/history.
            $invoice->update(['status' => 'digabung']);
            if ($approval) {
                $approval->update(['status' => 'digabung']);
                $this->logInvoiceHistory(
                    $approval->id,
                    $approval->pengiriman_id,
                    $invoice->id,
                    'edited',
                    'Invoice DIPISAH (bukan digabung) menjadi ' . count($newInvoiceNumbers) . ' invoice: ' . implode(', ', $newInvoiceNumbers)
                );
            }

            DB::commit();
            session()->flash('message', 'Invoice berhasil dipisah menjadi ' . count($newInvoiceNumbers) . ' invoice: ' . implode(', ', $newInvoiceNumbers));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Split Invoice Error: ' . $e->getMessage());
            session()->flash('error', 'Gagal memisah invoice: ' . $e->getMessage());
        }
    }
}