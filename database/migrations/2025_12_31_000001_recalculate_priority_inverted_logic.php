<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migration to recalculate order priorities using inverted (corrected) logic.
 *
 * Previous (incorrect) logic: more time remaining => higher priority
 *   - > 60 days   -> 'tinggi' (high)
 *   - 31-60 days  -> 'sedang' (medium)
 *   - <= 30 days  -> 'rendah' (low)
 *
 * New (correct) logic: closer deadline => higher priority (urgency-based)
 *   - <= 30 days  -> 'tinggi' (high, urgent!)
 *   - 31-60 days  -> 'sedang' (medium)
 *   - > 60 days   -> 'rendah' (low, plenty of time)
 *
 * This migration recalculates all active orders based on the new logic.
 */
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Recalculate priorities based on PO end date with INVERTED logic.
        // Closer deadlines now get higher priority (urgency-based).
        //
        // New mapping:
        //   - 'tinggi' when days <= 30 (urgent, deadline soon!)
        //   - 'sedang' when days > 30 and <= 60
        //   - 'rendah' when days > 60 (plenty of time)
        DB::statement("
            UPDATE orders
            SET priority = CASE
                WHEN po_end_date IS NOT NULL AND DATEDIFF(po_end_date, CURDATE()) <= 30 THEN 'tinggi'
                WHEN po_end_date IS NOT NULL AND DATEDIFF(po_end_date, CURDATE()) <= 60 THEN 'sedang'
                ELSE 'rendah'
            END,
            priority_calculated_at = NOW()
            WHERE po_end_date IS NOT NULL
              AND status NOT IN ('selesai', 'dibatalkan')
        ");
    }

    /**
     * Reverse the migrations.
     *
     * This reverts to the old (incorrect) logic where more time = higher priority.
     * Should only be needed for rollback purposes.
     */
    public function down(): void
    {
        // Revert to OLD logic (more time remaining => higher priority)
        DB::statement("
            UPDATE orders
            SET priority = CASE
                WHEN po_end_date IS NOT NULL AND DATEDIFF(po_end_date, CURDATE()) > 60 THEN 'tinggi'
                WHEN po_end_date IS NOT NULL AND DATEDIFF(po_end_date, CURDATE()) > 30 THEN 'sedang'
                ELSE 'rendah'
            END,
            priority_calculated_at = NOW()
            WHERE po_end_date IS NOT NULL
              AND status NOT IN ('selesai', 'dibatalkan')
        ");
    }
};
