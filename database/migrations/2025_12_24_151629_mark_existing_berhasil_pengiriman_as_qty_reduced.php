<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Mark existing 'berhasil' pengiriman as qty_reduced = true
     * because their qty was already reduced using old logic
     */
    public function up(): void
    {
        // ONLY update pengiriman with status 'berhasil'
        // These already have their qty reduced using old logic
        $updated = DB::table('pengiriman')
            ->where('status', 'berhasil')
            ->update([
                'qty_reduced' => true
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset qty_reduced flags for 'berhasil' status only
        DB::table('pengiriman')
            ->where('status', 'berhasil')
            ->update([
                'qty_reduced' => false
            ]);
    }
};
