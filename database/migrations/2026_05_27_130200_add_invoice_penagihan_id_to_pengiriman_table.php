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
        Schema::table('pengiriman', function (Blueprint $table) {
            if (!Schema::hasColumn('pengiriman', 'invoice_penagihan_id')) {
                $table->foreignId('invoice_penagihan_id')
                    ->nullable()
                    ->constrained('invoice_penagihan')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengiriman', function (Blueprint $table) {
            if (Schema::hasColumn('pengiriman', 'invoice_penagihan_id')) {
                $table->dropForeign(['invoice_penagihan_id']);
                $table->dropColumn('invoice_penagihan_id');
            }
        });
    }
};
