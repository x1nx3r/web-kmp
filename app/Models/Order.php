<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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
    ];

    protected $casts = [
        'tanggal_order' => 'date',
        'dikonfirmasi_at' => 'datetime',
        'selesai_at' => 'datetime',
        'dibatalkan_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'total_qty' => 'decimal:2',
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

    public function suppliers(): HasManyThrough
    {
        return $this->hasManyThrough(
            Supplier::class,
            OrderDetail::class,
            'order_id',
            'id',
            'id',
            'supplier_id'
        );
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
        return $query->where('margin_percentage', '>=', $threshold);
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

    public function cancel(string $reason = null): bool
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
        
        if ($shippedQty == 0) {
            // No items shipped yet
            if ($this->status === 'sebagian_dikirim') {
                $this->status = 'diproses';
            }
        } elseif ($shippedQty >= $totalQty) {
            // All items shipped
            $this->complete();
        } else {
            // Partially shipped
            $this->status = 'sebagian_dikirim';
        }
        
        $this->save();
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
        
        static::updating(function ($order) {
            // Auto-update completion status based on order details
            if ($order->isDirty(['status']) && $order->status === 'diproses') {
                $order->updateShippingStatus();
            }
        });
    }
}