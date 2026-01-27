<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('riwayat_harga_bahan_baku', function (Blueprint $table) {
            $table->foreignId('klien_id')
                  ->nullable()
                  ->after('bahan_baku_supplier_id')
                  ->constrained('kliens')
                  ->onDelete('cascade');
            
            $table->index('klien_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_harga_bahan_baku', function (Blueprint $table) {
            $table->dropForeign(['klien_id']);
            $table->dropColumn('klien_id');
        });
    }
};
