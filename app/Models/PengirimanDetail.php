<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PengirimanDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pengiriman_id',
        'purchase_order_bahan_baku_id',
        'bahan_baku_supplier_id',
        'qty_kirim',
        'harga_satuan',
        'total_harga',
        'qty_sisa',
        'kondisi_barang',
        'catatan_detail',
    ];

    protected $casts = [
        'qty_kirim' => 'decimal:2',
        'harga_satuan' => 'decimal:2',
        'total_harga' => 'decimal:2',
        'qty_sisa' => 'decimal:2',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Relasi ke Pengiriman
     */
    public function pengiriman()
    {
        return $this->belongsTo(Pengiriman::class);
    }

    /**
     * Relasi ke Bahan Baku Supplier
     */
    public function bahanBakuSupplier()
    {
        return $this->belongsTo(BahanBakuSupplier::class, 'bahan_baku_supplier_id');
    }

    /**
     * Relasi ke Purchase Order Bahan Baku
     */
    public function purchaseOrderBahanBaku()
    {
        return $this->belongsTo(PurchaseOrderBahanBaku::class);
    }

    /**
     * Scope untuk filter berdasarkan kondisi barang
     */
    public function scopeByKondisi($query, $kondisi)
    {
        return $query->where('kondisi_barang', $kondisi);
    }

    /**
     * Scope untuk barang dalam kondisi baik
     */
    public function scopeBaik($query)
    {
        return $query->where('kondisi_barang', 'baik');
    }

    /**
     * Scope untuk barang rusak
     */
    public function scopeRusak($query)
    {
        return $query->where('kondisi_barang', 'rusak');
    }

    /**
     * Scope untuk yang masih ada sisa
     */
    public function scopeAdaSisa($query)
    {
        return $query->where('qty_sisa', '>', 0);
    }

    /**
     * Accessor untuk format qty kirim
     */
    public function getFormattedQtyKirimAttribute()
    {
        return number_format((float) $this->qty_kirim, 2, ',', '.');
    }

    /**
     * Accessor untuk format qty sisa
     */
    public function getFormattedQtySisaAttribute()
    {
        return number_format((float) $this->qty_sisa, 2, ',', '.');
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

    /**
     * Accessor untuk status kondisi dengan warna
     */
    public function getKondisiBadgeClassAttribute()
    {
        return match($this->kondisi_barang) {
            'baik' => 'bg-green-100 text-green-800',
            'rusak' => 'bg-red-100 text-red-800',
            'cacat' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Check apakah ada sisa barang
     */
    public function hasSisa()
    {
        return $this->qty_sisa > 0;
    }

    /**
     * Check apakah barang dalam kondisi baik
     */
    public function isBaik()
    {
        return $this->kondisi_barang === 'baik';
    }

    /**
     * Calculate total harga otomatis
     */
    public function calculateTotalHarga()
    {
        $total = (float) $this->qty_kirim * (float) $this->harga_satuan;
        $this->attributes['total_harga'] = number_format($total, 2, '.', '');
        return $this;
    }

    /**
     * Boot method untuk auto calculate total
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->qty_kirim && $model->harga_satuan) {
                $total = (float) $model->qty_kirim * (float) $model->harga_satuan;
                $model->attributes['total_harga'] = number_format($total, 2, '.', '');
            }
        });
    }
}
