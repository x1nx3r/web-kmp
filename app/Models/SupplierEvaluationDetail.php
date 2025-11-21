<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierEvaluationDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_evaluation_id',
        'kriteria',
        'sub_kriteria',
        'penilaian',
        'keterangan',
    ];

    protected $casts = [
        'penilaian' => 'integer',
    ];

    /**
     * Relationships
     */
    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(SupplierEvaluation::class, 'supplier_evaluation_id');
    }
}
