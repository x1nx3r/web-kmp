<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BahanBakuSupplierKlien extends Model
{
    protected $table = 'bahan_baku_supplier_klien';

    protected $fillable = [
        'bahan_baku_supplier_id',
        'klien_id',
        'harga_per_satuan',
    ];

    protected $casts = [
        'harga_per_satuan' => 'decimal:2',
    ];

    /**
     * Relasi ke BahanBakuSupplier
     */
    public function bahanBakuSupplier(): BelongsTo
    {
        return $this->belongsTo(BahanBakuSupplier::class, 'bahan_baku_supplier_id');
    }

    /**
     * Relasi ke Klien
     */
    public function klien(): BelongsTo
    {
        return $this->belongsTo(Klien::class, 'klien_id');
    }
}
