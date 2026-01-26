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
        Schema::create('target_omset_procurement', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // ID procurement (manager_purchasing atau staff_purchasing)
            $table->integer('tahun'); // Tahun target
            $table->decimal('persentase_target', 5, 2); // Persentase dari total target (0-100)
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            // Unique constraint: satu user hanya bisa punya satu target per tahun
            $table->unique(['user_id', 'tahun']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target_omset_procurement');
    }
};
