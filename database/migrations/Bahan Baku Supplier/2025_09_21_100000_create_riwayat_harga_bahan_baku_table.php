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
        Schema::create('riwayat_harga_bahan_baku', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_supplier_id')->constrained('bahan_baku_supplier')->onDelete('cascade');
            $table->decimal('harga_lama', 15, 2)->nullable()->comment('Harga sebelum update, null jika data pertama');
            $table->decimal('harga_baru', 15, 2)->comment('Harga setelah update');
            $table->decimal('selisih_harga', 15, 2)->default(0)->comment('Selisih harga (harga_baru - harga_lama)');
            $table->decimal('persentase_perubahan', 8, 4)->default(0)->comment('Persentase perubahan harga');
            $table->enum('tipe_perubahan', ['naik', 'turun', 'tetap', 'awal'])->default('awal');
            $table->text('keterangan')->nullable()->comment('Keterangan tambahan untuk perubahan harga');
            $table->timestamp('tanggal_perubahan')->useCurrent()->comment('Waktu perubahan harga');
            $table->string('updated_by')->nullable()->comment('User yang melakukan update');
            $table->timestamps();

            // Indexes
            $table->index(['bahan_baku_supplier_id', 'tanggal_perubahan'], 'riwayat_harga_supplier_tanggal_idx');
            $table->index('tanggal_perubahan', 'riwayat_harga_tanggal_idx');
            $table->index('tipe_perubahan', 'riwayat_harga_tipe_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_harga_bahan_baku');
    }
};
