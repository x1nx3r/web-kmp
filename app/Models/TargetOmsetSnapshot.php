<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TargetOmsetSnapshot extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "target_omset_snapshots";

    protected $fillable = [
        "target_omset_id",
        "tahun",
        "bulan",
        "minggu",
        "periode_type",
        "target_amount",
        "actual_omset",
        "progress_percentage",
        "selisih",
        "status",
        "snapshot_at",
        "created_by",
    ];

    protected $casts = [
        "tahun" => "integer",
        "bulan" => "integer",
        "minggu" => "integer",
        "target_amount" => "decimal:2",
        "actual_omset" => "decimal:2",
        "progress_percentage" => "decimal:2",
        "selisih" => "decimal:2",
        "snapshot_at" => "datetime",
    ];

    /**
     * Relationship to TargetOmset
     */
    public function targetOmset()
    {
        return $this->belongsTo(TargetOmset::class);
    }

    /**
     * Create or update snapshot for a specific period
     */
    public static function createSnapshot(
        $targetOmsetId,
        $tahun,
        $periodeType,
        $targetAmount,
        $actualOmset,
        $bulan = null,
        $minggu = null,
        $createdBy = null,
    ) {
        $progressPercentage =
            $targetAmount > 0 ? ($actualOmset / $targetAmount) * 100 : 0;
        $selisih = $actualOmset - $targetAmount;

        // Determine status
        if ($progressPercentage >= 100) {
            $status = "tercapai";
        } elseif ($progressPercentage >= 75) {
            $status = "on_track";
        } elseif ($progressPercentage > 0) {
            $status = "perlu_boost";
        } else {
            $status = "belum_ada_data";
        }

        // Create unique identifier for snapshot
        $conditions = [
            "target_omset_id" => $targetOmsetId,
            "tahun" => $tahun,
            "periode_type" => $periodeType,
        ];

        if ($bulan) {
            $conditions["bulan"] = $bulan;
        }
        if ($minggu) {
            $conditions["minggu"] = $minggu;
        }

        return self::updateOrCreate($conditions, [
            "target_amount" => $targetAmount,
            "actual_omset" => $actualOmset,
            "progress_percentage" => $progressPercentage,
            "selisih" => $selisih,
            "status" => $status,
            "snapshot_at" => now(),
            "created_by" => $createdBy,
        ]);
    }

    /**
     * Get snapshots for a specific year
     */
    public static function getYearlySnapshots($tahun)
    {
        return self::where("tahun", $tahun)
            ->where("periode_type", "yearly")
            ->orderBy("snapshot_at", "desc")
            ->get();
    }

    /**
     * Get monthly snapshots for a specific year
     */
    public static function getMonthlySnapshots($tahun)
    {
        return self::where("tahun", $tahun)
            ->where("periode_type", "monthly")
            ->orderBy("bulan")
            ->get();
    }

    /**
     * Get weekly snapshots for a specific year and month
     */
    public static function getWeeklySnapshots($tahun, $bulan = null)
    {
        $query = self::where("tahun", $tahun)->where("periode_type", "weekly");

        if ($bulan) {
            $query->where("bulan", $bulan);
        }

        return $query->orderBy("minggu")->get();
    }

    /**
     * Get comparison between current and previous period
     */
    public static function getComparison($tahun, $periodeType, $bulan = null)
    {
        $current = self::where("tahun", $tahun)->where(
            "periode_type",
            $periodeType,
        );

        if ($bulan) {
            $current->where("bulan", $bulan);
        }

        $current = $current->latest("snapshot_at")->first();

        $previous = self::where("tahun", $tahun - 1)->where(
            "periode_type",
            $periodeType,
        );

        if ($bulan) {
            $previous->where("bulan", $bulan);
        }

        $previous = $previous->latest("snapshot_at")->first();

        return [
            "current" => $current,
            "previous" => $previous,
            "growth" =>
                $current && $previous
                    ? (($current->actual_omset - $previous->actual_omset) /
                            $previous->actual_omset) *
                        100
                    : null,
        ];
    }
}
