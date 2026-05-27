<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_pembayaran_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_pembayaran_id')
                ->constrained('approval_pembayaran')
                ->cascadeOnDelete();

            $table->string('type', 30); // truk|kuli|fee|other
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['approval_pembayaran_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_pembayaran_expenses');
    }
};
