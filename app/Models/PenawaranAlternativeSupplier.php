<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenawaranAlternativeSupplier extends Model
{
    use HasFactory;

    protected $table = 'penawaran_alternative_suppliers';

    protected $fillable = [
        'penawaran_detail_id',
        'supplier_id',
        'bahan_baku_supplier_id',
        'harga_supplier',
        'notes',
    ];

    protected $casts = [
        'harga_supplier' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function penawaranDetail(): BelongsTo
    {
        return $this->belongsTo(PenawaranDetail::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function bahanBakuSupplier(): BelongsTo
    {
        return $this->belongsTo(BahanBakuSupplier::class);
    }

    /**
     * Accessors
     */
    public function getSupplierNameAttribute(): string
    {
        return $this->supplier?->nama ?? 'Unknown Supplier';
    }

    public function getFormattedHargaAttribute(): string
    {
        return 'Rp ' . number_format($this->harga_supplier, 0, ',', '.');
    }
}
