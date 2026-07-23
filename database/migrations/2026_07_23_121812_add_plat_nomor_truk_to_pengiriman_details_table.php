<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengiriman_details', function (Blueprint $table) {
            $table->string('plat_nomor_truk', 20)->nullable()->after('catatan_detail');
        });
    }

    public function down(): void
    {
        Schema::table('pengiriman_details', function (Blueprint $table) {
            $table->dropColumn('plat_nomor_truk');
        });
    }
};