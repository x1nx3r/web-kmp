<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalPembayaran extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "approval_pembayaran";

    protected $fillable = [
        "pengiriman_id",
        "staff_id",
        "staff_approved_at",
        "manager_id",
        "manager_approved_at",
        "superadmin_id",
        "superadmin_approved_at",
        "status",
        "bukti_pembayaran",
        "catatan_piutang_id",
        "piutang_amount",
        "piutang_notes",
        "refraksi_type",
        "refraksi_value",
        "refraksi_amount",
        "qty_before_refraksi",
        "qty_after_refraksi",
        "amount_before_refraksi",
        "amount_after_refraksi",
    ];

    protected $casts = [
        "staff_approved_at" => "datetime",
        "manager_approved_at" => "datetime",
        "superadmin_approved_at" => "datetime",
        "piutang_amount" => "decimal:2",
        "refraksi_value" => "decimal:2",
        "refraksi_amount" => "decimal:2",
        "qty_before_refraksi" => "decimal:2",
        "qty_after_refraksi" => "decimal:2",
        "amount_before_refraksi" => "decimal:2",
        "amount_after_refraksi" => "decimal:2",
    ];

    /**
     * Relasi ke Pengiriman
     */
    public function pengiriman()
    {
        return $this->belongsTo(Pengiriman::class, "pengiriman_id");
    }

    /**
     * Relasi ke Staff (User)
     */
    public function staff()
    {
        return $this->belongsTo(User::class, "staff_id");
    }

    /**
     * Relasi ke Manager (User)
     */
    public function manager()
    {
        return $this->belongsTo(User::class, "manager_id");
    }

    /**
     * Relasi ke Superadmin (User)
     */
    public function superadmin()
    {
        return $this->belongsTo(User::class, "superadmin_id");
    }

    /**
     * Relasi ke Approval History
     */
    public function histories()
    {
        return $this->hasMany(ApprovalHistory::class, "approval_id")->where(
            "approval_type",
            "pembayaran",
        );
    }

    /**
     * Relasi ke Catatan Piutang
     */
    public function catatanPiutang()
    {
        return $this->belongsTo(CatatanPiutang::class, "catatan_piutang_id");
    }

    /**
     * Check if any accounting member can approve
     */
    public function canStaffApprove()
    {
        return $this->status === "pending";
    }

    /**
     * Check if any accounting member can approve
     */
    public function canManagerApprove()
    {
        return $this->status === "pending";
    }
}
