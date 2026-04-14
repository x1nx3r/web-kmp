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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('original_total_amount', 20, 2)->nullable()
                ->after('total_amount')
                ->comment('Nilai kontrak asli (tidak mengecil saat kirim)');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->decimal('original_qty', 15, 2)->nullable()
                ->after('qty')
                ->comment('Kuantitas kontrak asli (tidak mengecil saat kirim)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('original_total_amount');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn('original_qty');
        });
    }
};
