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
        Schema::create('pengiriman_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengiriman_id')->constrained('pengiriman')->onDelete('cascade');
            $table->foreignId('purchase_order_bahan_baku_id')->constrained('purchase_order_bahan_baku')->onDelete('cascade');
            $table->foreignId('bahan_baku_supplier_id')->constrained('bahan_baku_supplier')->onDelete('cascade');
            $table->decimal('qty_kirim', 15, 2);
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('total_harga', 15, 2);
            $table->decimal('qty_sisa', 15, 2)->default(0);
            $table->enum('kondisi_barang', ['baik', 'rusak_sebagian', 'rusak_total'])->default('baik');
            $table->text('catatan_detail')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Index untuk performance
            $table->index(['pengiriman_id', 'bahan_baku_supplier_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengiriman_details');
    }
};
