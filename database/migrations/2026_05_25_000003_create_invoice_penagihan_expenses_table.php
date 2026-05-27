<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_penagihan_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_penagihan_id')
                ->constrained('invoice_penagihan')
                ->cascadeOnDelete();

            $table->string('type', 30); // truk|kuli|fee|other
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['invoice_penagihan_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_penagihan_expenses');
    }
};
