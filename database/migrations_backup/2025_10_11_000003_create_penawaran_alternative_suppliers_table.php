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
        Schema::create('penawaran_alternative_suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penawaran_detail_id')->constrained('penawaran_detail')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('bahan_baku_supplier_id')->constrained('bahan_baku_supplier')->cascadeOnDelete();
            $table->decimal('harga_supplier', 15, 2)->comment('Alternative supplier price at time of quotation');
            $table->text('notes')->nullable()->comment('Why this alternative was not chosen');
            $table->timestamps();
            
            // Indexes
            $table->index('penawaran_detail_id');
            $table->index('supplier_id');
            
            // Unique constraint: one alternative supplier entry per detail+supplier combination
            $table->unique(['penawaran_detail_id', 'supplier_id'], 'unique_detail_supplier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penawaran_alternative_suppliers');
    }
};
