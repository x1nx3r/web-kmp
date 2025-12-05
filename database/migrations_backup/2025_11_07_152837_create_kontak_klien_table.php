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
        Schema::create('kontak_klien', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // Contact name
            $table->string('klien_nama'); // Client company name (not plant-specific)
            $table->string('nomor_hp')->nullable(); // Phone number
            $table->string('jabatan')->nullable(); // Position/Title
            $table->text('catatan')->nullable(); // Notes
            $table->timestamps();
            
            // Index for better performance when searching by client
            $table->index('klien_nama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kontak_klien');
    }
};
