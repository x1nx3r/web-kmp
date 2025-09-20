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
        Schema::create('bahan_baku_supplier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->string('nama')->nullable();
            $table->decimal('harga_per_satuan', 15, 2);
            $table->string('satuan')->nullable();
            $table->decimal('stok', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            // Unique constraint untuk mencegah duplikasi nama bahan baku per supplier
            $table->unique(['supplier_id', 'nama'], 'supplier_bahan_baku_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bahan_baku_supplier');
    }
};
