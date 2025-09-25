<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BahanBakuSupplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bahan_baku_supplier';

    protected $fillable = [
        'supplier_id',
        'nama',
        'slug',
        'harga_per_satuan',
        'satuan',
        'stok'
    ];

    protected $casts = [
        'harga_per_satuan' => 'decimal:2',
        'stok' => 'decimal:2',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Generate unique slug for bahan baku
     */
    public static function generateUniqueSlug($nama, $supplierId, $excludeId = null)
    {
        $baseSlug = \Str::slug($nama);
        $slug = $baseSlug;
        $counter = 1;

        // Cek unik secara global, bukan per supplier
        $query = self::where('slug', $slug);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
            
            $query = self::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

    /**
     * Relasi ke Supplier
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Relasi ke RiwayatHarga
     */
    public function riwayatHarga()
    {
        return $this->hasMany(RiwayatHargaBahanBaku::class, 'bahan_baku_supplier_id')->orderBy('tanggal_perubahan', 'desc');
    }

    /**
     * Relasi ke Forecast Details
     */
    public function forecastDetails()
    {
        return $this->hasMany(ForecastDetail::class);
    }

    /**
     * Get riwayat harga dalam format untuk grafik
     */
    public function getRiwayatHargaForChart()
    {
        return $this->riwayatHarga()
            ->select('harga_baru as harga', 'tanggal_perubahan as tanggal')
            ->orderBy('tanggal_perubahan', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'harga' => (float) $item->harga,
                    'tanggal' => $item->tanggal->format('Y-m-d')
                ];
            })->toArray();
    }

    /**
     * Accessor untuk format harga
     */
    public function getFormattedHargaAttribute()
    {
        return 'Rp ' . number_format((float) $this->harga_per_satuan, 0, ',', '.');
    }

    /**
     * Accessor untuk format stok
     */
    public function getFormattedStokAttribute()
    {
        return number_format((float) $this->stok, 0, ',', '.') . ' ' . $this->satuan;
    }

    /**
     * Scope untuk pencarian bahan baku
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('nama', 'like', '%' . $search . '%')
                    ->orWhere('satuan', 'like', '%' . $search . '%');
    }

    /**
     * Scope untuk filter berdasarkan supplier
     */
    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    /**
     * Scope untuk filter berdasarkan stok minimum
     */
    public function scopeStokMinimum($query, $minimum = 0)
    {
        return $query->where('stok', '>=', $minimum);
    }
}
