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
        Schema::table('approval_history', function (Blueprint $table) {
            // Drop and recreate the role column with updated enum values
            DB::statement("ALTER TABLE approval_history MODIFY COLUMN role ENUM('staff', 'manager_keuangan', 'direktur', 'superadmin') NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_history', function (Blueprint $table) {
            // Revert to original enum values
            DB::statement("ALTER TABLE approval_history MODIFY COLUMN role ENUM('staff', 'manager_keuangan', 'superadmin') NOT NULL");
        });
    }
};
