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
        Schema::create('target_omset_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('target_omset_id')->constrained('target_omset')->onDelete('cascade');
            $table->year('tahun');
            $table->tinyInteger('bulan')->nullable(); // 1-12, nullable for yearly
            $table->tinyInteger('minggu')->nullable(); // 1-52
            $table->string('periode_type'); // 'weekly', 'monthly', 'yearly'
            
            // Target values
            $table->decimal('target_amount', 20, 2);
            
            // Actual values (snapshot)
            $table->decimal('actual_omset', 20, 2);
            $table->decimal('progress_percentage', 5, 2);
            $table->decimal('selisih', 20, 2);
            
            // Additional info
            $table->string('status'); // 'tercapai', 'on_track', 'perlu_boost', 'belum_ada_data'
            $table->timestamp('snapshot_at');
            $table->string('created_by')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['tahun', 'bulan']);
            $table->index(['tahun', 'minggu']);
            $table->index('periode_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target_omset_snapshots');
    }
};
