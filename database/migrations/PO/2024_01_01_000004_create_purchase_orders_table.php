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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klien_id')->constrained('kliens')->onDelete('cascade');
            $table->string('no_po');
            $table->decimal('qty_total', 15, 2);
            $table->decimal('hpp_total', 15, 2);
            $table->decimal('total_amount', 15, 2);
            $table->string('spesifikasi');
            $table->text('catatan')->nullable();

            $table->enum('status', ['siap', 'proses','selesai', 'gagal'])->default('siap');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
