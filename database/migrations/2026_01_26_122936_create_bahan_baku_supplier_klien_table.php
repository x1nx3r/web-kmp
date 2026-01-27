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
        Schema::create('bahan_baku_supplier_klien', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_supplier_id')
                  ->constrained('bahan_baku_supplier')
                  ->onDelete('cascade');
            $table->foreignId('klien_id')
                  ->constrained('kliens')
                  ->onDelete('cascade');
            $table->decimal('harga_per_satuan', 15, 2);
            $table->timestamps();
            
            // Unique constraint: satu supplier-klien hanya punya satu harga aktif
            $table->unique(['bahan_baku_supplier_id', 'klien_id'], 'unique_supplier_klien');
            
            // Index untuk performa query
            $table->index('bahan_baku_supplier_id');
            $table->index('klien_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bahan_baku_supplier_klien');
    }
};
