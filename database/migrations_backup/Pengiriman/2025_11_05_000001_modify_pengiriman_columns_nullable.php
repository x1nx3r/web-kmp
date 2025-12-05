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
        Schema::table('pengiriman', function (Blueprint $table) {
            // Ubah kolom total_qty_kirim dan total_harga_kirim menjadi nullable
            $table->decimal('total_qty_kirim', 15, 2)->nullable()->default(0)->change();
            $table->decimal('total_harga_kirim', 15, 2)->nullable()->default(0)->change();
        });
        
        Schema::table('pengiriman_details', function (Blueprint $table) {
            // Ubah kolom qty_kirim, harga_satuan, dan total_harga menjadi nullable
            $table->decimal('qty_kirim', 15, 2)->nullable()->change();
            $table->decimal('harga_satuan', 15, 2)->nullable()->change();
            $table->decimal('total_harga', 15, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengiriman', function (Blueprint $table) {
            // Kembalikan ke non-nullable
            $table->decimal('total_qty_kirim', 15, 2)->nullable(false)->default(0)->change();
            $table->decimal('total_harga_kirim', 15, 2)->nullable(false)->default(0)->change();
        });
        
        Schema::table('pengiriman_details', function (Blueprint $table) {
            // Kembalikan ke non-nullable
            $table->decimal('qty_kirim', 15, 2)->nullable(false)->change();
            $table->decimal('harga_satuan', 15, 2)->nullable(false)->change();
            $table->decimal('total_harga', 15, 2)->nullable(false)->change();
        });
    }
};
