<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalPembayaran extends Model
{
    use HasFactory;

    protected $table = 'approval_pembayaran';

    protected $fillable = [
        'pengiriman_id',
        'staff_id',
        'staff_approved_at',
        'manager_id',
        'manager_approved_at',
        'superadmin_id',
        'superadmin_approved_at',
        'status',
        'bukti_pembayaran',
        'catatan_piutang_id',
        'piutang_amount',
        'piutang_notes',
        'refraksi_type',
        'refraksi_value',
        'refraksi_amount',
        'qty_before_refraksi',
        'qty_after_refraksi',
        'amount_before_refraksi',
        'amount_after_refraksi',
        'additional_expenses_total',
        'subtotal',
        'tax_percentage',
        'tax_amount',
        'total_dibayarkan',
    ];

    /**
     * Default PPN nonaktif untuk data pembayaran baru.
     */
    protected $attributes = [
        'tax_percentage' => 0,
        'tax_amount' => 0,
    ];

    protected $casts = [
        'staff_approved_at' => 'datetime',
        'manager_approved_at' => 'datetime',
        'superadmin_approved_at' => 'datetime',
        'piutang_amount' => 'decimal:3',
        'refraksi_value' => 'decimal:3',
        'refraksi_amount' => 'decimal:3',
        'qty_before_refraksi' => 'decimal:3',
        'qty_after_refraksi' => 'decimal:3',
        'amount_before_refraksi' => 'decimal:3',
        'amount_after_refraksi' => 'decimal:3',
        'additional_expenses_total' => 'decimal:3',
        'subtotal' => 'decimal:3',
        'tax_percentage' => 'decimal:3',
        'tax_amount' => 'decimal:3',
        'total_dibayarkan' => 'decimal:3',
    ];

    protected static function booted(): void
    {
        // PPN dihitung dari amount_after_refraksi x tax_percentage (di luar
        // pengeluaran tambahan & potongan piutang). Sama seperti InvoicePenagihan,
        // total_dibayarkan hanya dikoreksi sebesar selisih PPN lama -> baru.
        static::saving(function (ApprovalPembayaran $approval) {
            if (!$approval->isDirty(['amount_after_refraksi', 'tax_percentage'])) return;

            $pct = (float) ($approval->tax_percentage ?? 0);
            $base = (float) ($approval->amount_after_refraksi ?? $approval->amount_before_refraksi ?? 0);
            $newTax = $pct > 0 ? round($base * $pct / 100, 2) : 0;

            $oldTax = $approval->isDirty('tax_amount')
                ? (float) $approval->tax_amount
                : (float) ($approval->getOriginal('tax_amount') ?? 0);

            $approval->tax_amount = $newTax;
            $approval->total_dibayarkan = max(0, (float) ($approval->total_dibayarkan ?? 0) - $oldTax + $newTax);
        });
    }

    /**
     * PPN dianggap aktif hanya jika persentase & nominalnya benar-benar terisi.
     */
    public function hasPpn(): bool
    {
        return (float) $this->tax_percentage > 0 && (float) $this->tax_amount > 0;
    }

    /**
     * Terapkan PPN lalu hitung ulang tax_amount & total_dibayarkan.
     */
    public function applyPpnPercentage(float $pct): void
    {
        $pct = max(0, $pct);
        $base = (float) ($this->amount_after_refraksi ?? $this->amount_before_refraksi ?? 0);
        $newTax = $pct > 0 ? round($base * $pct / 100, 2) : 0;
        $oldTax = (float) ($this->tax_amount ?? 0);

        $this->tax_percentage = $pct;
        $this->tax_amount = $newTax;
        $this->total_dibayarkan = max(0, (float) ($this->total_dibayarkan ?? 0) - $oldTax + $newTax);

        $this->save();
    }

    /**
     * Relasi ke Pengiriman
     */
    public function pengiriman()
    {
        return $this->belongsTo(Pengiriman::class, 'pengiriman_id');
    }

    /**
     * Relasi ke Staff (User)
     */
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Relasi ke Manager (User)
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Relasi ke Superadmin (User)
     */
    public function superadmin()
    {
        return $this->belongsTo(User::class, 'superadmin_id');
    }

    /**
     * Relasi ke Approval History
     */
    public function histories()
    {
        return $this->hasMany(ApprovalHistory::class, 'approval_id')
            ->where('approval_type', 'pembayaran');
    }

    /**
     * Relasi ke Catatan Piutang
     */
    public function catatanPiutang()
    {
        return $this->belongsTo(CatatanPiutang::class, 'catatan_piutang_id');
    }

    /**
     * Relasi ke Approval Pembayaran Expense
     */
    public function expenses()
    {
        return $this->hasMany(ApprovalPembayaranExpense::class, 'approval_pembayaran_id');
    }

    /**
     * Check if any accounting member can approve
     */
    public function canStaffApprove()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if any accounting member can approve
     */
    public function canManagerApprove()
    {
        return $this->status === 'pending';
    }
}
