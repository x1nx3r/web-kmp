<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BahanBakuKlien extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bahan_baku_klien';

    protected $fillable = [
        'klien_id',
        'nama',
        'satuan',
        'spesifikasi',
        'harga_approved',
        'approved_at',
        'approved_by_marketing',
        'status',
        'post',
        'present',
        'cause',
        'jenis',
    ];

    protected $casts = [
        'harga_approved' => 'decimal:2',
        'approved_at' => 'datetime',
        'post' => 'boolean',
        'jenis' => 'array',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Relasi ke Klien
     */
    public function klien()
    {
        return $this->belongsTo(Klien::class);
    }

    /**
     * Relasi ke Marketing User yang approve
     */
    public function approvedByMarketing()
    {
        return $this->belongsTo(User::class, 'approved_by_marketing');
    }

    /**
     * Relasi ke Purchase Order Bahan Baku
     */
    public function purchaseOrderBahanBaku()
    {
        return $this->hasMany(PurchaseOrderBahanBaku::class, 'bahan_baku_klien_id');
    }

    /**
     * Relasi ke Riwayat Harga (will be added in Step 2)
     */
    public function riwayatHarga()
    {
        return $this->hasMany(RiwayatHargaKlien::class)->orderBy('tanggal_perubahan', 'desc');
    }

    /**
     * Scope untuk filter berdasarkan klien
     */
    public function scopeByKlien($query, $klienId)
    {
        return $query->where('klien_id', $klienId);
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk filter aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope untuk filter yang sudah ada harga approved
     */
    public function scopeWithApprovedPrice($query)
    {
        return $query->whereNotNull('harga_approved');
    }

    /**
     * Check apakah bahan baku aktif
     */
    public function isAktif()
    {
        return $this->status === 'aktif';
    }

    /**
     * Check apakah sudah ada harga approved
     */
    public function hasApprovedPrice()
    {
        return $this->harga_approved !== null;
    }

    /**
     * Get formatted approved price
     */
    public function getFormattedApprovedPriceAttribute()
    {
        return $this->harga_approved ? 'Rp ' . number_format((float) $this->harga_approved, 0, ',', '.') : 'Belum disetujui';
    }

    /**
     * Get formatted approved price with unit
     */
    public function getFormattedPriceWithUnitAttribute()
    {
        return $this->hasApprovedPrice() ? 
            $this->formatted_approved_price . '/' . $this->satuan : 
            'Harga belum disetujui';
    }

    /**
     * Relasi ke Penawaran Detail
     */
    public function penawaranDetails()
    {
        return $this->hasMany(PenawaranDetail::class);
    }

    /**
     * Get suppliers that have matching materials (by name similarity)
     * This is a pseudo-relationship since there's no direct FK
     */
    public function bahanBakuSuppliers()
    {
        // Match by material name similarity
        $materialName = $this->nama;
        $firstWord = trim(explode(' ', $materialName)[0]);
        
        return \App\Models\BahanBakuSupplier::where('nama', 'LIKE', '%' . $materialName . '%')
            ->orWhere('nama', 'LIKE', '%' . $firstWord . '%')
            ->get();
    }

    /**
     * Get suppliers that have matching materials (as relationship)
     */
    public function getMatchingSuppliersAttribute()
    {
        return $this->bahanBakuSuppliers();
    }
}
