<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RiwayatHargaKlien extends Model
{
    use HasFactory;

    protected $table = 'riwayat_harga_klien';

    protected $fillable = [
        'bahan_baku_klien_id',
        'harga_lama',
        'harga_approved_baru',
        'selisih_harga',
        'persentase_perubahan',
        'tipe_perubahan',
        'keterangan',
        'tanggal_perubahan',
        'updated_by_marketing',
    ];

    protected $casts = [
        'harga_lama' => 'decimal:2',
        'harga_approved_baru' => 'decimal:2',
        'selisih_harga' => 'decimal:2',
        'persentase_perubahan' => 'decimal:4',
        'tanggal_perubahan' => 'datetime',
    ];

    /**
     * Relationship with BahanBakuKlien
     */
    public function bahanBakuKlien()
    {
        return $this->belongsTo(BahanBakuKlien::class);
    }

    /**
     * Relationship with Marketing user who updated
     */
    public function updatedByMarketing()
    {
        return $this->belongsTo(User::class, 'updated_by_marketing');
    }

    /**
     * Scope untuk mendapatkan riwayat berdasarkan rentang tanggal
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_perubahan', [$startDate, $endDate]);
    }

    /**
     * Scope untuk mendapatkan riwayat berdasarkan tipe perubahan
     */
    public function scopeByTipePerubahan($query, $tipe)
    {
        return $query->where('tipe_perubahan', $tipe);
    }

    /**
     * Scope untuk mendapatkan riwayat berdasarkan bahan baku klien
     */
    public function scopeByBahanBakuKlien($query, $bahanBakuKlienId)
    {
        return $query->where('bahan_baku_klien_id', $bahanBakuKlienId);
    }

    /**
     * Get formatted old price
     */
    public function getFormattedHargaLamaAttribute()
    {
        return $this->harga_lama ? 'Rp ' . number_format((float) $this->harga_lama, 0, ',', '.') : 'N/A';
    }

    /**
     * Get formatted new price
     */
    public function getFormattedHargaBaruAttribute()
    {
        return 'Rp ' . number_format((float) $this->harga_approved_baru, 0, ',', '.');
    }

    /**
     * Get formatted price difference
     */
    public function getFormattedSelisihHargaAttribute()
    {
        $prefix = $this->selisih_harga >= 0 ? '+' : '';
        return $prefix . 'Rp ' . number_format((float) $this->selisih_harga, 0, ',', '.');
    }

    /**
     * Get color based on change type
     */
    public function getChangeColorAttribute()
    {
        return match($this->tipe_perubahan) {
            'naik' => 'red',
            'turun' => 'green',
            'tetap' => 'blue',
            'awal' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Get change icon based on type
     */
    public function getChangeIconAttribute()
    {
        return match($this->tipe_perubahan) {
            'naik' => '↗️',
            'turun' => '↘️',
            'tetap' => '➡️',
            'awal' => '🆕',
            default => '➡️'
        };
    }

    /**
     * Static method to create price history record
     */
    public static function createPriceHistory($bahanBakuKlienId, $hargaBaru, $marketingUserId, $keterangan = null, $tanggal = null)
    {
        // Get current price from bahan_baku_klien
        $bahanBaku = BahanBakuKlien::find($bahanBakuKlienId);
        $hargaLama = $bahanBaku->harga_approved;

        // Calculate difference and percentage
        $selisihHarga = $hargaBaru - ($hargaLama ?? 0);
        
        $persentasePerubahan = 0;
        if ($hargaLama && $hargaLama > 0) {
            $persentasePerubahan = ($selisihHarga / $hargaLama) * 100;
        }

        // Determine change type
        $tipePerubahan = 'awal';
        if ($hargaLama) {
            if ($selisihHarga > 0) {
                $tipePerubahan = 'naik';
            } elseif ($selisihHarga < 0) {
                $tipePerubahan = 'turun';
            } else {
                $tipePerubahan = 'tetap';
            }
        }

        // Create history record
        return self::create([
            'bahan_baku_klien_id' => $bahanBakuKlienId,
            'harga_lama' => $hargaLama,
            'harga_approved_baru' => $hargaBaru,
            'selisih_harga' => $selisihHarga,
            'persentase_perubahan' => $persentasePerubahan,
            'tipe_perubahan' => $tipePerubahan,
            'keterangan' => $keterangan,
            'tanggal_perubahan' => $tanggal ?? now(),
            'updated_by_marketing' => $marketingUserId,
        ]);
    }

    /**
     * Get price trend data for charts
     */
    public function scopeForChart($query, $bahanBakuKlienId, $days = 30)
    {
        return $query->where('bahan_baku_klien_id', $bahanBakuKlienId)
                    ->where('tanggal_perubahan', '>=', Carbon::now()->subDays($days))
                    ->orderBy('tanggal_perubahan', 'asc')
                    ->select('tanggal_perubahan', 'harga_approved_baru');
    }
}