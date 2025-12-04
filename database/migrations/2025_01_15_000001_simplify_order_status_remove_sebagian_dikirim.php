<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration removes the 'sebagian_dikirim' status from the orders table.
     * Marketing now controls when an order is closed (selesai), not the system automatically.
     *
     * Status flow simplified to:
     * draft -> dikonfirmasi -> diproses -> selesai
     *                              \-> dibatalkan
     */
    public function up(): void
    {
        // First, update any existing orders with 'sebagian_dikirim' to 'diproses'
        DB::table('orders')
            ->where('status', 'sebagian_dikirim')
            ->update(['status' => 'diproses']);

        // Alter the enum to remove 'sebagian_dikirim'
        // MySQL requires recreating the column for enum changes
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('draft', 'dikonfirmasi', 'diproses', 'selesai', 'dibatalkan') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add 'sebagian_dikirim' back to the enum
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('draft', 'dikonfirmasi', 'diproses', 'sebagian_dikirim', 'selesai', 'dibatalkan') DEFAULT 'draft'");
    }
};
