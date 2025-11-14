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
            $table->decimal('amount_before_refraksi', 15, 2)->nullable()->after('qty_after_refraksi');
            $table->decimal('amount_after_refraksi', 15, 2)->nullable()->after('amount_before_refraksi');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_penagihan', function (Blueprint $table) {
            $table->dropColumn(['amount_before_refraksi', 'amount_after_refraksi', 'status']);
        });
    }
};
