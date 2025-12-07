<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah enum refraksi_type di tabel approval_pembayaran
        DB::statement("ALTER TABLE approval_pembayaran MODIFY COLUMN refraksi_type ENUM('qty', 'rupiah', 'lainnya') NULL");

        // Ubah enum refraksi_type di tabel invoice_penagihan
        DB::statement("ALTER TABLE invoice_penagihan MODIFY COLUMN refraksi_type ENUM('qty', 'rupiah', 'lainnya') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan enum ke nilai semula (tanpa 'lainnya')
        DB::statement("ALTER TABLE approval_pembayaran MODIFY COLUMN refraksi_type ENUM('qty', 'rupiah') NULL");
        DB::statement("ALTER TABLE invoice_penagihan MODIFY COLUMN refraksi_type ENUM('qty', 'rupiah') NULL");
    }
};
