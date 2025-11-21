<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'pengiriman_id',
        'supplier_id',
        'evaluated_by',
        'total_score',
        'rating',
        'ulasan',
        'catatan_tambahan',
        'evaluated_at',
    ];

    protected $casts = [
        'total_score' => 'decimal:2',
        'evaluated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function pengiriman(): BelongsTo
    {
        return $this->belongsTo(Pengiriman::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(SupplierEvaluationDetail::class);
    }

    /**
     * Calculate total score and rating from details
     */
    public function calculateScoreAndRating(): void
    {
        $details = $this->details;
        
        if ($details->isEmpty()) {
            return;
        }

        // Calculate average score
        $totalScore = $details->avg('penilaian');
        $this->total_score = round($totalScore, 2);
        
        // Convert to rating (1-5 stars)
        $this->rating = (int) round($totalScore);
        
        // Generate auto review
        $this->ulasan = $this->generateAutoReview();
        
        $this->save();
    }

    /**
     * Generate automatic review text based on score
     */
    public function generateAutoReview(): string
    {
        $score = $this->total_score;
        
        if ($score >= 4.5) {
            return 'Excellent! Supplier sangat memuaskan di semua aspek.';
        } elseif ($score >= 4.0) {
            return 'Very Good! Supplier sangat baik dan dapat diandalkan.';
        } elseif ($score >= 3.5) {
            return 'Good! Supplier baik dengan beberapa area yang bisa ditingkatkan.';
        } elseif ($score >= 3.0) {
            return 'Fair. Supplier cukup baik namun perlu peningkatan di beberapa aspek.';
        } elseif ($score >= 2.0) {
            return 'Poor. Supplier perlu banyak perbaikan.';
        } else {
            return 'Very Poor. Supplier tidak memenuhi standar yang diharapkan.';
        }
    }

    /**
     * Get evaluation criteria structure
     */
    public static function getCriteriaStructure(): array
    {
        return [
            'Harga' => [
                'Sesuai komponen kontrak',
                'Kompetitif',
                'Tidak ada komponen tidak wajar',
                'Stabil / tidak fluktuatif tajam',
            ],
            'Kualitas' => [
                'Sesuai spesifikasi',
                'Tidak ada cacat',
                'Konsisten kualitas',
                'Memenuhi standar perusahaan',
            ],
            'Kuantitas' => [
                'Sesuai permintaan',
                'Tidak kurang kirim',
                'Tidak lebih kirim (over supply)',
                'Tidak sering ada selisih',
            ],
            'Pengiriman' => [
                'Tepat waktu',
                'Tidak delay signifikan',
                'Kemampuan pengiriman mendesak',
                'Fleksibilitas jadwal',
            ],
            'Kontinuitas Supply' => [
                'Stabil',
                'Tidak sering putus supply',
                'Mampu follow-up',
                'Standby saat urgent',
            ],
            'Service' => [
                'Respons cepat',
                'Komunikasi jelas',
                'Proaktif',
                'Support teknis (jika ada)',
            ],
            'Kepatuhan & Legalitas' => [
                'Lengkap dokumen legal',
                'Tidak ada pelanggaran',
                'Kepatuhan terhadap regulasi K3L',
                'Audit / evaluasi sebelumnya baik',
            ],
        ];
    }
}
