<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fix priority for selesai/dibatalkan orders that were excluded from the
 * previous migration (2026_03_09_000001_recalculate_priority_client_logic).
 *
 * Applies the same client-requested time-remaining logic:
 * - tinggi: remaining days > 60
 * - sedang: remaining days > 30 and <= 60
 * - rendah: remaining days <= 30
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
              AND status IN ('selesai', 'dibatalkan')
        ");
    }

    public function down(): void
    {
        // Data migration — no structural rollback needed.
    }
};
