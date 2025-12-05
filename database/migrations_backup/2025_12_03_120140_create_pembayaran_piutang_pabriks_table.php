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
        Schema::create('pembayaran_piutang_pabriks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_penagihan_id')->constrained('invoice_penagihan')->onDelete('cascade');
            $table->string('no_pembayaran')->unique();
            $table->date('tanggal_bayar');
            $table->decimal('jumlah_bayar', 15, 2);
            $table->enum('metode_pembayaran', ['tunai', 'transfer', 'cek', 'giro']);
            $table->text('catatan')->nullable();
            $table->string('bukti_pembayaran')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_piutang_pabriks');
    }
};
