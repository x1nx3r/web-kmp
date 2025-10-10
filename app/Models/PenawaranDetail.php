<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenawaranDetail extends Model
{
    use HasFactory;

    protected $table = 'penawaran_detail';

    protected $fillable = [
        'penawaran_id',
        'bahan_baku_klien_id',
        'supplier_id',
        'bahan_baku_supplier_id',
        'nama_material',
        'satuan',
        'quantity',
        'harga_klien',
        'harga_supplier',
        'is_custom_price',
        'subtotal_revenue',
        'subtotal_cost',
        'subtotal_profit',
        'margin_percentage',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'harga_klien' => 'decimal:2',
        'harga_supplier' => 'decimal:2',
        'subtotal_revenue' => 'decimal:2',
        'subtotal_cost' => 'decimal:2',
        'subtotal_profit' => 'decimal:2',
        'margin_percentage' => 'decimal:2',
        'is_custom_price' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function penawaran(): BelongsTo
    {
        return $this->belongsTo(Penawaran::class);
    }

    public function bahanBakuKlien(): BelongsTo
    {
        return $this->belongsTo(BahanBakuKlien::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function bahanBakuSupplier(): BelongsTo
    {
        return $this->belongsTo(BahanBakuSupplier::class);
    }

    public function alternativeSuppliers(): HasMany
    {
        return $this->hasMany(PenawaranAlternativeSupplier::class);
    }

    /**
     * Accessors
     */
    public function getSupplierNameAttribute(): string
    {
        return $this->supplier?->nama ?? 'Unknown Supplier';
    }

    public function getMaterialNameAttribute(): string
    {
        return $this->nama_material ?? $this->bahanBakuKlien?->nama ?? 'Unknown Material';
    }

    public function getSupplierPicAttribute(): ?User
    {
        return $this->supplier?->picPurchasing;
    }

    public function getFormattedQuantityAttribute(): string
    {
        return number_format($this->quantity, 2) . ' ' . $this->satuan;
    }

    public function getFormattedHargaKlienAttribute(): string
    {
        return 'Rp ' . number_format($this->harga_klien, 0, ',', '.');
    }

    public function getFormattedHargaSupplierAttribute(): string
    {
        return 'Rp ' . number_format($this->harga_supplier, 0, ',', '.');
    }

    public function getFormattedSubtotalRevenueAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal_revenue, 0, ',', '.');
    }

    /**
     * Methods
     */
    public function calculateSubtotals(): void
    {
        $this->subtotal_revenue = $this->quantity * $this->harga_klien;
        $this->subtotal_cost = $this->quantity * $this->harga_supplier;
        $this->subtotal_profit = $this->subtotal_revenue - $this->subtotal_cost;
        
        if ($this->subtotal_revenue > 0) {
            $this->margin_percentage = ($this->subtotal_profit / $this->subtotal_revenue) * 100;
        } else {
            $this->margin_percentage = 0;
        }
    }

    public function calculateMargin(): float
    {
        if ($this->subtotal_revenue > 0) {
            return ($this->subtotal_profit / $this->subtotal_revenue) * 100;
        }
        return 0;
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-calculate subtotals when creating/updating
        static::saving(function ($detail) {
            $detail->calculateSubtotals();
        });

        // Recalculate penawaran totals after saving detail
        static::saved(function ($detail) {
            $detail->penawaran->calculateTotals();
        });

        // Recalculate penawaran totals after deleting detail
        static::deleted(function ($detail) {
            $detail->penawaran->calculateTotals();
        });
    }
}
