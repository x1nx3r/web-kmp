<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Penawaran extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'penawaran';

    protected $fillable = [
        'nomor_penawaran',
        'klien_id',
        'tanggal_penawaran',
        'tanggal_berlaku_sampai',
        'status',
        'total_revenue',
        'total_cost',
        'total_profit',
        'margin_percentage',
        'created_by',
        'verified_by',
        'verified_at',
        'catatan',
        'alasan_penolakan',
    ];

    protected $casts = [
        'tanggal_penawaran' => 'date',
        'tanggal_berlaku_sampai' => 'date',
        'total_revenue' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'total_profit' => 'decimal:2',
        'margin_percentage' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function klien(): BelongsTo
    {
        return $this->belongsTo(Klien::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(PenawaranDetail::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scopes
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByKlien($query, int $klienId)
    {
        return $query->where('klien_id', $klienId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('tanggal_penawaran', '>=', now()->subDays($days));
    }

    public function scopePending($query)
    {
        return $query->where('status', 'menunggu_verifikasi');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'disetujui');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Accessors
     */
    public function getFormattedTanggalAttribute(): string
    {
        return $this->tanggal_penawaran->format('d M Y');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'draft' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Draft</span>',
            'menunggu_verifikasi' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu Verifikasi</span>',
            'disetujui' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Disetujui</span>',
            'ditolak' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>',
            'expired' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">Expired</span>',
            default => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Unknown</span>',
        };
    }

    public function getTotalMaterialsAttribute(): int
    {
        return $this->details()->count();
    }

    public function getSuppliersCountAttribute(): int
    {
        return $this->details()->distinct('supplier_id')->count('supplier_id');
    }

    /**
     * Methods
     */
    public function calculateTotals(): void
    {
        $totals = $this->details()->selectRaw('
            SUM(subtotal_revenue) as total_revenue,
            SUM(subtotal_cost) as total_cost,
            SUM(subtotal_profit) as total_profit
        ')->first();

        $this->total_revenue = $totals->total_revenue ?? 0;
        $this->total_cost = $totals->total_cost ?? 0;
        $this->total_profit = $totals->total_profit ?? 0;
        
        // Calculate overall margin percentage
        if ($this->total_revenue > 0) {
            $this->margin_percentage = ($this->total_profit / $this->total_revenue) * 100;
        } else {
            $this->margin_percentage = 0;
        }

        $this->save();
    }

    public function generateNomorPenawaran(): string
    {
        $year = now()->year;
        $prefix = "PNW-{$year}-";
        
        // Get the latest sequence number for this year
        $latestPenawaran = static::where('nomor_penawaran', 'like', "{$prefix}%")
            ->orderBy('nomor_penawaran', 'desc')
            ->first();

        if ($latestPenawaran) {
            // Extract sequence number from nomor like "PNW-2025-0001"
            $lastSequence = (int) substr($latestPenawaran->nomor_penawaran, -4);
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }

        return $prefix . str_pad($newSequence, 4, '0', STR_PAD_LEFT);
    }

    public function submitForVerification(): bool
    {
        if ($this->status !== 'draft') {
            return false;
        }

        $this->status = 'menunggu_verifikasi';
        return $this->save();
    }

    public function approve(User $user): bool
    {
        if ($this->status !== 'menunggu_verifikasi') {
            return false;
        }

        $this->status = 'disetujui';
        $this->verified_by = $user->id;
        $this->verified_at = now();
        return $this->save();
    }

    public function reject(User $user, string $reason): bool
    {
        if ($this->status !== 'menunggu_verifikasi') {
            return false;
        }

        $this->status = 'ditolak';
        $this->verified_by = $user->id;
        $this->verified_at = now();
        $this->alasan_penolakan = $reason;
        return $this->save();
    }

    public function getUniqueSuppliers()
    {
        return $this->details()
            ->with('supplier')
            ->get()
            ->pluck('supplier')
            ->unique('id');
    }

    public function getSuppliersSummary(): string
    {
        $suppliers = $this->details()
            ->with('supplier')
            ->select('supplier_id', DB::raw('COUNT(*) as material_count'))
            ->groupBy('supplier_id')
            ->get();

        if ($suppliers->isEmpty()) {
            return 'No suppliers';
        }

        $summary = $suppliers->map(function ($detail) {
            return "{$detail->supplier->nama} ({$detail->material_count} items)";
        })->implode(', ');

        return $summary;
    }

    /**
     * Check if quotation has expired
     */
    public function isExpired(): bool
    {
        return $this->tanggal_berlaku_sampai < now();
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate nomor_penawaran when creating
        static::creating(function ($penawaran) {
            if (empty($penawaran->nomor_penawaran)) {
                $penawaran->nomor_penawaran = $penawaran->generateNomorPenawaran();
            }
        });
    }
}
