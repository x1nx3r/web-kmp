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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('po_number')->nullable()->after('priority');
            $table->date('po_start_date')->nullable()->after('po_number');
            $table->date('po_end_date')->nullable()->after('po_start_date');
            $table->string('po_document_path')->nullable()->after('po_end_date');
            $table->string('po_document_original_name')->nullable()->after('po_document_path');
            $table->timestamp('priority_calculated_at')->nullable()->after('po_document_original_name');

            $table->index('po_number');
            $table->index('po_end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_po_number_index');
            $table->dropIndex('orders_po_end_date_index');

            $table->dropColumn([
                'po_number',
                'po_start_date',
                'po_end_date',
                'po_document_path',
                'po_document_original_name',
                'priority_calculated_at',
            ]);
        });
    }
};
