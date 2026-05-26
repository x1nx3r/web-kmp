<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approval_pembayaran', function (Blueprint $table) {
            if (!Schema::hasColumn('approval_pembayaran', 'additional_expenses_total')) {
                $table->decimal('additional_expenses_total', 15, 2)->default(0)->after('amount_after_refraksi');
            }
            if (!Schema::hasColumn('approval_pembayaran', 'subtotal')) {
                $table->decimal('subtotal', 15, 2)->default(0)->after('additional_expenses_total');
            }
            if (!Schema::hasColumn('approval_pembayaran', 'total_dibayarkan')) {
                $table->decimal('total_dibayarkan', 15, 2)->default(0)->after('subtotal');
            }
        });

        Schema::table('invoice_penagihan', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_penagihan', 'additional_expenses_total')) {
                $table->decimal('additional_expenses_total', 15, 2)->default(0)->after('subtotal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('approval_pembayaran', function (Blueprint $table) {
            if (Schema::hasColumn('approval_pembayaran', 'total_dibayarkan')) {
                $table->dropColumn('total_dibayarkan');
            }
            if (Schema::hasColumn('approval_pembayaran', 'subtotal')) {
                $table->dropColumn('subtotal');
            }
            if (Schema::hasColumn('approval_pembayaran', 'additional_expenses_total')) {
                $table->dropColumn('additional_expenses_total');
            }
        });

        Schema::table('invoice_penagihan', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_penagihan', 'additional_expenses_total')) {
                $table->dropColumn('additional_expenses_total');
            }
        });
    }
};
