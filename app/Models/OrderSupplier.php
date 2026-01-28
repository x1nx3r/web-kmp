<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderSupplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_detail_id',
        'supplier_id',
        'bahan_baku_supplier_id',
        'unit_price',
        'shipped_quantity',
        'shipped_amount',
        'calculated_margin',
        'potential_profit',
        'is_recommended',
        'price_rank',
        'is_available',
        'has_been_used',
        'price_updated_at',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'shipped_quantity' => 'decimal:2',
        'shipped_amount' => 'decimal:2',
        'calculated_margin' => 'decimal:4',
        'potential_profit' => 'decimal:2',
        'is_recommended' => 'boolean',
        'is_available' => 'boolean',
        'has_been_used' => 'boolean',
        'price_updated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function orderDetail(): BelongsTo
    {
        return $this->belongsTo(OrderDetail::class);
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
     * Scopes
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeRecommended($query)
    {
        return $query->where('is_recommended', true);
    }

    public function scopeByPriceRank($query)
    {
        return $query->orderBy('price_rank');
    }

    public function scopeByMargin($query)
    {
        return $query->orderByDesc('calculated_margin');
    }

    /**
     * Methods
     */
    public function calculateMargin($sellingPrice): void
    {
        if ($this->unit_price > 0 && $sellingPrice > 0) {
            $this->calculated_margin = (($sellingPrice - $this->unit_price) / $sellingPrice) * 100;
            $this->potential_profit = $sellingPrice - $this->unit_price;
        }
    }

    public function updateShippedQuantity(): void
    {
        // Calculate shipped quantity from pengiriman_details
        $shipped = \DB::table('pengiriman_details as pd')
            ->join('pengiriman as p', 'p.id', '=', 'pd.pengiriman_id')
            ->join('purchase_order_bahan_baku as pobb', 'pobb.id', '=', 'pd.purchase_order_bahan_baku_id')
            ->where('pd.bahan_baku_supplier_id', $this->bahan_baku_supplier_id)
            ->where('pobb.order_detail_id', $this->order_detail_id) // Assuming this link exists
            ->sum('pd.qty_kirim');

        $this->shipped_quantity = $shipped ?? 0;
        $this->shipped_amount = $this->shipped_quantity * $this->unit_price;
        $this->has_been_used = $this->shipped_quantity > 0;
    }

    public function getRemainingQuantityAttribute(): float
    {
        return max(0, $this->orderDetail->qty - $this->shipped_quantity);
    }

    public function getFulfillmentPercentageAttribute(): float
    {
        if ($this->orderDetail->qty == 0) return 0;
        return ($this->shipped_quantity / $this->orderDetail->qty) * 100;
    }

    /**
     * Accessor for compatibility with view expectations
     */
    public function getHargaSupplierAttribute(): float
    {
        return $this->unit_price ?? 0;
    }

    public function getMarginPercentageAttribute(): float
    {
        return $this->calculated_margin ?? 0;
    }
}
