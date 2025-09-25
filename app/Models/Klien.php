<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Klien extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama',
        'cabang',
        'no_hp'
    ];

    protected $dates = ['deleted_at'];

    // Scope untuk search
    public function scopeSearch($query, $search)
    {
        // Group OR conditions to avoid clobbering other where clauses when combined
        return $query->where(function ($q) use ($search) {
            $q->where('nama', 'like', '%' . $search . '%')
              ->orWhere('cabang', 'like', '%' . $search . '%')
              ->orWhere('no_hp', 'like', '%' . $search . '%');
        });
    }

    // Relationship dengan Purchase Orders
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    // Relationship dengan Bahan Baku Klien (Client Materials)
    public function bahanBakuKliens()
    {
        return $this->hasMany(BahanBakuKlien::class);
    }

    // Get active materials for this client
    public function activeBahanBakuKliens()
    {
        return $this->hasMany(BahanBakuKlien::class)->aktif();
    }

    // Get materials with approved prices
    public function approvedBahanBakuKliens()
    {
        return $this->hasMany(BahanBakuKlien::class)->withApprovedPrice();
    }
}