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
        // 1. pengiriman table optimization (heavy query targets)
        Schema::table('pengiriman', function (Blueprint $table) {
            $table->index(['status', 'tanggal_kirim'], 'pengiriman_status_tanggal_kirim_idx');
            $table->index(['purchasing_id', 'status'], 'pengiriman_purchasing_id_status_idx');
            $table->index(['status', 'updated_at'], 'pengiriman_status_updated_at_idx');
        });

        // 2. forecasts table optimization
        Schema::table('forecasts', function (Blueprint $table) {
            $table->index(['status', 'tanggal_forecast'], 'forecasts_status_tanggal_forecast_idx');
            $table->index(['purchasing_id', 'status'], 'forecasts_purchasing_id_status_idx');
        });

        // 3. invoice_penagihan table optimization
        Schema::table('invoice_penagihan', function (Blueprint $table) {
            $table->index(['payment_status', 'due_date'], 'invoice_penagihan_payment_status_due_date_idx');
            $table->index('status', 'invoice_penagihan_status_idx');
        });

        // 4. approval_pembayaran table optimization
        Schema::table('approval_pembayaran', function (Blueprint $table) {
            $table->index('status', 'approval_pembayaran_status_idx');
        });

        // 5. approval_penagihan table optimization
        Schema::table('approval_penagihan', function (Blueprint $table) {
            $table->index('status', 'approval_penagihan_status_idx');
        });

        // 6. catatan_piutang_pabriks table optimization
        Schema::table('catatan_piutang_pabriks', function (Blueprint $table) {
            $table->index(['klien_id', 'status'], 'catatan_piutang_pabriks_klien_id_status_idx');
            $table->index('tanggal_jatuh_tempo', 'catatan_piutang_pabriks_tanggal_jatuh_tempo_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengiriman', function (Blueprint $table) {
            $table->dropIndex('pengiriman_status_tanggal_kirim_idx');
            $table->dropIndex('pengiriman_purchasing_id_status_idx');
            $table->dropIndex('pengiriman_status_updated_at_idx');
        });

        Schema::table('forecasts', function (Blueprint $table) {
            $table->dropIndex('forecasts_status_tanggal_forecast_idx');
            $table->dropIndex('forecasts_purchasing_id_status_idx');
        });

        Schema::table('invoice_penagihan', function (Blueprint $table) {
            $table->dropIndex('invoice_penagihan_payment_status_due_date_idx');
            $table->dropIndex('invoice_penagihan_status_idx');
        });

        Schema::table('approval_pembayaran', function (Blueprint $table) {
            $table->dropIndex('approval_pembayaran_status_idx');
        });

        Schema::table('approval_penagihan', function (Blueprint $table) {
            $table->dropIndex('approval_penagihan_status_idx');
        });

        Schema::table('catatan_piutang_pabriks', function (Blueprint $table) {
            $table->dropIndex('catatan_piutang_pabriks_klien_id_status_idx');
            $table->dropIndex('catatan_piutang_pabriks_tanggal_jatuh_tempo_idx');
        });
    }
};
