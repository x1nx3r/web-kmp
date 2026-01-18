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
        Schema::table('bahan_baku_supplier', function (Blueprint $table) {
            // 1. Drop foreign key constraint that uses this index
            $table->dropForeign(['supplier_id']);
        });
        
        // 2. Drop the unique constraint
        Schema::table('bahan_baku_supplier', function (Blueprint $table) {
            $table->dropUnique('supplier_bahan_baku_unique');
        });
        
        // 3. Recreate foreign key constraint
        Schema::table('bahan_baku_supplier', function (Blueprint $table) {
            $table->foreign('supplier_id')
                  ->references('id')
                  ->on('suppliers')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bahan_baku_supplier', function (Blueprint $table) {
            // Restore the unique constraint
            $table->unique(['supplier_id', 'nama'], 'supplier_bahan_baku_unique');
        });
    }
};
