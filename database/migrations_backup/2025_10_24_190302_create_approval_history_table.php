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
        Schema::create('approval_history', function (Blueprint $table) {
            $table->id();
            $table->enum('approval_type', ['pembayaran', 'penagihan']);
            $table->unsignedBigInteger('approval_id');
            $table->foreignId('pengiriman_id')->constrained('pengiriman')->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained('invoice_penagihan')->onDelete('cascade');
            $table->enum('role', ['staff', 'manager_keuangan', 'superadmin']);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('action')->default('approved');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Index untuk query cepat
            $table->index(['approval_type', 'approval_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_history');
    }
};
