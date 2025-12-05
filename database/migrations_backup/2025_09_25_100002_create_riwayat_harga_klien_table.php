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
        Schema::create('riwayat_harga_klien', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_klien_id')->constrained('bahan_baku_klien')->onDelete('cascade');
            $table->decimal('harga_lama', 15, 2)->nullable()->comment('Previous approved price, null for first record');
            $table->decimal('harga_approved_baru', 15, 2)->comment('New approved price');
            $table->decimal('selisih_harga', 15, 2)->default(0)->comment('Price difference (new - old)');
            $table->decimal('persentase_perubahan', 8, 4)->default(0)->comment('Percentage change');
            $table->enum('tipe_perubahan', ['naik', 'turun', 'tetap', 'awal'])->default('awal');
            $table->text('keterangan')->nullable()->comment('Notes about price change');
            $table->timestamp('tanggal_perubahan')->useCurrent()->comment('When price was changed');
            $table->foreignId('updated_by_marketing')->constrained('users')->onDelete('restrict')->comment('Marketing user who updated');
            $table->timestamps();

            // Indexes for performance
            $table->index(['bahan_baku_klien_id', 'tanggal_perubahan'], 'riwayat_harga_klien_material_tanggal_idx');
            $table->index('tanggal_perubahan', 'riwayat_harga_klien_tanggal_idx');
            $table->index('tipe_perubahan', 'riwayat_harga_klien_tipe_idx');
            $table->index('updated_by_marketing', 'riwayat_harga_klien_marketing_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_harga_klien');
    }
};