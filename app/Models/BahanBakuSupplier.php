<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BahanBakuSupplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bahan_baku_supplier';

    protected $fillable = [
        'supplier_id',
        'nama',
        'harga_per_satuan',
        'satuan',
        'stok'
    ];

    protected $casts = [
        'harga_per_satuan' => 'decimal:2',
        'stok' => 'decimal:2',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Relasi ke Supplier
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Accessor untuk format harga
     */
    public function getFormattedHargaAttribute()
    {
        return 'Rp ' . number_format((float) $this->harga_per_satuan, 0, ',', '.');
    }

    /**
     * Accessor untuk format stok
     */
    public function getFormattedStokAttribute()
    {
        return number_format((float) $this->stok, 0, ',', '.') . ' ' . $this->satuan;
    }

    /**
     * Scope untuk pencarian bahan baku
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('nama', 'like', '%' . $search . '%')
                    ->orWhere('satuan', 'like', '%' . $search . '%');
    }

    /**
     * Scope untuk filter berdasarkan supplier
     */
    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    /**
     * Scope untuk filter berdasarkan stok minimum
     */
    public function scopeStokMinimum($query, $minimum = 0)
    {
        return $query->where('stok', '>=', $minimum);
    }
}
