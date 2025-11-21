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
        Schema::create('supplier_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengiriman_id')->constrained('pengiriman')->onDelete('cascade');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('total_score', 5, 2)->nullable()->comment('Total skor rata-rata (1-5)');
            $table->integer('rating')->nullable()->comment('Rating bintang 1-5');
            $table->text('ulasan')->nullable()->comment('Kesimpulan ulasan');
            $table->text('catatan_tambahan')->nullable();
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamps();
            
            $table->index(['pengiriman_id', 'supplier_id']);
        });

        Schema::create('supplier_evaluation_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_evaluation_id')->constrained('supplier_evaluations')->onDelete('cascade');
            $table->string('kriteria')->comment('Harga, Kualitas, Kuantitas, dst');
            $table->string('sub_kriteria');
            $table->integer('penilaian')->comment('1-5');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            
            $table->index('supplier_evaluation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_evaluation_details');
        Schema::dropIfExists('supplier_evaluations');
    }
};
