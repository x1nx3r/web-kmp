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
        Schema::create('pengiriman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')->constrained('purchase_orders')->onDelete('cascade');
            $table->foreignId('purchasing_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('bahan_baku_supplier_id')->nullable();
            $table->date('tanggal_kirim')->nullable();
            $table->date('hari_kirim')->nullable();
            $table->decimal('qty_kirim', 15, 2)->nullable();
            $table->decimal('harga_jual', 15, 2)->nullable();
            $table->decimal('total_harga', 15, 2)->nullable();
            $table->decimal('qty_sisa', 15, 2)->nullable();
            $table->string('bukti_foto_bongkar')->nullable();
            $table->enum('status', ['pending', 'terkirim', 'diverifikasi'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengiriman');
    }
};
