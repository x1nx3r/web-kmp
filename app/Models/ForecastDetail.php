<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForecastDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'forecast_details';

    protected $fillable = [
        'forecast_id',
        'purchase_order_bahan_baku_id',
        'bahan_baku_supplier_id',
        'qty_forecast',
        'harga_satuan_forecast',
        'total_harga_forecast',
        'harga_satuan_po',
        'total_harga_po',
        'catatan_detail',
    ];

    protected $casts = [
        'qty_forecast' => 'decimal:2',
        'harga_satuan_forecast' => 'decimal:2',
        'total_harga_forecast' => 'decimal:2',
        'harga_satuan_po' => 'decimal:2',
        'total_harga_po' => 'decimal:2',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Relasi ke Forecast
     */
    public function forecast()
    {
        return $this->belongsTo(Forecast::class);
    }

    /**
     * Relasi ke Bahan Baku Supplier
     */
    public function bahanBakuSupplier()
    {
        return $this->belongsTo(BahanBakuSupplier::class, 'bahan_baku_supplier_id');
    }

    /**
     * Relasi ke Order Detail (menggunakan purchase_order_bahan_baku_id sebagai foreign key)
     */
    public function purchaseOrderBahanBaku()
    {
        return $this->belongsTo(OrderDetail::class, 'purchase_order_bahan_baku_id');
    }

    /**
     * Accessor untuk format qty
     */
    public function getFormattedQtyForecastAttribute()
    {
        return number_format((float) $this->qty_forecast, 2, ',', '.');
    }

    /**
     * Accessor untuk format harga satuan
     */
    public function getFormattedHargaSatuanForecastAttribute()
    {
        return 'Rp ' . number_format((float) $this->harga_satuan_forecast, 0, ',', '.');
    }

    /**
     * Accessor untuk format total harga
     */
    public function getFormattedTotalHargaForecastAttribute()
    {
        return 'Rp ' . number_format((float) $this->total_harga_forecast, 0, ',', '.');
    }

    /**
     * Accessor untuk format harga satuan PO
     */
    public function getFormattedHargaSatuanPoAttribute()
    {
        return 'Rp ' . number_format((float) $this->harga_satuan_po, 0, ',', '.');
    }

    /**
     * Accessor untuk format total harga PO
     */
    public function getFormattedTotalHargaPoAttribute()
    {
        return 'Rp ' . number_format((float) $this->total_harga_po, 0, ',', '.');
    }

    /**
     * Accessor untuk selisih harga (supplier - PO)
     */
    public function getSelisihHargaAttribute()
    {
        return (float) $this->total_harga_forecast - (float) $this->total_harga_po;
    }

    /**
     * Accessor untuk persentase selisih
     */
    public function getPersentaseSelisihAttribute()
    {
        if ($this->total_harga_po > 0) {
            return (($this->selisih_harga / (float) $this->total_harga_po) * 100);
        }
        return 0;
    }

    /**
     * Calculate total harga otomatis
     */
    public function calculateTotalHarga()
    {
        $total = (float) $this->qty_forecast * (float) $this->harga_satuan_forecast;
        $this->attributes['total_harga_forecast'] = number_format($total, 2, '.', '');
        return $this;
    }

    /**
     * Boot method untuk auto calculate total
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Only auto-calculate if total_harga_forecast is not already set
            if ((!$model->total_harga_forecast || $model->total_harga_forecast == 0) && $model->qty_forecast && $model->harga_satuan_forecast) {
                $total = (float) $model->qty_forecast * (float) $model->harga_satuan_forecast;
                $model->total_harga_forecast = $total;
            }
        });

        static::updating(function ($model) {
            // Only auto-calculate if qty or price changed and total is not manually set
            if ($model->isDirty(['qty_forecast', 'harga_satuan_forecast']) && $model->qty_forecast && $model->harga_satuan_forecast) {
                $total = (float) $model->qty_forecast * (float) $model->harga_satuan_forecast;
                $model->total_harga_forecast = $total;
            }
        });
    }
}
