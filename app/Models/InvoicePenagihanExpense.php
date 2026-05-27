<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoicePenagihanExpense extends Model
{
    use HasFactory;

    protected $table = 'invoice_penagihan_expenses';

    protected $fillable = [
        'invoice_penagihan_id',
        'type',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(InvoicePenagihan::class, 'invoice_penagihan_id');
    }
}
