<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetOmset extends Model
{
    use HasFactory;

    protected $table = 'target_omset';

    protected $fillable = [
        'tahun',
        'target_tahunan',
        'target_bulanan',
        'target_mingguan',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'target_tahunan' => 'decimal:2',
        'target_bulanan' => 'decimal:2',
        'target_mingguan' => 'decimal:2',
    ];

    /**
     * Relationship to snapshots
     */
    public function snapshots()
    {
        return $this->hasMany(TargetOmsetSnapshot::class);
    }

    /**
     * Get target omset for a specific year
     */
    public static function getTargetForYear($year)
    {
        return self::where('tahun', $year)->first();
    }

    /**
     * Set or update target omset for a year
     * Sistem: 1 bulan = 4 minggu, 1 tahun = 48 minggu (12 bulan x 4 minggu)
     */
    public static function setTarget($year, $targetTahunan, $createdBy = null)
    {
        // Target per bulan (12 bulan)
        $targetBulanan = round($targetTahunan / 12, 2);
        
        // Target per minggu (48 minggu = 12 bulan x 4 minggu per bulan)
        $targetMingguan = round($targetTahunan / 48, 2);

        return self::updateOrCreate(
            ['tahun' => $year],
            [
                'target_tahunan' => $targetTahunan,
                'target_bulanan' => $targetBulanan,
                'target_mingguan' => $targetMingguan,
                'updated_by' => $createdBy,
            ]
        );
    }

    /**
     * Save current progress as snapshot
     */
    public function saveSnapshot($actualOmset, $periodeType, $bulan = null, $minggu = null, $createdBy = null)
    {
        $targetAmount = match($periodeType) {
            'weekly' => $this->target_mingguan,
            'monthly' => $this->target_bulanan,
            'yearly' => $this->target_tahunan,
            default => 0,
        };

        return TargetOmsetSnapshot::createSnapshot(
            $this->id,
            $this->tahun,
            $periodeType,
            $targetAmount,
            $actualOmset,
            $bulan,
            $minggu,
            $createdBy
        );
    }
}
