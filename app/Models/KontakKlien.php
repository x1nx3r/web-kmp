<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KontakKlien extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kontak_klien';

    protected $fillable = [
        'nama',
        'klien_nama',
        'nomor_hp',
        'jabatan',
        'catatan',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Get all clients that have contacts
     */
    public static function getClientNames()
    {
        return self::distinct('klien_nama')
            ->orderBy('klien_nama')
            ->pluck('klien_nama')
            ->toArray();
    }

    /**
     * Get contacts for a specific client
     */
    public static function getContactsByClient($klienNama)
    {
        return self::where('klien_nama', $klienNama)
            ->orderBy('nama')
            ->get();
    }
}
