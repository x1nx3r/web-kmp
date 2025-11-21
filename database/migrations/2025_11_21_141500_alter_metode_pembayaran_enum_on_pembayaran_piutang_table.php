<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE pembayaran_piutang MODIFY metode_pembayaran ENUM('tunai','transfer','cek','giro','potong_pembayaran') NOT NULL DEFAULT 'transfer'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE pembayaran_piutang MODIFY metode_pembayaran ENUM('tunai','transfer','cek','giro') NOT NULL DEFAULT 'transfer'");
    }
};
