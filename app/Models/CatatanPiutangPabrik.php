<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class CatatanPiutangPabrik extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "catatan_piutang_pabriks";

    protected $fillable = [
        "klien_id",
        "no_invoice",
        "tanggal_invoice",
        "tanggal_jatuh_tempo",
        "jumlah_piutang",
        "jumlah_dibayar",
        "sisa_piutang",
        "status",
        "hari_keterlambatan",
        "keterangan",
        "bukti_transaksi",
        "created_by",
        "updated_by",
    ];

    protected $casts = [
        "tanggal_invoice" => "date",
        "tanggal_jatuh_tempo" => "date",
        "jumlah_piutang" => "decimal:2",
        "jumlah_dibayar" => "decimal:2",
        "sisa_piutang" => "decimal:2",
        "hari_keterlambatan" => "integer",
    ];

    /**
     * Relasi ke Klien
     */
    public function klien()
    {
        return $this->belongsTo(Klien::class, "klien_id");
    }

    /**
     * Relasi ke User (created_by)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, "created_by");
    }

    /**
     * Relasi ke User (updated_by)
     */
    public function updater()
    {
        return $this->belongsTo(User::class, "updated_by");
    }

    /**
     * Update status berdasarkan jatuh tempo
     */
    public function updateStatus()
    {
        $today = Carbon::now()->startOfDay();
        $jatuhTempo = Carbon::parse($this->tanggal_jatuh_tempo)->startOfDay();

        // Jika sudah lunas, status tidak berubah
        if ($this->status === "lunas") {
            return;
        }

        // Hitung hari keterlambatan
        if ($today->gt($jatuhTempo)) {
            $this->hari_keterlambatan = $today->diffInDays($jatuhTempo);

            if ($this->sisa_piutang > 0) {
                $this->status = "terlambat";
            }
        } elseif ($today->eq($jatuhTempo)) {
            $this->hari_keterlambatan = 0;
            $this->status =
                $this->jumlah_dibayar > 0 ? "cicilan" : "jatuh_tempo";
        } else {
            $this->hari_keterlambatan = 0;
            $this->status =
                $this->jumlah_dibayar > 0 ? "cicilan" : "belum_jatuh_tempo";
        }

        $this->save();
    }

    /**
     * Update sisa piutang dan status
     */
    public function updateSisaPiutang($jumlahBayar)
    {
        $this->jumlah_dibayar += $jumlahBayar;
        $this->sisa_piutang = $this->jumlah_piutang - $this->jumlah_dibayar;

        // Update status
        if ($this->sisa_piutang <= 0) {
            $this->status = "lunas";
            $this->hari_keterlambatan = 0;
        } else {
            $this->updateStatus();
        }

        $this->save();
    }
}
