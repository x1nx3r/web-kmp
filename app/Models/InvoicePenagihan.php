<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'refraksi_value' => 'decimal:2',
        'refraksi_amount' => 'decimal:2',
        'qty_before_refraksi' => 'decimal:2',
        'qty_after_refraksi' => 'decimal:2',
        'amount_before_refraksi' => 'decimal:2',
        'amount_after_refraksi' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
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
        $this->tax_amount = 0;
        $this->tax_percentage = 0;
        $this->total_amount = $this->subtotal - $this->discount_amount;
        $this->save();
    }

    /**
     * Check if overdue
     */
    public function isOverdue()
    {
        return $this->payment_status === 'unpaid' && Carbon::now()->gt($this->due_date);
    }
}
