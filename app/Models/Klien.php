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
        'alamat_lengkap',
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
              ->orWhere('cabang', 'like', '%' . $search . '%');

            // If the 'no_hp' column still exists on the kliens table (legacy), include it in search.
            try {
                if (\Illuminate\Support\Facades\Schema::hasColumn('kliens', 'no_hp')) {
                    $q->orWhere('no_hp', 'like', '%' . $search . '%');
                }
            } catch (\Throwable $e) {
                // If the schema cannot be inspected for any reason, skip direct column search.
            }

            // Also search via related contact person (preferred place for phone numbers)
            $q->orWhereHas('contactPerson', function ($contactQuery) use ($search) {
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

    /**
     * Convenience accessor to get a phone number for the client.
     * Prefers contactPerson.nomor_hp (the new canonical place) and falls
     * back to the legacy kliens.no_hp column if present.
     *
     * Usage: $klien->phone
     */
    public function getPhoneAttribute()
    {
        // If contact person relation is loaded and has a phone, use it
        if ($this->relationLoaded('contactPerson') && $this->contactPerson && !empty($this->contactPerson->nomor_hp)) {
            return $this->contactPerson->nomor_hp;
        }

        // Fallback to the legacy column if present on the model
        return $this->attributes['no_hp'] ?? null;
    }
}