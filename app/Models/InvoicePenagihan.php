<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InvoicePenagihan extends Model
{
    use HasFactory;

    protected $table = 'invoice_penagihan';

    protected $fillable = [
        'pengiriman_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'customer_name',
        'customer_address',
        'customer_phone',
        'customer_email',
        'items',
        'refraksi_type',
        'refraksi_value',
        'refraksi_amount',
        'qty_before_refraksi',
        'qty_after_refraksi',
        'amount_before_refraksi',
        'amount_after_refraksi',
        'subtotal',
        'additional_expenses_total',
        'tax_percentage',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'status',
        'notes',
        'payment_status',
        'paid_at',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'items' => 'array',
        'refraksi_value' => 'decimal:3',
        'refraksi_amount' => 'decimal:3',
        'qty_before_refraksi' => 'decimal:3',
        'qty_after_refraksi' => 'decimal:3',
        'amount_before_refraksi' => 'decimal:3',
        'amount_after_refraksi' => 'decimal:3',
        'subtotal' => 'decimal:3',
        'additional_expenses_total' => 'decimal:3',
        'tax_percentage' => 'decimal:3',
        'tax_amount' => 'decimal:3',
        'discount_amount' => 'decimal:3',
        'total_amount' => 'decimal:3',
        'paid_at' => 'datetime',
    ];

    /**
     * Relasi ke Pengiriman
     */
    public function pengiriman()
    {
        return $this->belongsTo(Pengiriman::class, 'pengiriman_id');
    }

    /**
     * Relasi ke Pengiriman yang di-merge
     */
    public function pengirimans()
    {
        return $this->hasMany(Pengiriman::class, 'invoice_penagihan_id');
    }

    /**
     * Relasi ke User (Created By)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke Approval Penagihan
     */
    public function approvalPenagihan()
    {
        return $this->hasOne(ApprovalPenagihan::class, 'invoice_id');
    }

    /**
     * Relasi ke Pembayaran Piutang Pabrik
     */
    public function pembayaranPabrik()
    {
        return $this->hasMany(PembayaranPiutangPabrik::class, 'invoice_penagihan_id');
    }

    /**
     * Relasi ke Biaya Tambahan
     */
    public function expenses()
    {
        return $this->hasMany(InvoicePenagihanExpense::class, 'invoice_penagihan_id');
    }

    /**
     * Generate invoice number with duplicate prevention
     *
     * @param int $maxRetries Maximum retry attempts if duplicate found
     * @return string Generated invoice number
     * @throws \Exception if unable to generate unique number after max retries
     */
    public static function generateInvoiceNumber(int $maxRetries = 5): string
    {
        $date = Carbon::now();
        $yearMonth = $date->format('Ym');

        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            // Get last invoice number for this month with locking
            $lastInvoice = self::whereYear('invoice_date', $date->year)
                ->whereMonth('invoice_date', $date->month)
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            $sequence = 1;
            if ($lastInvoice && $lastInvoice->invoice_number) {
                // Extract sequence from last invoice number (format: INV-YYYYMM-XXXX)
                $parts = explode('-', $lastInvoice->invoice_number);
                if (count($parts) >= 3) {
                    $sequence = intval($parts[2]) + 1;
                }
            }

            $invoiceNumber = sprintf('INV-%s-%04d', $yearMonth, $sequence);

            // Check if this number already exists
            $exists = self::where('invoice_number', $invoiceNumber)->exists();

            if (!$exists) {
                return $invoiceNumber;
            }

            // If exists, try next sequence
            $sequence++;
        }

        // If all retries failed, use timestamp for uniqueness
        return sprintf('INV-%s-%04d-%s', $yearMonth, $sequence, substr(uniqid(), -4));
    }

    /**
     * Recalculate total amount
     */
    public function recalculateTotal()
    {
        $this->discount_amount = $this->discount_amount ?? 0;

        $this->total_amount = ($this->subtotal ?? 0) + ($this->tax_amount ?? 0) - $this->discount_amount;
        $this->save();
    }

    /**
     * Check if overdue
     */
    public function isOverdue()
    {
        return $this->payment_status === 'unpaid' && Carbon::now()->gt($this->due_date);
    }

    /**
     * Recalculate invoice numbers (qty/amount/refraksi/subtotal/total) from
     * the live condition of the related Pengiriman(s).
     *
     * Idempotent: calling this repeatedly with the same underlying qty/harga
     * data will always produce the same result.
     *
     * Mode SINGLE (items kosong atau skema lama description/quantity/unit_price/...):
     *   qty_before_refraksi & amount_before_refraksi dihitung dari SUM pengirimanDetails
     *   milik $this->pengiriman. Kolom items[] TIDAK disentuh.
     *
     * Mode MERGED (items skema baru item_name/amount/details/expenses/...):
     *   Hanya elemen items[] yang berkorespondensi (via index array) dengan
     *   $pengiriman yang di-update. Elemen lain tidak disentuh. Field level-invoice
     *   di-re-sum dari SELURUH items[] setelah item yang direvisi diupdate.
     *
     * Field yang selalu dipertahankan (bukan turunan qty): refraksi_type,
     * refraksi_value, additional_expenses_total, tax_percentage, tax_amount,
     * discount_amount, dan data expenses.
     *
     * @param Pengiriman $pengiriman Pengiriman yang baru saja direvisi/submit ulang.
     */
    public function recalculateFromShipments(Pengiriman $pengiriman): void
    {
        $items = $this->items ?? [];

        if (empty($items) || $this->isLegacyItemsSchema($items)) {
            $this->recalculateSingleMode();
            return;
        }

        if ($this->isMergedItemsSchema($items)) {
            $this->recalculateMergedMode($pengiriman, $items);
            return;
        }

        Log::warning("InvoicePenagihan#{$this->id}: skema items[] tidak dikenali, skip recalculateFromShipments()", [
            'invoice_id' => $this->id,
            'pengiriman_id' => $pengiriman->id,
        ]);
    }

    /**
     * Skema lama: description/quantity/unit_price/refraksi_kg/total
     */
    private function isLegacyItemsSchema(array $items): bool
    {
        $first = $items[0] ?? null;
        if (!is_array($first)) return false;

        return array_key_exists('description', $first)
            && array_key_exists('quantity', $first)
            && array_key_exists('unit_price', $first);
    }

    /**
     * Skema baru (merged): item_name/amount/details/expenses/refraksi_type/refraksi_value/refraksi_amount
     */
    private function isMergedItemsSchema(array $items): bool
    {
        $first = $items[0] ?? null;
        if (!is_array($first)) return false;

        return array_key_exists('item_name', $first) && array_key_exists('amount', $first);
    }

    /**
     * Mode SINGLE: recompute langsung dari SUM pengirimanDetails milik $this->pengiriman.
     */
    private function recalculateSingleMode(): void
    {
        $pengiriman = $this->pengiriman ?? $this->pengiriman()->first();

        if (!$pengiriman) {
            Log::warning("InvoicePenagihan#{$this->id}: relasi pengiriman tunggal tidak ditemukan, skip recalculate single mode");
            return;
        }

        $pengiriman->loadMissing(['pengirimanDetails.purchaseOrderBahanBaku', 'pengirimanDetails.orderDetail']);

        $qtyBefore = 0;
        $amountBefore = 0;

        foreach ($pengiriman->pengirimanDetails as $detail) {
            $orderDetail = $detail->purchaseOrderBahanBaku ?? $detail->orderDetail;
            $hargaJual = floatval($orderDetail->harga_jual ?? 0);

            $qtyBefore += floatval($detail->qty_kirim);
            $amountBefore += floatval($detail->qty_kirim) * $hargaJual;
        }

        $this->applyLevelRefraksiAndSave($qtyBefore, $amountBefore);
    }

    /**
     * Mode MERGED: update HANYA item[] yang berkorespondensi dengan $pengiriman (via index array),
     * lalu re-sum field level-invoice dari SELURUH items[].
     */
    private function recalculateMergedMode(Pengiriman $pengiriman, array $items): void
    {
        $this->loadMissing(['pengirimans.pengirimanDetails.purchaseOrderBahanBaku', 'pengirimans.pengirimanDetails.orderDetail']);
        $shipments = $this->pengirimans->values();

        $index = $shipments->search(fn($s) => $s->id === $pengiriman->id);

        if ($index === false || !array_key_exists($index, $items)) {
            Log::warning("InvoicePenagihan#{$this->id}: Pengiriman #{$pengiriman->id} tidak ditemukan/berkorespondensi di items[], skip recalculate merged mode");
            return;
        }

        // 1) Recompute HANYA elemen item yang direvisi
        $revisedShipment = $shipments[$index];
        $qty = 0;
        $amount = 0;

        foreach ($revisedShipment->pengirimanDetails as $detail) {
            $orderDetail = $detail->purchaseOrderBahanBaku ?? $detail->orderDetail;
            $hargaJual = floatval($orderDetail->harga_jual ?? 0);

            $qty += floatval($detail->qty_kirim);
            $amount += floatval($detail->qty_kirim) * $hargaJual;
        }

        $items[$index]['amount'] = $amount;
        $items[$index]['refraksi_amount'] = $this->calculateItemRefraksiAmount(
            $items[$index]['refraksi_type'] ?? null,
            floatval($items[$index]['refraksi_value'] ?? 0),
            $qty,
            $amount
        );

        // 2) Re-sum field level-invoice dari SELURUH items[] (elemen lain tidak diubah, hanya dibaca)
        $totalQtyBefore = 0;
        $totalAmountBefore = 0;
        $totalRefraksiAmount = 0;
        $totalRefraksiQty = 0;

        foreach ($items as $i => $item) {
            $itemShipment = $shipments[$i] ?? null;
            $itemQty = $itemShipment ? floatval($itemShipment->total_qty_kirim ?? 0) : 0;

            $itemAmount = floatval($item['amount'] ?? 0);
            $itemRefraksiAmount = floatval($item['refraksi_amount'] ?? 0);
            $itemRefraksiType = $item['refraksi_type'] ?? null;
            $itemRefraksiValue = floatval($item['refraksi_value'] ?? 0);

            $totalQtyBefore += $itemQty;
            $totalAmountBefore += $itemAmount;
            $totalRefraksiAmount += $itemRefraksiAmount;

            if ($itemRefraksiType === 'qty' && $itemRefraksiValue > 0) {
                $totalRefraksiQty += $itemQty * ($itemRefraksiValue / 100);
            }
        }

        $amountAfterRefraksi = $totalAmountBefore - $totalRefraksiAmount;
        $subtotal = $amountAfterRefraksi;
        $additionalExpenses = floatval($this->additional_expenses_total ?? 0);
        $taxAmount = floatval($this->tax_amount ?? 0);
        $discountAmount = floatval($this->discount_amount ?? 0);
        $totalAmount = max(0, $subtotal + $additionalExpenses + $taxAmount - $discountAmount);

        $this->update([
            'items' => $items,
            'qty_before_refraksi' => $totalQtyBefore,
            'amount_before_refraksi' => $totalAmountBefore,
            'refraksi_amount' => $totalRefraksiAmount,
            'qty_after_refraksi' => $totalQtyBefore - $totalRefraksiQty,
            'amount_after_refraksi' => $amountAfterRefraksi,
            'subtotal' => $subtotal,
            'total_amount' => $totalAmount,
        ]);
    }

    /**
     * Hitung refraksi_amount untuk satu item (mode merged), berdasarkan tipe/value item itu sendiri.
     */
    private function calculateItemRefraksiAmount(?string $type, float $value, float $qty, float $amount): float
    {
        if ($value <= 0 || !$type) {
            return 0;
        }

        if ($type === 'qty') {
            $refraksiQty = $qty * ($value / 100);
            $hargaPerKg = $qty > 0 ? $amount / $qty : 0;
            return $refraksiQty * $hargaPerKg;
        }

        if ($type === 'rupiah') {
            return $value * $qty;
        }

        return $value; // lainnya
    }

    /**
     * Terapkan refraksi_type/refraksi_value invoice (dipertahankan) ke qty/amount baru,
     * lalu simpan field turunan (mode single).
     */
    private function applyLevelRefraksiAndSave(float $qtyBefore, float $amountBefore): void
    {
        $refraksiType = $this->refraksi_type;
        $refraksiValue = floatval($this->refraksi_value ?? 0);

        $refraksiAmount = 0;
        $qtyAfter = $qtyBefore;
        $amountAfter = $amountBefore;

        if ($refraksiValue > 0 && $refraksiType) {
            if ($refraksiType === 'qty') {
                $refraksiQty = $qtyBefore * ($refraksiValue / 100);
                $qtyAfter = $qtyBefore - $refraksiQty;
                $hargaPerKg = $qtyBefore > 0 ? $amountBefore / $qtyBefore : 0;
                $refraksiAmount = $refraksiQty * $hargaPerKg;
            } elseif ($refraksiType === 'rupiah') {
                $refraksiAmount = $refraksiValue * $qtyBefore;
            } else { // lainnya
                $refraksiAmount = $refraksiValue;
            }
            $amountAfter = $amountBefore - $refraksiAmount;
        }

        $subtotal = $amountAfter;
        $additionalExpenses = floatval($this->additional_expenses_total ?? 0);
        $taxAmount = floatval($this->tax_amount ?? 0);
        $discountAmount = floatval($this->discount_amount ?? 0);
        $totalAmount = max(0, $subtotal + $additionalExpenses + $taxAmount - $discountAmount);

        $this->update([
            'qty_before_refraksi' => $qtyBefore,
            'amount_before_refraksi' => $amountBefore,
            'refraksi_amount' => $refraksiAmount,
            'qty_after_refraksi' => $qtyAfter,
            'amount_after_refraksi' => $amountAfter,
            'subtotal' => $subtotal,
            'total_amount' => $totalAmount,
        ]);
    }
}