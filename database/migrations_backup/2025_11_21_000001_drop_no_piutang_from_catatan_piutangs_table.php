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
        if (Schema::hasColumn('catatan_piutangs', 'no_piutang')) {
            Schema::table('catatan_piutangs', function (Blueprint $table) {
                $table->dropColumn('no_piutang');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catatan_piutangs', function (Blueprint $table) {
            if (! Schema::hasColumn('catatan_piutangs', 'no_piutang')) {
                $table->string('no_piutang')->nullable()->unique()->after('supplier_id');
            }
        });
    }
};
