<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalPenagihan extends Model
{
    use HasFactory;

    protected $table = 'approval_penagihan';

    protected $fillable = [
        'invoice_id',
        'pengiriman_id',
        'staff_id',
        'staff_approved_at',
        'manager_id',
        'manager_approved_at',
        'superadmin_id',
        'superadmin_approved_at',
        'status',
    ];

    protected $casts = [
        'staff_approved_at' => 'datetime',
        'manager_approved_at' => 'datetime',
        'superadmin_approved_at' => 'datetime',
    ];

    /**
     * Relasi ke Invoice Penagihan
     */
    public function invoice()
    {
        return $this->belongsTo(InvoicePenagihan::class, 'invoice_id');
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
            ->where('approval_type', 'penagihan');
    }

    /**
     * Check if staff can approve
     */
    public function canStaffApprove()
    {
        return $this->status === 'pending' && !$this->staff_approved_at;
    }

    /**
     * Check if manager can approve
     */
    public function canManagerApprove()
    {
        return $this->status === 'staff_approved' && $this->staff_approved_at && !$this->manager_approved_at;
    }

    /**
     * Check if superadmin can approve
     */
    public function canSuperadminApprove()
    {
        return $this->status === 'manager_approved' && $this->manager_approved_at && !$this->superadmin_approved_at;
    }
}
