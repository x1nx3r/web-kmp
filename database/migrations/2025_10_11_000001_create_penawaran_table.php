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
        Schema::create('penawaran', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_penawaran', 50)->unique()->comment('Format: PNW-YYYY-XXXX');
            $table->foreignId('klien_id')->constrained('kliens')->cascadeOnDelete();
            $table->date('tanggal_penawaran');
            $table->date('tanggal_berlaku_sampai');
            
            // Status workflow
            $table->enum('status', [
                'draft',
                'menunggu_verifikasi',
                'disetujui',
                'ditolak',
                'expired'
            ])->default('draft');
            
            // Financial totals
            $table->decimal('total_revenue', 15, 2)->default(0)->comment('Total client price');
            $table->decimal('total_cost', 15, 2)->default(0)->comment('Total supplier cost');
            $table->decimal('total_profit', 15, 2)->default(0)->comment('Total profit');
            $table->decimal('margin_percentage', 5, 2)->default(0)->comment('Overall margin %');
            
            // User tracking
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            
            // Notes
            $table->text('catatan')->nullable()->comment('General notes');
            $table->text('alasan_penolakan')->nullable()->comment('Rejection reason if status=ditolak');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('nomor_penawaran');
            $table->index('klien_id');
            $table->index('status');
            $table->index('tanggal_penawaran');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penawaran');
    }
};
