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
        Schema::table('approval_pembayaran', function (Blueprint $table) {
            $table->foreignId('catatan_piutang_id')->nullable()->after('status')->constrained('catatan_piutangs')->onDelete('set null');
            $table->decimal('piutang_amount', 15, 2)->default(0)->after('catatan_piutang_id');
            $table->text('piutang_notes')->nullable()->after('piutang_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_pembayaran', function (Blueprint $table) {
            $table->dropForeign(['catatan_piutang_id']);
            $table->dropColumn(['catatan_piutang_id', 'piutang_amount', 'piutang_notes']);
        });
    }
};
