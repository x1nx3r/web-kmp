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
        Schema::create('catatan_piutang_pabriks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klien_id')->constrained('kliens')->onDelete('cascade');
            $table->string('no_invoice')->unique();
            $table->date('tanggal_invoice');
            $table->date('tanggal_jatuh_tempo');
            $table->decimal('jumlah_piutang', 15, 2);
            $table->decimal('jumlah_dibayar', 15, 2)->default(0);
            $table->decimal('sisa_piutang', 15, 2);
            $table->enum('status', ['belum_jatuh_tempo', 'jatuh_tempo', 'terlambat', 'cicilan', 'lunas'])->default('belum_jatuh_tempo');
            $table->integer('hari_keterlambatan')->default(0);
            $table->text('keterangan')->nullable();
            $table->string('bukti_transaksi')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catatan_piutang_pabriks');
    }
};
