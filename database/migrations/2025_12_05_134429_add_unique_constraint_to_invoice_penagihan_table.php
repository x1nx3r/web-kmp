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
        Schema::table('invoice_penagihan', function (Blueprint $table) {
            // Add unique constraint to invoice_number to prevent duplicates
            $table->unique('invoice_number', 'invoice_penagihan_invoice_number_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_penagihan', function (Blueprint $table) {
            $table->dropUnique('invoice_penagihan_invoice_number_unique');
        });
    }
};
