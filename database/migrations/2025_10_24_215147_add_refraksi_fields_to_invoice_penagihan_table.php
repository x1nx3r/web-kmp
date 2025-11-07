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
            $table->enum('refraksi_type', ['qty', 'rupiah'])->nullable()->after('items');
            $table->decimal('refraksi_value', 15, 2)->default(0)->after('refraksi_type');
            $table->decimal('refraksi_amount', 15, 2)->default(0)->after('refraksi_value'); // Total potongan dalam rupiah
            $table->decimal('qty_before_refraksi', 15, 2)->nullable()->after('refraksi_amount'); // Qty sebelum refraksi
            $table->decimal('qty_after_refraksi', 15, 2)->nullable()->after('qty_before_refraksi'); // Qty setelah refraksi
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_penagihan', function (Blueprint $table) {
            $table->dropColumn([
                'refraksi_type',
                'refraksi_value',
                'refraksi_amount',
                'qty_before_refraksi',
                'qty_after_refraksi'
            ]);
        });
    }
};
