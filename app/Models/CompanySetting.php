<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    use HasFactory;

    protected $table = 'company_settings';

    protected $fillable = [
        'company_name',
        'company_address',
        'company_phone',
        'company_email',
        'company_website',
        'company_logo',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'tax_number',
        'invoice_terms_conditions',
        'invoice_footer_notes',
        'tax_percentage',
        'invoice_due_days',
    ];

    protected $casts = [
        'tax_percentage' => 'decimal:2',
        'invoice_due_days' => 'integer',
    ];

    /**
     * Get the first (and only) company setting
     */
    public static function getSettings()
    {
        return self::first() ?? self::create([
            'company_name' => 'PT. Example',
            'company_address' => 'Alamat perusahaan',
            'company_phone' => '021-1234567',
            'company_email' => 'info@example.com',
            'tax_percentage' => 11.00,
            'invoice_due_days' => 30,
        ]);
    }
}
