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
        Schema::create('target_omset', function (Blueprint $table) {
            $table->id();
            $table->year('tahun');
            $table->decimal('target_tahunan', 20, 2);
            $table->decimal('target_bulanan', 20, 2);
            $table->decimal('target_mingguan', 20, 2);
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
            
            $table->unique('tahun');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target_omset');
    }
};
