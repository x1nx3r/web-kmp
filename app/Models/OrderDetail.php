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
        'nama_material_po',
        'qty',
        'satuan',
        'harga_jual',
        'total_harga',
        'qty_shipped',
        'status',
        'spesifikasi_khusus',
        'catatan',
        // New fields for multi-supplier support
        'cheapest_price',
        'most_expensive_price',
        'recommended_price',
        'best_margin_percentage',
        'worst_margin_percentage',
        'recommended_margin_percentage',
        'available_suppliers_count',
        'recommended_supplier_id',
        'total_shipped_quantity',
        'remaining_quantity',
        'suppliers_used_count',
        'supplier_options_populated',
        'options_populated_at',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'total_harga' => 'decimal:2',
        'qty_shipped' => 'decimal:2',
        // New fields
        'cheapest_price' => 'decimal:2',
        'most_expensive_price' => 'decimal:2',
        'recommended_price' => 'decimal:2',
        'best_margin_percentage' => 'decimal:2',
        'worst_margin_percentage' => 'decimal:2',
        'recommended_margin_percentage' => 'decimal:2',
        'total_shipped_quantity' => 'decimal:2',
        'remaining_quantity' => 'decimal:2',
        'supplier_options_populated' => 'boolean',
        'options_populated_at' => 'datetime',
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

    public function recommendedSupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'recommended_supplier_id');
    }

    public function orderSuppliers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderSupplier::class);
    }

    public function availableSuppliers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderSupplier::class)->where('is_available', true);
    }

    public function usedSuppliers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderSupplier::class)->where('has_been_used', true);
    }

    /**
     * Relasi ke Forecast Details (One-to-Many) - menggunakan purchase_order_bahan_baku_id sebagai foreign key
     */
    public function forecastDetails(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ForecastDetail::class, 'purchase_order_bahan_baku_id');
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
        return $query->whereHas('orderSuppliers', function ($subQuery) use ($supplierId) {
            $subQuery->where('supplier_id', $supplierId);
        });
    }

    public function scopeByMaterial($query, $materialId)
    {
        return $query->where('bahan_baku_klien_id', $materialId);
    }

    public function scopeHighMargin($query, $threshold = 20)
    {
        return $query->where(function ($subQuery) use ($threshold) {
            $subQuery
                ->where('best_margin_percentage', '>=', $threshold)
                ->orWhere('recommended_margin_percentage', '>=', $threshold);
        });
    }

    public function scopeLowMargin($query, $threshold = 10)
    {
        return $query->where(function ($subQuery) use ($threshold) {
            $subQuery
                ->whereNull('best_margin_percentage')
                ->orWhere('best_margin_percentage', '<', $threshold);
        });
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
     * Accessor for compatibility with view expectations
     */
    public function getTotalAmountAttribute(): float
    {
        return $this->total_harga ?? 0;
    }

    /**
     * Business Logic Methods
     */
    public function calculateTotals(bool $save = true): void
    {
        // Calculate total selling price (this still exists)
        $this->total_harga = $this->qty * $this->harga_jual;
        
        if ($save) {
            $this->save();
        }
    }

    public function getProfitCategoryAttribute(): string
    {
        $margin = $this->recommended_margin_percentage ?? $this->best_margin_percentage ?? 0;

        if ($margin < 0) return 'rugi';
        if ($margin < 10) return 'rendah';
        if ($margin < 25) return 'sedang';
        return 'tinggi';
    }

    public function updatePricing(float $supplierPrice, float $sellingPrice): void
    {
        $this->harga_jual = $sellingPrice;
        $this->calculateTotals(true);

        $this->recommended_price = $sellingPrice;
        if ($sellingPrice > 0) {
            $this->recommended_margin_percentage = (($sellingPrice - $supplierPrice) / $sellingPrice) * 100;
        }

        $this->updateSupplierSummary();
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
        
        $this->calculateTotals(true);
        
        // Update parent order status
        $this->order->updateShippingStatus();
        
        return true;
    }

    /**
     * Static Methods
     */
    public static function createFromPenawaran(Order $order, $penawaranDetail): self
    {
        $quantity = $penawaranDetail->quantity ?? $penawaranDetail->qty ?? 0;
        $sellingPrice = $penawaranDetail->harga_klien ?? $penawaranDetail->harga_jual ?? 0;

        $detail = static::create([
            'order_id' => $order->id,
            'bahan_baku_klien_id' => $penawaranDetail->bahan_baku_klien_id,
            'qty' => $quantity,
            'satuan' => $penawaranDetail->satuan,
            'harga_jual' => $sellingPrice,
            'total_harga' => $quantity * $sellingPrice,
            'status' => 'menunggu',
            'spesifikasi_khusus' => $penawaranDetail->spesifikasi ?? null,
            'catatan' => $penawaranDetail->catatan ?? $penawaranDetail->notes ?? null,
        ]);

        $detail->populateSupplierOptions();

        if ($penawaranDetail->bahan_baku_supplier_id ?? null) {
            $detail->orderSuppliers()->update(['is_recommended' => false]);

            $selectedSupplier = $detail->orderSuppliers()
                ->where('bahan_baku_supplier_id', $penawaranDetail->bahan_baku_supplier_id)
                ->first();

            if ($selectedSupplier) {
                $selectedSupplier->is_recommended = true;
                $selectedSupplier->price_rank = 1;
                $selectedSupplier->save();
            }

            $detail->updateSupplierSummary();
        }

        return $detail;
    }

    /**
     * Auto-Supplier Population Methods
     */
    public function populateSupplierOptions(): void
    {
        if ($this->supplier_options_populated) {
            return; // Already populated
        }

        // Get the material name for matching
        $material = $this->bahanBakuKlien;
        if (!$material) {
            return;
        }

        $materialName = $material->nama;
        $firstWord = trim(explode(' ', $materialName)[0]);

        // Find suppliers with matching material names
        $bahanBakuSuppliers = BahanBakuSupplier::with('supplier')
            ->where(function($query) use ($materialName, $firstWord) {
                $query->where('nama', 'LIKE', '%' . $materialName . '%')
                      ->orWhere('nama', 'LIKE', '%' . $firstWord . '%');
            })
            ->get();

        if ($bahanBakuSuppliers->isEmpty()) {
            return;
        }

        $suppliers = [];
        $rank = 1;

        // Sort by price to assign ranks (use harga_per_satuan field)
        $sortedSuppliers = $bahanBakuSuppliers->sortBy('harga_per_satuan');

        foreach ($sortedSuppliers as $bahanBakuSupplier) {
            $orderSupplier = new OrderSupplier([
                'order_detail_id' => $this->id,
                'supplier_id' => $bahanBakuSupplier->supplier_id,
                'bahan_baku_supplier_id' => $bahanBakuSupplier->id,
                'unit_price' => $bahanBakuSupplier->harga_per_satuan, // Use correct field name
                'price_rank' => $rank,
                'is_recommended' => $rank === 1, // Best price is recommended
                'is_available' => true,
                'price_updated_at' => now(),
            ]);

            // Calculate margin if selling price is set
            if ($this->harga_jual > 0) {
                $orderSupplier->calculateMargin($this->harga_jual);
            }

            $orderSupplier->save();
            $suppliers[] = $orderSupplier;
            $rank++;
        }

        // Update summary fields
        $this->updateSupplierSummary($suppliers);
        
        // Mark as populated
        $this->supplier_options_populated = true;
        $this->options_populated_at = now();
        $this->save();
    }

    public function updateSupplierSummary($suppliers = null): void
    {
        $supplierCollection = $suppliers === null
            ? $this->orderSuppliers()->get()
            : collect($suppliers);

        if ($supplierCollection->isEmpty()) {
            $this->cheapest_price = null;
            $this->most_expensive_price = null;
            $this->best_margin_percentage = null;
            $this->worst_margin_percentage = null;
            $this->recommended_supplier_id = null;
            $this->recommended_price = null;
            $this->recommended_margin_percentage = null;
            $this->available_suppliers_count = 0;
            $this->save();

            return;
        }

        // Price analysis
        $prices = $supplierCollection->pluck('unit_price');
        $this->cheapest_price = $prices->min();
        $this->most_expensive_price = $prices->max();
        
        // Margin analysis
        $margins = $supplierCollection->whereNotNull('calculated_margin')->pluck('calculated_margin');
        if ($margins->isNotEmpty()) {
            $this->best_margin_percentage = $margins->max();
            $this->worst_margin_percentage = $margins->min();
        }

        // Recommended supplier (best margin or cheapest if no margin calculated)
        $recommended = $supplierCollection->firstWhere('is_recommended', true);
        if ($recommended) {
            $this->recommended_supplier_id = $recommended->supplier_id;
            $this->recommended_price = $recommended->unit_price;
            $this->recommended_margin_percentage = $recommended->calculated_margin;
        }

        $this->available_suppliers_count = $supplierCollection->where('is_available', true)->count();

        $this->save();
    }

    public function updateFulfillmentTracking(): void
    {
        $suppliers = $this->orderSuppliers()->get();
        
        // Update shipped quantities for each supplier
        foreach ($suppliers as $supplier) {
            $supplier->updateShippedQuantity();
            $supplier->save();
        }

        // Update order detail summary
        $this->total_shipped_quantity = $suppliers->sum('shipped_quantity');
        $this->remaining_quantity = max(0, $this->qty - $this->total_shipped_quantity);
        $this->suppliers_used_count = $suppliers->where('has_been_used', true)->count();
        
        // Update qty_shipped for compatibility with existing system
        $this->qty_shipped = $this->total_shipped_quantity;
        
        // Update status based on fulfillment
        if ($this->remaining_quantity <= 0) {
            $this->status = 'selesai';
        } elseif ($this->total_shipped_quantity > 0) {
            $this->status = 'sebagian_dikirim';
        }

        $this->save();
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($orderDetail) {
            // Auto-calculate totals on creation but don't save (will be saved by the main save operation)
            $orderDetail->calculateTotals(false);
        });
        
        static::updating(function ($orderDetail) {
            // Recalculate totals when pricing changes
            if ($orderDetail->isDirty(['qty', 'harga_jual'])) {
                $orderDetail->calculateTotals(false);
            }
        });
        
        static::saved(function ($orderDetail) {
            // Update parent order totals when detail changes
            $orderDetail->order->calculateTotals();
        });
    }
}