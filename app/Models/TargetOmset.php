<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TargetOmset extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "target_omset";

    protected $fillable = [
        "tahun",
        "target_tahunan",
        "target_bulanan",
        "target_mingguan",
        "created_by",
        "updated_by",
    ];

    protected $casts = [
        "tahun" => "integer",
        "target_tahunan" => "decimal:2",
        "target_bulanan" => "decimal:2",
        "target_mingguan" => "decimal:2",
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
        return self::where("tahun", $year)->first();
    }

    /**
     * Set or update target omset for a year
     */
    public static function setTarget($year, $targetTahunan, $createdBy = null)
    {
        $targetMingguan = round($targetTahunan / 52, 2);
        $targetBulanan = round($targetMingguan * 4, 2);

        return self::updateOrCreate(
            ["tahun" => $year],
            [
                "target_tahunan" => $targetTahunan,
                "target_bulanan" => $targetBulanan,
                "target_mingguan" => $targetMingguan,
                "updated_by" => $createdBy,
            ],
        );
    }

    /**
     * Save current progress as snapshot
     */
    public function saveSnapshot(
        $actualOmset,
        $periodeType,
        $bulan = null,
        $minggu = null,
        $createdBy = null,
    ) {
        $targetAmount = match ($periodeType) {
            "weekly" => $this->target_mingguan,
            "monthly" => $this->target_bulanan,
            "yearly" => $this->target_tahunan,
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
            $createdBy,
        );
    }
}
