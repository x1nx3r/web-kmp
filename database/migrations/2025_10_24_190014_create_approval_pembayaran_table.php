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
        Schema::create('approval_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengiriman_id')->unique()->constrained('pengiriman')->onDelete('cascade');
            $table->foreignId('staff_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('staff_approved_at')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('manager_approved_at')->nullable();
            $table->foreignId('superadmin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('superadmin_approved_at')->nullable();
            $table->enum('status', ['pending', 'staff_approved', 'manager_approved', 'completed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_pembayaran');
    }
};
