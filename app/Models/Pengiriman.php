<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Pengiriman extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pengiriman';

    protected $fillable = [
        'purchase_order_id',
        'purchasing_id',
        'forecast_id',
        'no_pengiriman',
        'tanggal_kirim',
        'hari_kirim',
        'total_qty_kirim',
        'total_harga_kirim',
        'bukti_foto_bongkar',
        'bukti_foto_bongkar_uploaded_at',
        'foto_tanda_terima',
        'foto_tanda_terima_uploaded_at',
        'status',
        'catatan',
        'catatan_refraksi',
        'rating',
        'ulasan',
    ];

    protected $casts = [
        'tanggal_kirim' => 'date',
        'total_qty_kirim' => 'decimal:2',
        'total_harga_kirim' => 'decimal:2',
        'total_qty_sisa' => 'decimal:2',
        'bukti_foto_bongkar_uploaded_at' => 'datetime',
        'foto_tanda_terima_uploaded_at' => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Relasi ke User (Purchasing)
     */
    public function purchasing()
    {
        return $this->belongsTo(User::class, 'purchasing_id');
    }

    /**
     * Relasi ke Forecast
     */
    public function forecast()
    {
        return $this->belongsTo(Forecast::class, 'forecast_id');
    }

    /**
     * Relasi ke Pengiriman Details (One-to-Many)
     */
    public function pengirimanDetails()
    {
        return $this->hasMany(PengirimanDetail::class);
    }

    /**
     * Alias for pengirimanDetails
     */
    public function details()
    {
        return $this->hasMany(PengirimanDetail::class);
    }

    /**
     * Relasi ke Purchase Order (Legacy)
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(Order::class, 'purchase_order_id');
    }

    /**
     * Relasi ke Order (New)
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'purchase_order_id');
    }

    /**
     * Relasi ke Klien melalui Purchase Order
     */
    public function klien()
    {
        return $this->hasOneThrough(
            Klien::class,
            Order::class,
            'id', // Foreign key on orders table
            'id', // Foreign key on klien table
            'purchase_order_id', // Local key on pengiriman table
            'klien_id' // Local key on orders table
        );
    }

    /**
     * Relasi ke Approval Pembayaran
     */
    public function approvalPembayaran()
    {
        return $this->hasOne(ApprovalPembayaran::class);
    }

    /**
     * Relasi ke Supplier Evaluation
     */
    public function supplierEvaluation()
    {
        return $this->hasOne(SupplierEvaluation::class);
    }

    /**
     * Relasi ke Invoice Penagihan
     */
    public function invoicePenagihan()
    {
        return $this->hasOne(InvoicePenagihan::class);
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk filter pending
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope untuk filter terkirim
     */
    public function scopeTerkirim($query)
    {
        return $query->where('status', 'terkirim');
    }

    /**
     * Scope untuk filter diverifikasi
     */
    public function scopeDiverifikasi($query)
    {
        return $query->where('status', 'diverifikasi');
    }

    /**
     * Scope untuk filter yang sudah dikirim (terkirim + diverifikasi)
     */
    public function scopeSudahKirim($query)
    {
        return $query->whereIn('status', ['terkirim', 'diverifikasi']);
    }

    /**
     * Accessor untuk format tanggal kirim
     */
    public function getTanggalKirimFormattedAttribute()
    {
        return $this->tanggal_kirim ? Carbon::parse($this->tanggal_kirim)->format('d-m-Y') : null;
    }

    /**
     * Accessor untuk format hari kirim
     */
    public function getHariKirimFormattedAttribute()
    {
        return $this->hari_kirim ? Carbon::parse($this->hari_kirim)->format('d-m-Y') : null;
    }

    /**
     * Accessor untuk status dalam bahasa Indonesia
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending',
            'terkirim' => 'Terkirim',
            'diverifikasi' => 'Diverifikasi'
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Accessor untuk total harga dalam format rupiah
     */
    public function getTotalHargaKirimFormattedAttribute()
    {
        return $this->total_harga_kirim ? 'Rp ' . number_format((float) $this->total_harga_kirim, 0, ',', '.') : null;
    }

    /**
     * Helper untuk menghitung ulang total dari detail
     */
    public function recalculateTotals()
    {
        $this->total_qty_kirim = $this->pengirimanDetails()->sum('qty_kirim');
        $this->total_harga_kirim = $this->pengirimanDetails()->sum('total_harga');
        $this->save();
    }

    /**
     * Check apakah pengiriman sudah lengkap
     */
    public function isComplete()
    {
        return $this->status === 'diverifikasi';
    }

    /**
     * Check apakah pengiriman sudah dikirim
     */
    public function isDelivered()
    {
        return in_array($this->status, ['terkirim', 'diverifikasi']);
    }

    /**
     * Check apakah masih ada sisa qty
     */
    public function hasSisa()
    {
        return $this->total_qty_sisa > 0;
    }

    /**
     * Get bukti foto bongkar raw value
     */
    public function getBuktiFotoBongkarRawAttribute()
    {
        return $this->attributes['bukti_foto_bongkar'] ?? null;
    }

    /**
     * Get bukti foto bongkar sebagai array
     */
    public function getBuktiFotoBongkarArrayAttribute()
    {
        $value = $this->attributes['bukti_foto_bongkar'] ?? null;

        if (!$value) {
            return [];
        }

        // Jika string JSON, decode
        if (is_string($value) && (str_starts_with($value, '[') || str_starts_with($value, '{'))) {
            try {
                $decoded = json_decode($value, true);
                return is_array($decoded) ? $decoded : [$value];
            } catch (\Exception $e) {
                return [$value];
            }
        }

        // Jika string biasa, return sebagai array dengan satu element
        return [$value];
    }

    /**
     * Set bukti foto bongkar
     */
    public function setBuktiFotoBongkarAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['bukti_foto_bongkar'] = json_encode($value);
        } elseif (is_string($value)) {
            $this->attributes['bukti_foto_bongkar'] = $value;
        } else {
            $this->attributes['bukti_foto_bongkar'] = null;
        }
    }

    /**
     * Accessor untuk URL foto bukti bongkar
     */
    public function getBuktiFotoBongkarUrlAttribute()
    {
        $photos = $this->bukti_foto_bongkar_array;

        if (!$photos || empty($photos)) {
            return [];
        }

        // Return array URLs
        return array_map(function($photo) {
            return asset('storage/pengiriman/bukti/' . $photo);
        }, $photos);
    }

    /**
     * Get foto bukti paths untuk storage
     */
    public function getBuktiFotoBongkarPathsAttribute()
    {
        $photos = $this->bukti_foto_bongkar_array;

        if (!$photos || empty($photos)) {
            return [];
        }

        return array_map(function($photo) {
            return 'pengiriman/bukti/' . $photo;
        }, $photos);
    }

    /**
     * Generate nomor pengiriman otomatis
     * Format: PGR/YYYYMM/XXXX
     * Contoh: PGR/202511/0001
     */
    public static function generateNoPengiriman()
    {
        $yearMonth = now()->format('Ym'); // Format: 202511
        $prefix = "PGR/{$yearMonth}/";

        // Cari nomor pengiriman terakhir di bulan ini
        $lastPengiriman = self::where('no_pengiriman', 'LIKE', $prefix . '%')
            ->orderBy('no_pengiriman', 'desc')
            ->first();

        if ($lastPengiriman) {
            // Extract nomor urut dari no_pengiriman terakhir
            $lastNumber = (int) substr($lastPengiriman->no_pengiriman, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            // Jika belum ada, mulai dari 0001
            $newNumber = '0001';
        }

        return $prefix . $newNumber;
    }

    /**
     * Accessor untuk URL foto tanda terima
     */
    public function getFotoTandaTerimaUrlAttribute()
    {
        if (!$this->foto_tanda_terima) {
            return null;
        }

        return asset('storage/pengiriman/tanda-terima/' . $this->foto_tanda_terima);
    }

    /**
     * Get foto tanda terima path untuk storage
     */
    public function getFotoTandaTerimaPathAttribute()
    {
        if (!$this->foto_tanda_terima) {
            return null;
        }

        return 'pengiriman/tanda-terima/' . $this->foto_tanda_terima;
    }
}
