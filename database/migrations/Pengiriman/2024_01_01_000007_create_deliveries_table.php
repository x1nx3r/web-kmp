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
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('cascade');
            $table->foreignId('purchasing_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('forecast_id')->constrained('forecasts')->onDelete('cascade');
            $table->string('no_pengiriman')->nullable();
            $table->date('tanggal_kirim')->nullable();
            $table->string('hari_kirim')->nullable();
            $table->decimal('total_qty_kirim', 15, 2)->default(0);
            $table->decimal('total_harga_kirim', 15, 2)->default(0);
            $table->string('bukti_foto_bongkar')->nullable();
            $table->enum('status', ['pending','menunggu_verifikasi', 'berhasil', 'gagal'])->default('pending');
            $table->text('catatan')->nullable();
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
