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
            $table->foreignId('bahan_baku_klien_id')->constrained('bahan_baku_klien')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->decimal('harga_per_satuan', 15, 2);
            $table->integer('spesifikasi')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Unique constraint untuk mencegah duplikasi
            $table->unique(['bahan_baku_klien_id', 'supplier_id'], 'bahan_baku_supplier_unique');
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
