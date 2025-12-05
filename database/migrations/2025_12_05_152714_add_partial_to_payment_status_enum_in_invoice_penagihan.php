<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify ENUM to add 'partial' value
        DB::statement("ALTER TABLE invoice_penagihan MODIFY COLUMN payment_status ENUM('unpaid', 'partial', 'paid', 'overdue') DEFAULT 'unpaid'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First update any 'partial' to 'unpaid' before reverting
        DB::statement("UPDATE invoice_penagihan SET payment_status = 'unpaid' WHERE payment_status = 'partial'");
        DB::statement("ALTER TABLE invoice_penagihan MODIFY COLUMN payment_status ENUM('unpaid', 'paid', 'overdue') DEFAULT 'unpaid'");
    }
};
