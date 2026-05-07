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
        // Update pengiriman_details table
        Schema::table('pengiriman_details', function (Blueprint $table) {
            $table->decimal('qty_kirim', 15, 3)->nullable()->change();
            $table->decimal('harga_satuan', 15, 3)->nullable()->change();
            $table->decimal('total_harga', 15, 3)->nullable()->change();
        });

        // Update pengiriman table
        Schema::table('pengiriman', function (Blueprint $table) {
            $table->decimal('total_qty_kirim', 15, 3)->nullable()->default(0)->change();
            $table->decimal('total_harga_kirim', 15, 3)->nullable()->default(0)->change();
        });

        // Update invoice_penagihan table
        Schema::table('invoice_penagihan', function (Blueprint $table) {
            $table->decimal('refraksi_value', 15, 3)->default(0)->change();
            $table->decimal('refraksi_amount', 15, 3)->default(0)->change();
            $table->decimal('qty_before_refraksi', 15, 3)->nullable()->change();
            $table->decimal('qty_after_refraksi', 15, 3)->nullable()->change();
            $table->decimal('amount_before_refraksi', 15, 3)->nullable()->change();
            $table->decimal('amount_after_refraksi', 15, 3)->nullable()->change();
            $table->decimal('subtotal', 15, 3)->default(0)->change();
            $table->decimal('tax_percentage', 5, 3)->default(11.000)->change();
            $table->decimal('tax_amount', 15, 3)->default(0)->change();
            $table->decimal('discount_amount', 15, 3)->default(0)->change();
            $table->decimal('total_amount', 15, 3)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert pengiriman_details table
        Schema::table('pengiriman_details', function (Blueprint $table) {
            $table->decimal('qty_kirim', 15, 2)->nullable()->change();
            $table->decimal('harga_satuan', 15, 2)->nullable()->change();
            $table->decimal('total_harga', 15, 2)->nullable()->change();
        });

        // Revert pengiriman table
        Schema::table('pengiriman', function (Blueprint $table) {
            $table->decimal('total_qty_kirim', 15, 2)->nullable()->default(0)->change();
            $table->decimal('total_harga_kirim', 15, 2)->nullable()->default(0)->change();
        });

        // Revert invoice_penagihan table
        Schema::table('invoice_penagihan', function (Blueprint $table) {
            $table->decimal('refraksi_value', 15, 2)->default(0)->change();
            $table->decimal('refraksi_amount', 15, 2)->default(0)->change();
            $table->decimal('qty_before_refraksi', 15, 2)->nullable()->change();
            $table->decimal('qty_after_refraksi', 15, 2)->nullable()->change();
            $table->decimal('amount_before_refraksi', 15, 2)->nullable()->change();
            $table->decimal('amount_after_refraksi', 15, 2)->nullable()->change();
            $table->decimal('subtotal', 15, 2)->default(0)->change();
            $table->decimal('tax_percentage', 5, 2)->default(11.00)->change();
            $table->decimal('tax_amount', 15, 2)->default(0)->change();
            $table->decimal('discount_amount', 15, 2)->default(0)->change();
            $table->decimal('total_amount', 15, 2)->default(0)->change();
        });
    }
};