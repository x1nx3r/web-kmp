<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class RiwayatHargaBahanBaku extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "riwayat_harga_bahan_baku";

    protected $fillable = [
        "bahan_baku_supplier_id",
        "harga_lama",
        "harga_baru",
        "selisih_harga",
        "persentase_perubahan",
        "tipe_perubahan",
        "keterangan",
        "tanggal_perubahan",
        "updated_by",
    ];

    protected $casts = [
        "harga_lama" => "decimal:2",
        "harga_baru" => "decimal:2",
        "selisih_harga" => "decimal:2",
        "persentase_perubahan" => "decimal:4",
        "tanggal_perubahan" => "datetime",
    ];

    /**
     * Relationship dengan BahanBakuSupplier
     */
    public function bahanBakuSupplier()
    {
        return $this->belongsTo(BahanBakuSupplier::class);
    }

    /**
     * Scope untuk mendapatkan riwayat berdasarkan rentang tanggal
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween("tanggal_perubahan", [
            $startDate,
            $endDate,
        ]);
    }

    /**
     * Scope untuk mendapatkan riwayat dalam X hari terakhir
     */
    public function scopeLastDays($query, $days = 30)
    {
        return $query->where(
            "tanggal_perubahan",
            ">=",
            Carbon::now()->subDays($days),
        );
    }

    /**
     * Scope untuk mendapatkan riwayat berdasarkan tipe perubahan
     */
    public function scopeByTipePerubahan($query, $tipe)
    {
        return $query->where("tipe_perubahan", $tipe);
    }

    /**
     * Get formatted harga lama
     */
    public function getFormattedHargaLamaAttribute()
    {
        return $this->harga_lama
            ? "Rp " . number_format((float) $this->harga_lama, 0, ",", ".")
            : "-";
    }

    /**
     * Get formatted harga baru
     */
    public function getFormattedHargaBaruAttribute()
    {
        return "Rp " . number_format((float) $this->harga_baru, 0, ",", ".");
    }

    /**
     * Get formatted selisih harga
     */
    public function getFormattedSelisihHargaAttribute()
    {
        $prefix = $this->selisih_harga > 0 ? "+" : "";
        return $prefix .
            "Rp " .
            number_format((float) $this->selisih_harga, 0, ",", ".");
    }

    /**
     * Get formatted persentase perubahan
     */
    public function getFormattedPersentasePerubahanAttribute()
    {
        if ($this->tipe_perubahan === "awal") {
            return "-";
        }
        $prefix = $this->persentase_perubahan > 0 ? "+" : "";
        return $prefix .
            number_format((float) $this->persentase_perubahan, 2) .
            "%";
    }

    /**
     * Get color class based on tipe perubahan
     */
    public function getColorClassAttribute()
    {
        return match ($this->tipe_perubahan) {
            "naik" => "text-red-600",
            "turun" => "text-green-600",
            "tetap" => "text-gray-600",
            "awal" => "text-blue-600",
            default => "text-gray-600",
        };
    }

    /**
     * Get badge class based on tipe perubahan
     */
    public function getBadgeClassAttribute()
    {
        return match ($this->tipe_perubahan) {
            "naik" => "bg-green-100 text-green-800",
            "turun" => "bg-red-100 text-red-800",
            "tetap" => "bg-gray-100 text-gray-800",
            "awal" => "bg-blue-100 text-blue-800",
            default => "bg-gray-100 text-gray-800",
        };
    }

    /**
     * Get icon based on tipe perubahan
     */
    public function getIconAttribute()
    {
        return match ($this->tipe_perubahan) {
            "naik" => "fas fa-arrow-up",
            "turun" => "fas fa-arrow-down",
            "tetap" => "fas fa-minus",
            "awal" => "fas fa-plus-circle",
            default => "fas fa-minus",
        };
    }

    /**
     * Static method untuk mencatat perubahan harga
     */
    public static function catatPerubahanHarga(
        $bahanBakuSupplierId,
        $hargaLama,
        $hargaBaru,
        $keterangan = null,
        $updatedBy = null,
        $tanggal = null,
    ) {
        // Hitung selisih dan persentase
        $selisihHarga = $hargaBaru - ($hargaLama ?? 0);
        $persentasePerubahan = 0;

        if ($hargaLama && $hargaLama > 0) {
            $persentasePerubahan = ($selisihHarga / $hargaLama) * 100;
        }

        // Tentukan tipe perubahan
        $tipePerubahan = "awal";
        if ($hargaLama !== null) {
            if ($hargaBaru > $hargaLama) {
                $tipePerubahan = "naik";
            } elseif ($hargaBaru < $hargaLama) {
                $tipePerubahan = "turun";
            } else {
                $tipePerubahan = "tetap";
            }
        }

        return self::create([
            "bahan_baku_supplier_id" => $bahanBakuSupplierId,
            "harga_lama" => $hargaLama,
            "harga_baru" => $hargaBaru,
            "selisih_harga" => $selisihHarga,
            "persentase_perubahan" => $persentasePerubahan,
            "tipe_perubahan" => $tipePerubahan,
            "keterangan" => $keterangan,
            "tanggal_perubahan" => $tanggal ?? now(),
            "updated_by" => $updatedBy,
        ]);
    }
}
