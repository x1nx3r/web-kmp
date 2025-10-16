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
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('bahan_baku_klien_id')->constrained('bahan_baku_kliens')->onDelete('cascade');
            
            // Primary supplier (main supplier for this order item)
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('restrict');
            
            // Quantity and pricing
            $table->decimal('qty', 10, 2);
            $table->string('satuan', 20);
            
            // Historical price caching from supplier
            $table->decimal('harga_supplier', 12, 2);
            $table->decimal('total_hpp', 15, 2); // qty * harga_supplier
            
            // Client pricing
            $table->decimal('harga_jual', 12, 2);
            $table->decimal('total_harga', 15, 2); // qty * harga_jual
            
            // Margin analysis
            $table->decimal('margin_per_unit', 12, 2); // harga_jual - harga_supplier
            $table->decimal('total_margin', 15, 2); // qty * margin_per_unit
            $table->decimal('margin_percentage', 5, 2); // (margin_per_unit / harga_jual) * 100
            
            // Delivery tracking (for middleman business)
            $table->decimal('qty_shipped', 10, 2)->default(0);
            $table->enum('status', ['menunggu', 'diproses', 'sebagian_dikirim', 'selesai'])->default('menunggu');
            
            // Material specifications
            $table->text('spesifikasi_khusus')->nullable();
            $table->text('catatan')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['order_id', 'status']);
            $table->index(['bahan_baku_klien_id', 'supplier_id']);
            $table->index(['supplier_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};