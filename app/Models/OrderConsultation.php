<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderConsultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'requested_by',
        'requested_note',
        'requested_at',
        'responded_by',
        'response_type',
        'response_note',
        'responded_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    /**
     * Get the order this consultation belongs to.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who requested the consultation (Marketing).
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who responded to the consultation (Direktur).
     */
    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    /**
     * Check if this consultation has been responded to.
     */
    public function isResponded(): bool
    {
        return $this->responded_at !== null;
    }

    /**
     * Check if this consultation is pending (awaiting response).
     */
    public function isPending(): bool
    {
        return $this->responded_at === null;
    }

    /**
     * Scope to get only pending consultations.
     */
    public function scopePending($query)
    {
        return $query->whereNull('responded_at');
    }

    /**
     * Scope to get only responded consultations.
     */
    public function scopeResponded($query)
    {
        return $query->whereNotNull('responded_at');
    }

    /**
     * Get the response type label in Indonesian.
     */
    public function getResponseTypeLabelAttribute(): ?string
    {
        return match ($this->response_type) {
            'selesai' => 'Setujui Selesai',
            'lanjutkan' => 'Lanjutkan Pengiriman',
            default => null,
        };
    }
}
