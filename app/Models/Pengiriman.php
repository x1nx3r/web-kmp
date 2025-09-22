<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Pengiriman extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pengiriman';

    protected $fillable = [
        'po_id',
        'purchasing_id',
        'bahan_baku_supplier_id',
        'tanggal_kirim',
        'hari_kirim',
        'qty_kirim',
        'harga_jual',
        'total_harga',
        'qty_sisa',
        'bukti_foto_bongkar',
        'status'
    ];

    protected $casts = [
        'tanggal_kirim' => 'date',
        'hari_kirim' => 'date',
        'qty_kirim' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'total_harga' => 'decimal:2',
        'qty_sisa' => 'decimal:2',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Relasi ke Purchase Order
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    /**
     * Relasi ke User (Purchasing)
     */
    public function purchasing()
    {
        return $this->belongsTo(User::class, 'purchasing_id');
    }

    /**
     * Relasi ke Bahan Baku Supplier
     */
    public function bahanBaku()
    {
        return $this->belongsTo(BahanBakuSupplier::class, 'bahan_baku_supplier_id');
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk filter pending
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope untuk filter terkirim
     */
    public function scopeTerkirim($query)
    {
        return $query->where('status', 'terkirim');
    }

    /**
     * Scope untuk filter diverifikasi
     */
    public function scopeDiverifikasi($query)
    {
        return $query->where('status', 'diverifikasi');
    }

    /**
     * Scope untuk filter yang sudah dikirim (terkirim + diverifikasi)
     */
    public function scopeSudahKirim($query)
    {
        return $query->whereIn('status', ['terkirim', 'diverifikasi']);
    }

    /**
     * Accessor untuk format tanggal kirim
     */
    public function getTanggalKirimFormattedAttribute()
    {
        return $this->tanggal_kirim ? Carbon::parse($this->tanggal_kirim)->format('d-m-Y') : null;
    }

    /**
     * Accessor untuk format hari kirim
     */
    public function getHariKirimFormattedAttribute()
    {
        return $this->hari_kirim ? Carbon::parse($this->hari_kirim)->format('d-m-Y') : null;
    }

    /**
     * Accessor untuk status dalam bahasa Indonesia
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending',
            'terkirim' => 'Terkirim',
            'diverifikasi' => 'Diverifikasi'
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Accessor untuk total harga dalam format rupiah
     */
    public function getTotalHargaFormattedAttribute()
    {
        return $this->total_harga ? 'Rp ' . number_format((float) $this->total_harga, 0, ',', '.') : null;
    }

    /**
     * Accessor untuk harga jual dalam format rupiah
     */
    public function getHargaJualFormattedAttribute()
    {
        return $this->harga_jual ? 'Rp ' . number_format((float) $this->harga_jual, 0, ',', '.') : null;
    }

    /**
     * Check apakah pengiriman sudah lengkap
     */
    public function isComplete()
    {
        return $this->status === 'diverifikasi';
    }

    /**
     * Check apakah pengiriman sudah dikirim
     */
    public function isDelivered()
    {
        return in_array($this->status, ['terkirim', 'diverifikasi']);
    }

    /**
     * Check apakah masih ada sisa qty
     */
    public function hasSisa()
    {
        return $this->qty_sisa > 0;
    }

    /**
     * Accessor untuk URL foto bukti bongkar
     */
    public function getBuktiFotoBongkarUrlAttribute()
    {
        return $this->bukti_foto_bongkar ? asset('storage/' . $this->bukti_foto_bongkar) : null;
    }
}
