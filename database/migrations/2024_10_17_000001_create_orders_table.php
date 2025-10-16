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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('no_order')->unique();
            $table->foreignId('klien_id')->constrained('kliens')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->date('tanggal_order');
            $table->text('catatan')->nullable();
            $table->enum('status', ['draft', 'dikonfirmasi', 'diproses', 'sebagian_dikirim', 'selesai', 'dibatalkan'])->default('draft');
            $table->enum('priority', ['rendah', 'normal', 'tinggi', 'mendesak'])->default('normal');
            
            // Summary totals (calculated from details)
            $table->decimal('total_amount', 15, 2)->default(0);
            
            // Quantity Summary
            $table->integer('total_items')->default(0);
            $table->decimal('total_qty', 10, 2)->default(0);
            
            // Workflow
            $table->timestamp('dikonfirmasi_at')->nullable();
            $table->timestamp('selesai_at')->nullable();
            $table->timestamp('dibatalkan_at')->nullable();
            $table->text('alasan_pembatalan')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['klien_id', 'status']);
            $table->index(['created_by', 'tanggal_order']);
            $table->index(['status', 'created_at']);
            $table->index('no_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};