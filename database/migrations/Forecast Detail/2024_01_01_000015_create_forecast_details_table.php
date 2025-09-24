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
        Schema::create('forecast_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forecast_id')->constrained('forecasts')->onDelete('cascade');
            $table->foreignId('purchase_order_bahan_baku_id')->constrained('purchase_order_bahan_baku')->onDelete('cascade');
            $table->foreignId('bahan_baku_supplier_id')->constrained('bahan_baku_supplier')->onDelete('cascade');
            $table->decimal('qty_forecast', 15, 2);
            $table->decimal('harga_satuan_forecast', 15, 2);
            $table->decimal('total_harga_forecast', 15, 2);
            $table->decimal('harga_satuan_po', 15, 2)->nullable();
            $table->decimal('total_harga_po', 15, 2)->nullable();
            $table->text('catatan_detail')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Index untuk performance
            $table->index(['forecast_id', 'bahan_baku_supplier_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forecast_details');
    }
};
