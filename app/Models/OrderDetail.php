<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'bahan_baku_klien_id',
        'supplier_id',
        'qty',
        'satuan',
        'harga_supplier',
        'total_hpp',
        'harga_jual',
        'total_harga',
        'margin_per_unit',
        'total_margin',
        'margin_percentage',
        'qty_shipped',
        'status',
        'spesifikasi_khusus',
        'catatan',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'harga_supplier' => 'decimal:2',
        'total_hpp' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'total_harga' => 'decimal:2',
        'margin_per_unit' => 'decimal:2',
        'total_margin' => 'decimal:2',
        'margin_percentage' => 'decimal:2',
        'qty_shipped' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function bahanBakuKlien(): BelongsTo
    {
        return $this->belongsTo(BahanBakuKlien::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Scopes
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeByMaterial($query, $materialId)
    {
        return $query->where('bahan_baku_klien_id', $materialId);
    }

    public function scopeHighMargin($query, $threshold = 20)
    {
        return $query->where('margin_percentage', '>=', $threshold);
    }

    public function scopeLowMargin($query, $threshold = 10)
    {
        return $query->where('margin_percentage', '<', $threshold);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'menunggu');
    }

    public function scopeInProcess($query)
    {
        return $query->where('status', 'diproses');
    }

    public function scopePartiallyShipped($query)
    {
        return $query->where('status', 'sebagian_dikirim')
                    ->where('qty_shipped', '>', 0)
                    ->where('qty_shipped', '<', 'qty');
    }

    /**
     * Computed Properties
     */
    public function getCompletionPercentageAttribute(): float
    {
        if ($this->qty == 0) return 0;
        
        return round(($this->qty_shipped / $this->qty) * 100, 2);
    }

    public function getIsCompleteAttribute(): bool
    {
        return $this->qty_shipped >= $this->qty;
    }

    public function getRemainingToShipAttribute(): float
    {
        return max(0, $this->qty - $this->qty_shipped);
    }

    /**
     * Business Logic Methods
     */
    public function calculateTotals(): void
    {
        // Calculate total HPP
        $this->total_hpp = $this->qty * $this->harga_supplier;
        
        // Calculate total selling price
        $this->total_harga = $this->qty * $this->harga_jual;
        
        // Calculate margin per unit
        $this->margin_per_unit = $this->harga_jual - $this->harga_supplier;
        
        // Calculate total margin
        $this->total_margin = $this->qty * $this->margin_per_unit;
        
        // Calculate margin percentage
        if ($this->harga_jual > 0) {
            $this->margin_percentage = ($this->margin_per_unit / $this->harga_jual) * 100;
        }
        
        $this->save();
    }

    public function getProfitCategoryAttribute(): string
    {
        if ($this->margin_percentage < 0) return 'rugi';
        if ($this->margin_percentage < 10) return 'rendah';
        if ($this->margin_percentage < 25) return 'sedang';
        return 'tinggi';
    }

    public function updatePricing(float $supplierPrice, float $sellingPrice): void
    {
        $this->harga_supplier = $supplierPrice;
        $this->harga_jual = $sellingPrice;
        $this->calculateTotals();
    }

    public function startProcessing(): bool
    {
        if ($this->status !== 'menunggu') return false;
        
        $this->status = 'diproses';
        
        return $this->save();
    }

    public function ship(float $quantity): bool
    {
        if ($quantity <= 0) return false;
        if ($quantity > $this->remaining_to_ship) return false;
        
        $this->qty_shipped += $quantity;
        
        // Update status based on shipping progress
        if ($this->qty_shipped >= $this->qty) {
            $this->status = 'selesai';
        } elseif ($this->qty_shipped > 0) {
            $this->status = 'sebagian_dikirim';
        }
        
        $this->calculateTotals();
        
        // Update parent order status
        $this->order->updateShippingStatus();
        
        return true;
    }

    /**
     * Static Methods
     */
    public static function createFromPenawaran(Order $order, $penawaranDetail): self
    {
        return static::create([
            'order_id' => $order->id,
            'bahan_baku_klien_id' => $penawaranDetail->bahan_baku_klien_id,
            'supplier_id' => $penawaranDetail->supplier_id,
            'qty' => $penawaranDetail->qty,
            'satuan' => $penawaranDetail->satuan,
            'harga_supplier' => $penawaranDetail->harga_supplier,
            'harga_jual' => $penawaranDetail->harga_jual,
            'spesifikasi_khusus' => $penawaranDetail->spesifikasi,
            'catatan' => $penawaranDetail->catatan,
        ]);
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($orderDetail) {
            // Auto-calculate totals on creation
            $orderDetail->calculateTotals();
        });
        
        static::updating(function ($orderDetail) {
            // Recalculate totals when pricing changes
            if ($orderDetail->isDirty(['qty', 'harga_supplier', 'harga_jual'])) {
                $orderDetail->calculateTotals();
            }
        });
        
        static::saved(function ($orderDetail) {
            // Update parent order totals when detail changes
            $orderDetail->order->calculateTotals();
        });
    }
}