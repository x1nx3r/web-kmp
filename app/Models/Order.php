<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_order',
        'klien_id',
        'created_by',
        'tanggal_order',
        'catatan',
        'status',
        'priority',
        'total_amount',
        'total_items',
        'total_qty',
        'dikonfirmasi_at',
        'selesai_at',
        'dibatalkan_at',
        'alasan_pembatalan',
        'po_number',
        'po_start_date',
        'po_end_date',
        'po_document_path',
        'po_document_original_name',
        'priority_calculated_at',
    ];

    protected $casts = [
        'tanggal_order' => 'date',
        'dikonfirmasi_at' => 'datetime',
        'selesai_at' => 'datetime',
        'dibatalkan_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'total_qty' => 'decimal:2',
        'po_start_date' => 'date',
        'po_end_date' => 'date',
        'priority_calculated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function klien(): BelongsTo
    {
        return $this->belongsTo(Klien::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function winner()
    {
        return $this->hasOne(OrderWinner::class);
    }



    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function bahanBakuKliens(): HasManyThrough
    {
        return $this->hasManyThrough(
            BahanBakuKlien::class,
            OrderDetail::class,
            'order_id',
            'id',
            'id',
            'bahan_baku_klien_id'
        );
    }

    public function orderSuppliers(): HasManyThrough
    {
        return $this->hasManyThrough(
            OrderSupplier::class,
            OrderDetail::class,
            'order_id',
            'order_detail_id',
            'id',
            'id'
        );
    }

    public function allSuppliers(): HasManyThrough
    {
        return $this->hasManyThrough(
            Supplier::class,
            OrderSupplier::class,
            'order_detail_id',
            'id',
            'id',
            'supplier_id'
        )->distinct();
    }

    public function usedSuppliers(): HasManyThrough
    {
        return $this->hasManyThrough(
            Supplier::class,
            OrderSupplier::class,
            'order_detail_id',
            'id',
            'id',
            'supplier_id'
        )->where('order_suppliers.has_been_used', true)->distinct();
    }

    /**
     * Relasi ke Forecasts (One-to-Many) - menggunakan purchase_order_id sebagai foreign key
     */
    public function forecasts(): HasMany
    {
        return $this->hasMany(Forecast::class, 'purchase_order_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByKlien($query, $klienId)
    {
        return $query->where('klien_id', $klienId);
    }

    public function scopeByCreator($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_order', [$startDate, $endDate]);
    }

    public function scopeHighMargin($query, $threshold = 20)
    {
        return $query->whereHas('orderDetails', function ($detailQuery) use ($threshold) {
            $detailQuery
                ->where('best_margin_percentage', '>=', $threshold)
                ->orWhere('recommended_margin_percentage', '>=', $threshold);
        });
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', 'mendesak');
    }

    /**
     * Computed Properties
     */
    public function getIsUrgentAttribute(): bool
    {
        return $this->priority === 'mendesak';
    }

    public function getCompletionPercentageAttribute(): float
    {
        if ($this->total_qty == 0) return 0;
        
        $totalShipped = $this->orderDetails->sum('qty_shipped');
        return round(($totalShipped / $this->total_qty) * 100, 2);
    }

    public function getPoDocumentUrlAttribute(): ?string
    {
        if (!$this->po_document_path) {
            return null;
        }

        if (!Storage::disk('public')->exists($this->po_document_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->po_document_path);
    }

    public function getProgressStatusAttribute(): string
    {
        $completion = $this->completion_percentage;
        
        if ($completion == 0) return 'not_started';
        if ($completion < 50) return 'low_progress';
        if ($completion < 100) return 'in_progress';
        return 'completed';
    }

    /**
     * Business Logic Methods
     */
    public function calculateTotals(): void
    {
        $details = $this->orderDetails;
        
        $this->total_amount = $details->sum('total_harga');
        $this->total_items = $details->count();
        $this->total_qty = $details->sum('qty');
        
        $this->save();
    }

    public function confirm(): bool
    {
        if ($this->status !== 'draft') return false;
        
        $this->status = 'dikonfirmasi';
        $this->dikonfirmasi_at = now();
        
        return $this->save();
    }

    public function startProcessing(): bool
    {
        if ($this->status !== 'dikonfirmasi') return false;
        
        $this->status = 'diproses';
        
        return $this->save();
    }

    public function complete(): bool
    {
        if (!in_array($this->status, ['diproses', 'sebagian_dikirim'])) return false;
        
        $this->status = 'selesai';
        $this->selesai_at = now();
        
        return $this->save();
    }

    public function cancel(?string $reason = null): bool
    {
        if (in_array($this->status, ['selesai', 'dibatalkan'])) return false;
        
        $this->status = 'dibatalkan';
        $this->dibatalkan_at = now();
        $this->alasan_pembatalan = $reason;
        
        return $this->save();
    }

    public function updateShippingStatus(): void
    {
        $totalQty = $this->total_qty;
        $shippedQty = $this->orderDetails->sum('qty_shipped');
        
        // Prevent recursion by checking if status change is needed
        $newStatus = null;
        
        if ($shippedQty == 0) {
            // No items shipped yet
            if ($this->status === 'sebagian_dikirim') {
                $newStatus = 'diproses';
            }
        } elseif ($shippedQty >= $totalQty) {
            // All items shipped
            if (!in_array($this->status, ['selesai'])) {
                $newStatus = 'selesai';
                $this->selesai_at = now();
            }
        } else {
            // Partially shipped
            if ($this->status !== 'sebagian_dikirim') {
                $newStatus = 'sebagian_dikirim';
            }
        }
        
        // Only save if status actually needs to change
        if ($newStatus && $newStatus !== $this->status) {
            $this->status = $newStatus;
            $this->saveQuietly(); // Use saveQuietly to prevent triggering events
        }
    }

    /**
     * Auto-Supplier Population Methods
     */
    public function populateAllSupplierOptions(): void
    {
        foreach ($this->orderDetails as $detail) {
            $detail->populateSupplierOptions();
        }

        // Update order-level summary
        $this->updateSupplierSummary();
    }

    public function updateSupplierSummary(): void
    {
        $details = $this->orderDetails()->with('orderSuppliers')->get();
        
        // Update each detail's supplier summary
        foreach ($details as $detail) {
            $detail->updateSupplierSummary();
        }

        // Update order totals
        $this->calculateTotals();
    }

    public function updateAllFulfillmentTracking(): void
    {
        foreach ($this->orderDetails as $detail) {
            $detail->updateFulfillmentTracking();
        }

        // Update order status based on overall completion
        $this->updateOrderStatus();
    }

    public function updateOrderStatus(): void
    {
        $details = $this->orderDetails;
        
        if ($details->isEmpty()) return;

        $totalQuantity = $details->sum('qty');
        $shippedQuantity = $details->sum('total_shipped_quantity');
        
        if ($shippedQuantity >= $totalQuantity) {
            $this->status = 'selesai';
            $this->selesai_at = now();
        } elseif ($shippedQuantity > 0) {
            $this->status = 'sebagian_dikirim';
        } elseif ($this->orderSuppliers()->where('has_been_used', true)->exists()) {
            $this->status = 'diproses';
        }

        $this->save();
    }

    public function getAvailableSupplierOptionsAttribute(): array
    {
        $summary = [];
        
        foreach ($this->orderDetails as $detail) {
            $summary[] = [
                'material' => $detail->bahanBakuKlien->nama_material,
                'quantity' => $detail->qty,
                'available_suppliers' => $detail->available_suppliers_count,
                'recommended_supplier' => $detail->recommendedSupplier?->nama ?? null,
                'best_margin' => $detail->best_margin_percentage,
                'worst_margin' => $detail->worst_margin_percentage,
            ];
        }

        return $summary;
    }

    /**
     * Static Methods
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $sequence = static::whereDate('created_at', now())->count() + 1;
        
        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }

    public function determinePriority(?Carbon $baseDate = null): ?string
    {
        if (!$this->po_end_date) {
            return null;
        }

        $baseDate = $baseDate ?? now();
        $daysUntilDue = $baseDate->diffInDays($this->po_end_date, false);

        if ($daysUntilDue <= 3) {
            return 'mendesak';
        }

        if ($daysUntilDue <= 7) {
            return 'tinggi';
        }

        if ($daysUntilDue <= 14) {
            return 'normal';
        }

        return 'rendah';
    }

    public function syncPriorityFromSchedule(): void
    {
        $calculated = $this->determinePriority();

        if ($calculated) {
            $this->priority = $calculated;
            $this->priority_calculated_at = now();
        }
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($order) {
            if (!$order->no_order) {
                $order->no_order = static::generateOrderNumber();
            }
        });

        static::saving(function ($order) {
            $order->syncPriorityFromSchedule();
        });
        
        // Remove the problematic updating event that causes recursion
        // Status updates should be handled manually through business logic methods
    }
}