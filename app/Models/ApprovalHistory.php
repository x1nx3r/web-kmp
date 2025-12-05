<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "approval_history";

    protected $fillable = [
        "approval_type",
        "approval_id",
        "pengiriman_id",
        "invoice_id",
        "role",
        "user_id",
        "action",
        "notes",
    ];

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    /**
     * Relasi ke Pengiriman
     */
    public function pengiriman()
    {
        return $this->belongsTo(Pengiriman::class, "pengiriman_id");
    }

    /**
     * Relasi ke Invoice (nullable)
     */
    public function invoice()
    {
        return $this->belongsTo(InvoicePenagihan::class, "invoice_id");
    }

    /**
     * Relasi polymorphic ke approval (pembayaran atau penagihan)
     */
    public function approval()
    {
        if ($this->approval_type === "pembayaran") {
            return $this->belongsTo(ApprovalPembayaran::class, "approval_id");
        } else {
            return $this->belongsTo(ApprovalPenagihan::class, "approval_id");
        }
    }
}
