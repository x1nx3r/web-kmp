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
        'contact_person_id'
    ];

    protected $dates = ['deleted_at'];

    // Relationship dengan Contact Person
    public function contactPerson()
    {
        return $this->belongsTo(KontakKlien::class, 'contact_person_id');
    }

    // Scope untuk search
    public function scopeSearch($query, $search)
    {
        // Group OR conditions to avoid clobbering other where clauses when combined
        return $query->where(function ($q) use ($search) {
            $q->where('nama', 'like', '%' . $search . '%')
              ->orWhere('cabang', 'like', '%' . $search . '%')
              ->orWhereHas('contactPerson', function ($contactQuery) use ($search) {
                  $contactQuery->where('nama', 'like', '%' . $search . '%')
                              ->orWhere('nomor_hp', 'like', '%' . $search . '%');
              });
        });
    }

    // Relationship dengan Orders (replaces legacy purchaseOrders)
    public function orders()
    {
        return $this->hasMany(Order::class);
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

    // Relationship dengan Penawaran
    public function penawaran()
    {
        return $this->hasMany(Penawaran::class);
    }
}