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
        Schema::create('omset_manual', function (Blueprint $table) {
            $table->id();
            $table->year('tahun')->comment('Tahun omset manual');
            $table->unsignedTinyInteger('bulan')->comment('Bulan 1-12');
            $table->decimal('omset_manual', 20, 2)->default(0)->comment('Omset manual bulanan');
            $table->text('catatan')->nullable()->comment('Catatan tambahan');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            // Unique constraint untuk tahun dan bulan
            $table->unique(['tahun', 'bulan']);
            
            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('omset_manual');
    }
};
