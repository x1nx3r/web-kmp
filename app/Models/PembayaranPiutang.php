<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranPiutang extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_piutang';

    protected $fillable = [
        'catatan_piutang_id',
        'no_pembayaran',
        'tanggal_bayar',
        'jumlah_bayar',
        'metode_pembayaran',
        'bukti_pembayaran',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
        'jumlah_bayar' => 'decimal:2',
    ];

    /**
     * Relasi ke Catatan Piutang
     */
    public function catatanPiutang()
    {
        return $this->belongsTo(CatatanPiutang::class, 'catatan_piutang_id');
    }

    /**
     * Relasi ke User (created_by)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate nomor pembayaran otomatis
     */
    public static function generateNoPembayaran()
    {
        $lastPembayaran = self::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastPembayaran ? (intval(substr($lastPembayaran->no_pembayaran, -4)) + 1) : 1;

        return 'PAY-' . now()->format('Ym') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
