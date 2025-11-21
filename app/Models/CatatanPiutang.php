<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatatanPiutang extends Model
{
    use HasFactory;

    protected $table = 'catatan_piutangs';

    protected $fillable = [
        'supplier_id',
        'tanggal_piutang',
        'tanggal_jatuh_tempo',
        'jumlah_piutang',
        'jumlah_dibayar',
        'sisa_piutang',
        'status',
        'keterangan',
        'bukti_transaksi',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_piutang' => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'jumlah_piutang' => 'decimal:2',
        'jumlah_dibayar' => 'decimal:2',
        'sisa_piutang' => 'decimal:2',
    ];

    /**
     * Relasi ke Supplier
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Relasi ke User (created_by)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke User (updated_by)
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relasi ke Pembayaran Piutang
     */
    public function pembayaran()
    {
        return $this->hasMany(PembayaranPiutang::class, 'catatan_piutang_id');
    }

    /**
     * Update sisa piutang dan status
     */
    public function updateSisaPiutang()
    {
        $totalDibayar = $this->pembayaran()->sum('jumlah_bayar');
        $this->jumlah_dibayar = $totalDibayar;
        $this->sisa_piutang = $this->jumlah_piutang - $totalDibayar;

        // Update status
        if ($this->sisa_piutang <= 0) {
            $this->status = 'lunas';
        } elseif ($totalDibayar > 0) {
            $this->status = 'cicilan';
        } else {
            $this->status = 'belum_lunas';
        }

        $this->save();
    }
}
