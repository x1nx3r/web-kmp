<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanySettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CompanySetting::create([
            'company_name' => 'PT. KENCANA MAKMUR PRATAMA',
            'company_address' => 'Jl. Contoh No. 123, Jakarta Selatan, DKI Jakarta 12345',
            'company_phone' => '021-12345678',
            'company_email' => 'info@kmp.co.id',
            'company_website' => 'www.kmp.co.id',
            'bank_name' => 'Bank Mandiri',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'PT. KENCANA MAKMUR PRATAMA',
            'tax_number' => '01.234.567.8-901.000',
            'invoice_terms_conditions' => 'Pembayaran dilakukan maksimal pada tanggal jatuh tempo. Keterlambatan pembayaran akan dikenakan denda 2% per bulan.',
            'invoice_footer_notes' => 'Terima kasih atas kepercayaan Anda menggunakan layanan kami.',
            'tax_percentage' => 11.00,
            'invoice_due_days' => 30,
        ]);
    }
}
