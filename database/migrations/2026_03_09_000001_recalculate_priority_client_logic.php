<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Recalculate all order priorities to match client-requested logic:
 * - tinggi: remaining days > 60 (plenty of time, high-value PO)
 * - sedang: remaining days > 30 and <= 60
 * - rendah: remaining days <= 30 (deadline soon, low remaining value)
 *
 * This reverses the previous urgency-based mapping back to the time-remaining logic
 * that the client originally intended.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE orders
            SET priority = CASE
                WHEN DATEDIFF(po_end_date, NOW()) > 60 THEN 'tinggi'
                WHEN DATEDIFF(po_end_date, NOW()) > 30 THEN 'sedang'
                ELSE 'rendah'
            END,
            priority_calculated_at = NOW()
            WHERE po_end_date IS NOT NULL
              AND deleted_at IS NULL
              AND status NOT IN ('selesai', 'dibatalkan')
        ");
    }

    public function down(): void
    {
        // Revert to urgency-based logic (closer deadline => higher priority)
        DB::statement("
            UPDATE orders
            SET priority = CASE
                WHEN DATEDIFF(po_end_date, NOW()) <= 30 THEN 'tinggi'
                WHEN DATEDIFF(po_end_date, NOW()) <= 60 THEN 'sedang'
                ELSE 'rendah'
            END,
            priority_calculated_at = NOW()
            WHERE po_end_date IS NOT NULL
              AND deleted_at IS NULL
              AND status NOT IN ('selesai', 'dibatalkan')
        ");
    }
};
