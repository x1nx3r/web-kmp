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
        Schema::create('forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')->constrained('purchase_orders')->onDelete('cascade');
            $table->foreignId('purchasing_id')->nullable()->constrained('users')->onDelete('set null');
            $table->unsignedBigInteger('bahan_baku_supplier_id')->nullable();
            $table->string('tanggal_kirim_forecast');
            $table->string('hari_kirim_forecast');
            $table->decimal('qty_forecast', 15, 2);
            $table->decimal('harga_jual_forecast', 15, 2);
            $table->decimal('total_forecast', 15, 2);
            $table->enum('status',['pending','sukses','gagal'])->default('pending');
            $table->string('catatan')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forecasts');
    }
};
