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
        Schema::table('bahan_baku_klien', function (Blueprint $table) {
            // Add client relationship
            $table->foreignId('klien_id')->nullable()->after('id')->constrained('kliens')->onDelete('cascade');
            
            // Add pricing fields
            $table->decimal('harga_approved', 15, 2)->nullable()->after('spesifikasi')->comment('Client approved price per unit');
            $table->timestamp('approved_at')->nullable()->after('harga_approved')->comment('When price was approved');
            $table->foreignId('approved_by_marketing')->nullable()->after('approved_at')->constrained('users')->onDelete('set null')->comment('Marketing user who approved price');
            
            // Add indexes for performance
            $table->index('klien_id', 'idx_bahan_baku_klien_klien');
            $table->index(['klien_id', 'status'], 'idx_klien_status');
            $table->index('approved_by_marketing', 'idx_approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bahan_baku_klien', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['klien_id']);
            $table->dropForeign(['approved_by_marketing']);
            
            // Drop indexes
            $table->dropIndex('idx_bahan_baku_klien_klien');
            $table->dropIndex('idx_klien_status');
            $table->dropIndex('idx_approved_by');
            
            // Drop columns
            $table->dropColumn([
                'klien_id',
                'harga_approved', 
                'approved_at',
                'approved_by_marketing'
            ]);
        });
    }
};