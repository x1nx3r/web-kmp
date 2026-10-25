<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approval_pembayaran', function (Blueprint $table) {
            if (!Schema::hasColumn('approval_pembayaran', 'tax_percentage')) {
                $table->decimal('tax_percentage', 5, 3)->default(0)->after('additional_expenses_total');
            }
            if (!Schema::hasColumn('approval_pembayaran', 'tax_amount')) {
                $table->decimal('tax_amount', 15, 3)->default(0)->after('tax_percentage');
            }
        });
    }

    public function down(): void
    {
        Schema::table('approval_pembayaran', function (Blueprint $table) {
            if (Schema::hasColumn('approval_pembayaran', 'tax_amount')) {
                $table->dropColumn('tax_amount');
            }
            if (Schema::hasColumn('approval_pembayaran', 'tax_percentage')) {
                $table->dropColumn('tax_percentage');
            }
        });
    }
};
