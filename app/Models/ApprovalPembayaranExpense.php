<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalPembayaranExpense extends Model
{
    use HasFactory;

    protected $table = 'approval_pembayaran_expenses';

    protected $fillable = [
        'approval_pembayaran_id',
        'type',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function approvalPembayaran()
    {
        return $this->belongsTo(ApprovalPembayaran::class, 'approval_pembayaran_id');
    }
}
