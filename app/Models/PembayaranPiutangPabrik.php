<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembayaranPiutangPabrik extends Model
{
    protected $fillable = [
        'invoice_penagihan_id',
        'no_pembayaran',
        'tanggal_bayar',
        'jumlah_bayar',
        'metode_pembayaran',
        'catatan',
        'bukti_pembayaran',
        'created_by',
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
        'jumlah_bayar' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(InvoicePenagihan::class, 'invoice_penagihan_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
