<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Surgical migration (blocking) to migrate the legacy priority enum values to the new set.
 *
 * This migration performs the following steps (blocking ALTERs on the `orders` table):
 *  1) Add the new token 'sedang' to the existing enum so we can safely write it.
 *  2) Remap rows:
 *       - 'mendesak' -> 'tinggi'
 *       - 'normal'  -> 'sedang'
 *  3) Recreate the enum to only contain ['rendah','sedang','tinggi'] and set default to 'sedang'.
 *
 * WARNING
 * - ALTERing enum columns on MySQL can be a blocking operation that rebuilds the table.
 *   Run this in a maintenance window or use an online schema-change tool if you cannot accept downtime.
 * - Ensure application code is deployed to accept the new values (e.g. validation allowing 'sedang')
 *   prior to running this migration to avoid validation or insert errors.
 */
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) Add 'sedang' to the enum values so we can write it safely.
        //    This is a blocking DDL on MySQL (table rebuild) — expect downtime for large tables.
        DB::statement("
            ALTER TABLE orders
            MODIFY COLUMN priority ENUM('rendah','normal','tinggi','mendesak','sedang')
            DEFAULT 'normal'
        ");

        // 2) Update existing rows to the new mapping.
        //    - map 'mendesak' -> 'tinggi'
        //    - map 'normal'  -> 'sedang'
        DB::table("orders")
            ->where("priority", "mendesak")
            ->update(["priority" => "tinggi"]);
        DB::table("orders")
            ->where("priority", "normal")
            ->update(["priority" => "sedang"]);

        // 3) Finalize the enum to only allow the new set: ['rendah','sedang','tinggi'].
        //    Set default to 'sedang' (choose default appropriate for your system).
        DB::statement("
            ALTER TABLE orders
            MODIFY COLUMN priority ENUM('rendah','sedang','tinggi')
            NOT NULL
            DEFAULT 'sedang'
        ");

        // 4) Recalculate priorities based on PO end date timeframe and update timestamp.
        //    This ensures stored priorities match the literal mapping (timeframe) instead
        //    of only relying on token remapping from legacy values.
        //
        //    Literal mapping (days remaining = DATEDIFF(po_end_date, CURDATE())):
        //      - 'tinggi' when days > 60
        //      - 'sedang' when days > 30 and <= 60
        //      - 'rendah' when days <= 30
        DB::statement("
            UPDATE orders
            SET priority = CASE
                WHEN po_end_date IS NOT NULL AND DATEDIFF(po_end_date, CURDATE()) > 60 THEN 'tinggi'
                WHEN po_end_date IS NOT NULL AND DATEDIFF(po_end_date, CURDATE()) > 30 THEN 'sedang'
                ELSE 'rendah'
            END,
            priority_calculated_at = NOW()
            WHERE po_end_date IS NOT NULL
              AND priority <> (
                CASE
                    WHEN po_end_date IS NOT NULL AND DATEDIFF(po_end_date, CURDATE()) > 60 THEN 'tinggi'
                    WHEN po_end_date IS NOT NULL AND DATEDIFF(po_end_date, CURDATE()) > 30 THEN 'sedang'
                    ELSE 'rendah'
                END
              )
        ");
    }

    /**
     * Reverse the migrations.
     *
     * This attempts a best-effort rollback by reintroducing legacy enum tokens and remapping
     * 'sedang' -> 'normal' and 'tinggi' -> 'mendesak'. Note that some information may be lost
     * if the mapping is not bijective; adjust as needed.
     */
    public function down(): void
    {
        // 1) Re-introduce the legacy enum tokens alongside the new one to allow remapping.
        DB::statement("
            ALTER TABLE orders
            MODIFY COLUMN priority ENUM('rendah','normal','tinggi','mendesak','sedang')
            DEFAULT 'normal'
        ");

        // 2) Remap values back to legacy tokens.
        //    - map 'sedang' -> 'normal'
        //    - map 'tinggi' -> 'mendesak'  (only if you want to revert high urgency back to 'mendesak')
        DB::table("orders")
            ->where("priority", "sedang")
            ->update(["priority" => "normal"]);
        DB::table("orders")
            ->where("priority", "tinggi")
            ->update(["priority" => "mendesak"]);

        // 3) Restore the original enum definition (legacy set).
        DB::statement("
            ALTER TABLE orders
            MODIFY COLUMN priority ENUM('rendah','normal','tinggi','mendesak')
            NOT NULL
            DEFAULT 'normal'
        ");
    }
};
