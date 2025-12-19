<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OmsetManual extends Model
{
    protected $table = 'omset_manual';
    
    protected $fillable = [
        'tahun',
        'bulan',
        'omset_manual',
        'catatan',
        'created_by',
        'updated_by'
    ];
    
    protected $casts = [
        'tahun' => 'integer',
        'bulan' => 'integer',
        'omset_manual' => 'decimal:2'
    ];
    
    // Relationship dengan user yang membuat
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    // Relationship dengan user yang mengupdate
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
