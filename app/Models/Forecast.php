<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Forecast extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_order_id',
        'purchasing_id',
        'no_forecast',
        'tanggal_forecast',
        'hari_kirim_forecast',
        'total_qty_forecast',
        'total_harga_forecast',
        'status',
        'catatan'
    ];

    protected $casts = [
        'tanggal_forecast' => 'date',
        'total_qty_forecast' => 'decimal:2',
        'total_harga_forecast' => 'decimal:2',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Relasi ke User (Purchasing)
     */
    public function purchasing()
    {
        return $this->belongsTo(User::class, 'purchasing_id');
    }

    /**
     * Relasi ke Forecast Details (One-to-Many)
     */
    public function forecastDetails()
    {
        return $this->hasMany(ForecastDetail::class);
    }

    /**
     * Relasi ke Bahan Baku Suppliers melalui details (Many-to-Many)
     */
    public function bahanBakuSuppliers()
    {
        return $this->belongsToMany(
            BahanBakuSupplier::class,
            'forecast_details',
            'forecast_id',
            'bahan_baku_supplier_id'
        )->withPivot([
            'purchase_order_bahan_baku_id',
            'qty_forecast',
            'harga_satuan_forecast',
            'total_harga_forecast',
            'catatan_detail'
        ])->withTimestamps();
    }

    /**
     * Relasi ke Purchase Order Bahan Baku melalui details (Many-to-Many)
     */
    public function purchaseOrderBahanBakus()
    {
        return $this->belongsToMany(
            PurchaseOrderBahanBaku::class,
            'forecast_details',
            'forecast_id',
            'purchase_order_bahan_baku_id'
        )->withPivot([
            'bahan_baku_supplier_id',
            'qty_forecast',
            'harga_satuan_forecast',
            'total_harga_forecast',
            'catatan_detail'
        ])->withTimestamps();
    }

    /**
     * Relasi ke Purchase Order
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk filter pending
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope untuk filter sukses
     */
    public function scopeSukses($query)
    {
        return $query->where('status', 'sukses');
    }

    /**
     * Scope untuk filter gagal
     */
    public function scopeGagal($query)
    {
        return $query->where('status', 'gagal');
    }

    /**
     * Scope untuk filter berdasarkan purchasing
     */
    public function scopeByPurchasing($query, $purchasingId)
    {
        return $query->where('purchasing_id', $purchasingId);
    }

    /**
     * Scope untuk filter berdasarkan tanggal forecast
     */
    public function scopeByTanggalForecast($query, $startDate, $endDate = null)
    {
        if ($endDate) {
            return $query->whereBetween('tanggal_forecast', [$startDate, $endDate]);
        }
        return $query->whereDate('tanggal_forecast', $startDate);
    }

    /**
     * Accessor untuk format tanggal forecast
     */
    public function getTanggalForecastFormattedAttribute()
    {
        return $this->tanggal_forecast ? Carbon::parse($this->tanggal_forecast)->format('d-m-Y') : null;
    }

    /**
     * Accessor untuk status dalam bahasa Indonesia
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending',
            'sukses' => 'Sukses',
            'gagal' => 'Gagal'
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Accessor untuk total qty forecast formatted
     */
    public function getTotalQtyForecastFormattedAttribute()
    {
        return number_format((float) $this->total_qty_forecast, 2, ',', '.');
    }

    /**
     * Accessor untuk total harga forecast dalam format rupiah
     */
    public function getTotalHargaForecastFormattedAttribute()
    {
        return 'Rp ' . number_format((float) $this->total_harga_forecast, 0, ',', '.');
    }

    /**
     * Status Badge Classes untuk UI
     */
    public function getBadgeClassAttribute()
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'sukses' => 'bg-green-100 text-green-800',
            'gagal' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Status Icon Classes untuk UI
     */
    public function getIconClassAttribute()
    {
        return match($this->status) {
            'pending' => 'fas fa-clock text-yellow-500',
            'sukses' => 'fas fa-check-circle text-green-500',
            'gagal' => 'fas fa-times-circle text-red-500',
            default => 'fas fa-question-circle text-gray-500'
        };
    }

    /**
     * Helper Methods
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isSukses()
    {
        return $this->status === 'sukses';
    }

    public function isGagal()
    {
        return $this->status === 'gagal';
    }

    /**
     * Helper untuk menghitung ulang total dari detail
     */
    public function recalculateTotals()
    {
        $this->total_qty_forecast = $this->forecastDetails()->sum('qty_forecast');
        $this->total_harga_forecast = $this->forecastDetails()->sum('total_harga_forecast');
        $this->save();
        
        return $this;
    }

    /**
     * Helper untuk mendapatkan total items dalam forecast
     */
    public function getTotalItemsAttribute()
    {
        return $this->forecastDetails()->count();
    }

    /**
     * Helper untuk mendapatkan semua bahan baku dalam forecast
     */
    public function getAllBahanBakuAttribute()
    {
        return $this->forecastDetails()
            ->with('bahanBakuSupplier')
            ->get()
            ->pluck('bahanBakuSupplier.nama')
            ->unique()
            ->implode(', ');
    }

    /**
     * Generate nomor forecast otomatis
     */
    public static function generateNoForecast()
    {
        $year = date('Y');
        $month = date('m');
        
        $lastForecast = self::where('no_forecast', 'like', "FC-{$year}{$month}%")
            ->orderBy('no_forecast', 'desc')
            ->first();
            
        if ($lastForecast) {
            $lastNumber = intval(substr($lastForecast->no_forecast, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return "FC-{$year}{$month}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Observer untuk auto-generate nomor forecast
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->no_forecast)) {
                $model->no_forecast = self::generateNoForecast();
            }
        });

        // Update totals ketika detail berubah
        static::saved(function ($model) {
            if ($model->forecastDetails()->exists()) {
                $model->recalculateTotals();
            }
        });
    }
}
