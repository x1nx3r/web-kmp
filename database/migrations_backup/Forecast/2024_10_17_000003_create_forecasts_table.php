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
        Schema::create('forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('orders')->onDelete('cascade'); // References orders table
            $table->foreignId('purchasing_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('no_forecast')->unique();
            $table->date('tanggal_forecast');
            $table->string('hari_kirim_forecast');
            $table->decimal('total_qty_forecast', 15, 2)->default(0);
            $table->decimal('total_harga_forecast', 15, 2)->default(0);
            $table->enum('status',['pending','sukses','gagal'])->default('pending');
            $table->text('catatan')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forecasts');
    }
};
