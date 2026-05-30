<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approval_pembayaran', function (Blueprint $table) {
            // 3 decimal places
            $table->decimal('piutang_amount', 15, 3)->default(0)->change();

            $table->decimal('refraksi_value', 15, 3)->default(0)->change();
            $table->decimal('refraksi_amount', 15, 3)->default(0)->change();

            $table->decimal('qty_before_refraksi', 15, 3)->nullable()->change();
            $table->decimal('qty_after_refraksi', 15, 3)->nullable()->change();

            $table->decimal('amount_before_refraksi', 15, 3)->nullable()->change();
            $table->decimal('amount_after_refraksi', 15, 3)->nullable()->change();

            $table->decimal('additional_expenses_total', 15, 3)->default(0)->change();
            $table->decimal('subtotal', 15, 3)->default(0)->change();
            $table->decimal('total_dibayarkan', 15, 3)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('approval_pembayaran', function (Blueprint $table) {
            // back to 2 decimal places
            $table->decimal('piutang_amount', 15, 2)->default(0)->change();

            $table->decimal('refraksi_value', 15, 2)->default(0)->change();
            $table->decimal('refraksi_amount', 15, 2)->default(0)->change();

            $table->decimal('qty_before_refraksi', 15, 2)->nullable()->change();
            $table->decimal('qty_after_refraksi', 15, 2)->nullable()->change();

            $table->decimal('amount_before_refraksi', 15, 2)->nullable()->change();
            $table->decimal('amount_after_refraksi', 15, 2)->nullable()->change();

            $table->decimal('additional_expenses_total', 15, 2)->default(0)->change();
            $table->decimal('subtotal', 15, 2)->default(0)->change();
            $table->decimal('total_dibayarkan', 15, 2)->default(0)->change();
        });
    }
};
