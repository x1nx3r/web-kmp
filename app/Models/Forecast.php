<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Forecast extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'po_id',
        'purchasing_id',
        'bahan_baku_supplier_id',
        'tanggal_kirim_forecast',
        'hari_kirim_forecast',
        'qty_forecast',
        'harga_jual_forecast',
        'total_forecast',
        'status',
        'catatan'
    ];

    protected $casts = [
        'qty_forecast' => 'decimal:2',
        'harga_jual_forecast' => 'decimal:2',
        'total_forecast' => 'decimal:2',
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
     * Scope untuk filter sukses
     */
    public function scopeSukses($query)
    {
        return $query->where('status', 'sukses');
    }

    /**
     * Scope untuk filter gagal
     */
    public function scopeGagal($query)
    {
        return $query->where('status', 'gagal');
    }

    /**
     * Accessor untuk format tanggal kirim
     */
    public function getTanggalKirimForecastFormattedAttribute()
    {
        return Carbon::parse($this->tanggal_kirim_forecast)->format('d-m-Y');
    }

    /**
     * Accessor untuk status dalam bahasa Indonesia
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending',
            'sukses' => 'Sukses',
            'gagal' => 'Gagal'
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Accessor untuk total forecast dalam format rupiah
     */
    public function getTotalForecastFormattedAttribute()
    {
        return 'Rp ' . number_format((float) $this->total_forecast, 0, ',', '.');
    }
}
