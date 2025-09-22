<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderBahanBaku extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'purchase_order_bahan_baku';

    protected $fillable = [
        'purchase_order_id',
        'bahan_baku_klien_id',
        'jumlah',
        'harga_satuan',
        'total_harga'
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'harga_satuan' => 'decimal:2',
        'total_harga' => 'decimal:2',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Relasi ke Purchase Order
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    /**
     * Relasi ke Bahan Baku Klien
     */
    public function bahanBakuKlien()
    {
        return $this->belongsTo(BahanBakuKlien::class, 'bahan_baku_klien_id');
    }

    /**
     * Relasi ke Forecast
     */
    public function forecasts()
    {
        return $this->hasMany(Forecast::class, 'purchase_order_bahan_baku_id');
    }

    /**
     * Relasi ke Pengiriman
     */
    public function pengiriman()
    {
        return $this->hasMany(Pengiriman::class, 'purchase_order_bahan_baku_id');
    }

    /**
     * Accessor untuk format jumlah
     */
    public function getFormattedJumlahAttribute()
    {
        return number_format((float) $this->jumlah, 2, ',', '.');
    }

    /**
     * Accessor untuk format harga satuan
     */
    public function getFormattedHargaSatuanAttribute()
    {
        return 'Rp ' . number_format((float) $this->harga_satuan, 0, ',', '.');
    }

    /**
     * Accessor untuk format total harga
     */
    public function getFormattedTotalHargaAttribute()
    {
        return 'Rp ' . number_format((float) $this->total_harga, 0, ',', '.');
    }
}
