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
        // Add foreign key constraints after bahan_baku_supplier table is created
        Schema::table('forecasts', function (Blueprint $table) {
            $table->foreign('bahan_baku_supplier_id')
                  ->references('id')
                  ->on('bahan_baku_supplier')
                  ->onDelete('set null');
        });

        Schema::table('pengiriman', function (Blueprint $table) {
            $table->foreign('bahan_baku_supplier_id')
                  ->references('id')
                  ->on('bahan_baku_supplier')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forecasts', function (Blueprint $table) {
            $table->dropForeign(['bahan_baku_supplier_id']);
        });

        Schema::table('pengiriman', function (Blueprint $table) {
            $table->dropForeign(['bahan_baku_supplier_id']);
        });
    }
};
