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
        'refraksi_type',
        'refraksi_value',
        'refraksi_amount',
        'qty_before_refraksi',
        'qty_after_refraksi',
        'amount_before_refraksi',
        'amount_after_refraksi',
    ];

    protected $casts = [
        'staff_approved_at' => 'datetime',
        'manager_approved_at' => 'datetime',
        'superadmin_approved_at' => 'datetime',
        'refraksi_value' => 'decimal:2',
        'refraksi_amount' => 'decimal:2',
        'qty_before_refraksi' => 'decimal:2',
        'qty_after_refraksi' => 'decimal:2',
        'amount_before_refraksi' => 'decimal:2',
        'amount_after_refraksi' => 'decimal:2',
    ];

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
     * Check if staff can approve
     */
    public function canStaffApprove()
    {
        return $this->status === 'pending' && !$this->staff_approved_at;
    }

    /**
     * Check if manager can approve (final approval)
     */
    public function canManagerApprove()
    {
        return $this->status === 'staff_approved' && $this->staff_approved_at && !$this->manager_approved_at;
    }
}
