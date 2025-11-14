<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Pengiriman;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama',
        'slug',
        'alamat',
        'no_hp',
        'pic_purchasing_id'
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
     * Relasi ke BahanBakuSupplier
     */
    public function bahanBakuSuppliers()
    {
        return $this->hasMany(BahanBakuSupplier::class);
    }

    /**
     * Relasi ke User (PIC Purchasing)
     */
    public function picPurchasing()
    {
        return $this->belongsTo(\App\Models\User::class, 'pic_purchasing_id');
    }

    /**
     * Accessor untuk total bahan baku
     */
    public function getTotalProdukAttribute()
    {
        return $this->bahanBakuSuppliers()->count();
    }

    /**
     * Accessor untuk total stok
     */
    public function getTotalBarangAttribute()
    {
        return $this->bahanBakuSuppliers()->sum('stok');
    }

    // Scope untuk search
    public function scopeSearch($query, $search)
    {
        return $query->where('nama', 'like', '%' . $search . '%')
                    ->orWhere('alamat', 'like', '%' . $search . '%')
                    ->orWhere('no_hp', 'like', '%' . $search . '%')
                    ->orWhereHas('picPurchasing', function($subQuery) use ($search) {
                        $subQuery->where('nama', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('bahanBakuSuppliers', function($subQuery) use ($search) {
                        $subQuery->where('nama', 'like', '%' . $search . '%');
                    });
    }

    /**
     * Get all pengiriman reviews for this supplier
     */
    public function getPengirimanReviews()
    {
        return Pengiriman::whereHas('pengirimanDetails.bahanBakuSupplier', function($query) {
            $query->where('supplier_id', $this->id);
        })
        ->whereNotNull('rating')
        ->with(['order.klien', 'purchasing', 'pengirimanDetails.bahanBakuSupplier'])
        ->orderBy('created_at', 'desc')
        ->get();
    }

    /**
     * Get average rating for this supplier
     */
    public function getAverageRating()
    {
        $avgRating = Pengiriman::whereHas('pengirimanDetails.bahanBakuSupplier', function($query) {
            $query->where('supplier_id', $this->id);
        })
        ->whereNotNull('rating')
        ->avg('rating');

        return $avgRating ? number_format($avgRating, 1) : null;
    }

    /**
     * Get total reviews count for this supplier
     */
    public function getTotalReviews()
    {
        return Pengiriman::whereHas('pengirimanDetails.bahanBakuSupplier', function($query) {
            $query->where('supplier_id', $this->id);
        })
        ->whereNotNull('rating')
        ->count();
    }

    /**
     * Get pengiriman berhasil count for this supplier
     */
    public function getPengirimanBerhasilCount()
    {
        return Pengiriman::whereHas('pengirimanDetails.bahanBakuSupplier', function($query) {
            $query->where('supplier_id', $this->id);
        })
        ->where('status', 'berhasil')
        ->count();
    }

    /**
     * Get pengiriman gagal count for this supplier
     */
    public function getPengirimanGagalCount()
    {
        return Pengiriman::whereHas('pengirimanDetails.bahanBakuSupplier', function($query) {
            $query->where('supplier_id', $this->id);
        })
        ->where('status', 'gagal')
        ->count();
    }

    /**
     * Get all pengiriman for this supplier (berhasil + gagal)
     */
    public function getAllPengiriman()
    {
        return Pengiriman::whereHas('pengirimanDetails.bahanBakuSupplier', function($query) {
            $query->where('supplier_id', $this->id);
        })
        ->whereIn('status', ['berhasil', 'gagal'])
        ->with(['order.klien', 'purchasing', 'pengirimanDetails.bahanBakuSupplier'])
        ->orderBy('created_at', 'desc')
        ->get();
    }

    /**
     * Resolve child route binding for nested routes
     */
    public function resolveChildRouteBinding($childType, $value, $field)
    {
        switch ($childType) {
            case 'bahanBaku':
                return $this->bahanBakuSuppliers()
                    ->where($field ?? 'slug', $value)
                    ->first();
            default:
                return null;
        }
    }

    /**
     * Relasi ke Penawaran Detail
     */
    public function penawaranDetails()
    {
        return $this->hasMany(PenawaranDetail::class);
    }
}
