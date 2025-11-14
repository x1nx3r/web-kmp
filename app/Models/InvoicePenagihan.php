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
     * Generate invoice number
     */
    public static function generateInvoiceNumber()
    {
        $date = Carbon::now();
        $year = $date->format('Y');
        $month = $date->format('m');

        // Get last invoice number for this month
        $lastInvoice = self::whereYear('invoice_date', $year)
            ->whereMonth('invoice_date', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = 1;
        if ($lastInvoice) {
            // Extract sequence from last invoice number
            $parts = explode('/', $lastInvoice->invoice_number);
            $sequence = isset($parts[3]) ? intval($parts[3]) + 1 : 1;
        }

        return sprintf('INV/%s/%s/%04d', $year, $month, $sequence);
    }

    /**
     * Recalculate total amount
     */
    public function recalculateTotal()
    {
        $this->tax_amount = $this->subtotal * ($this->tax_percentage / 100);
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
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
