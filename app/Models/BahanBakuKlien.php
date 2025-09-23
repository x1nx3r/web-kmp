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
        'nama',
        'satuan',
        'spesifikasi',
        'status',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Relasi ke Purchase Order Bahan Baku
     */
    public function purchaseOrderBahanBaku()
    {
        return $this->hasMany(PurchaseOrderBahanBaku::class, 'bahan_baku_klien_id');
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
     * Check apakah bahan baku aktif
     */
    public function isAktif()
    {
        return $this->status === 'aktif';
    }
}
