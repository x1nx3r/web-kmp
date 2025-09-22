<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'klien_id',
        'no_po',
        'qty_total',
        'hpp_total',
        'total_amount',
        'spesifikasi',
        'catatan',
        'status'
    ];

    protected $casts = [
        'qty_total' => 'decimal:2',
        'hpp_total' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Relasi ke Klien
     */
    public function klien()
    {
        return $this->belongsTo(Klien::class);
    }

    /**
     * Relasi ke Supplier
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Relasi ke Forecasts (One-to-Many)
     */
    public function forecasts()
    {
        return $this->hasMany(Forecast::class);
    }

    /**
     * Relasi ke Pengiriman (One-to-Many)
     */
    public function pengiriman()
    {
        return $this->hasMany(Pengiriman::class);
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk filter siap
     */
    public function scopeSiap($query)
    {
        return $query->where('status', 'siap');
    }

    /**
     * Scope untuk filter proses
     */
    public function scopeProses($query)
    {
        return $query->where('status', 'proses');
    }

    /**
     * Scope untuk filter selesai
     */
    public function scopeSelesai($query)
    {
        return $query->where('status', 'selesai');
    }

    /**
     * Scope untuk filter gagal
     */
    public function scopeGagal($query)
    {
        return $query->where('status', 'gagal');
    }

    /**
     * Accessor untuk status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'siap' => 'Siap',
            'proses' => 'Proses',
            'selesai' => 'Selesai',
            'gagal' => 'Gagal'
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Accessor untuk total amount dalam format rupiah
     */
    public function getTotalAmountFormattedAttribute()
    {
        return 'Rp ' . number_format((float) $this->total_amount, 0, ',', '.');
    }

    /**
     * Accessor untuk HPP total dalam format rupiah
     */
    public function getHppTotalFormattedAttribute()
    {
        return 'Rp ' . number_format((float) $this->hpp_total, 0, ',', '.');
    }

    /**
     * Check apakah PO sudah selesai
     */
    public function isComplete()
    {
        return $this->status === 'selesai';
    }

    /**
     * Check apakah PO sedang dalam proses
     */
    public function isInProgress()
    {
        return $this->status === 'proses';
    }
}
