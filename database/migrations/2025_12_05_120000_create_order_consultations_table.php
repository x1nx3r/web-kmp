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
        Schema::create('order_consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('requested_by')->constrained('users');
            $table->text('requested_note')->nullable();
            $table->timestamp('requested_at');
            $table->foreignId('responded_by')->nullable()->constrained('users');
            $table->enum('response_type', ['selesai', 'lanjutkan'])->nullable();
            $table->text('response_note')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            // Index for faster queries
            $table->index(['order_id', 'responded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_consultations');
    }
};
