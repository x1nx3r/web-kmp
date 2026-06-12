<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_penagihan', function (Blueprint $table) {
            $table->dropForeign(['pengiriman_id']);
        });

        try {
            Schema::table('invoice_penagihan', function (Blueprint $table) {
                $table->dropUnique(['pengiriman_id']);
            });
        } catch (Throwable $e) {
            // Index may not exist, skip
        }

        DB::statement('ALTER TABLE invoice_penagihan MODIFY COLUMN pengiriman_id BIGINT(20) UNSIGNED NULL');

        Schema::table('invoice_penagihan', function (Blueprint $table) {
            $table->foreign('pengiriman_id')->references('id')->on('pengiriman')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_penagihan', function (Blueprint $table) {
            $table->dropForeign(['pengiriman_id']);
        });

        DB::statement('ALTER TABLE invoice_penagihan MODIFY COLUMN pengiriman_id BIGINT(20) UNSIGNED NOT NULL');

        Schema::table('invoice_penagihan', function (Blueprint $table) {
            $table->unique('pengiriman_id');
            $table->foreign('pengiriman_id')->references('id')->on('pengiriman')->cascadeOnDelete();
        });
    }
};
