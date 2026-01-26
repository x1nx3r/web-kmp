<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetOmsetProcurement extends Model
{
    use HasFactory;

    protected $table = 'target_omset_procurement';

    protected $fillable = [
        'user_id',
        'tahun',
        'persentase_target',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'persentase_target' => 'decimal:2',
    ];

    /**
     * Relationship to user (procurement)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship to creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship to updater
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get all target procurement for a specific year
     */
    public static function getTargetsForYear($year)
    {
        return self::with('user')->where('tahun', $year)->get();
    }

    /**
     * Set or update target for a procurement user
     */
    public static function setTarget($userId, $year, $persentase, $updatedBy = null)
    {
        return self::updateOrCreate(
            [
                'user_id' => $userId,
                'tahun' => $year
            ],
            [
                'persentase_target' => $persentase,
                'updated_by' => $updatedBy,
            ]
        );
    }

    /**
     * Get total persentase for a year (should not exceed 100)
     */
    public static function getTotalPersentaseForYear($year, $excludeUserId = null)
    {
        $query = self::where('tahun', $year);
        
        if ($excludeUserId) {
            $query->where('user_id', '!=', $excludeUserId);
        }
        
        return $query->sum('persentase_target');
    }

    /**
     * Calculate target amount for procurement based on percentage
     */
    public function calculateTargetAmount($targetOmset, $periodeType = 'yearly')
    {
        if (!$targetOmset) {
            return 0;
        }

        $baseTarget = match($periodeType) {
            'weekly' => $targetOmset->target_mingguan,
            'monthly' => $targetOmset->target_bulanan,
            'yearly' => $targetOmset->target_tahunan,
            default => 0,
        };

        return round(($baseTarget * $this->persentase_target) / 100, 2);
    }
}
