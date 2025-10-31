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
        Schema::table('order_details', function (Blueprint $table) {
            // Remove single supplier constraint - ALL suppliers will be auto-populated as options
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
            
            // Remove supplier-specific pricing - will be managed in order_suppliers
            $table->dropColumn(['harga_supplier', 'total_hpp']);
            
            // Remove single margin calculation - will show margin analysis from order_suppliers
            $table->dropColumn(['margin_per_unit', 'total_margin', 'margin_percentage']);
            
            // Add supplier options summary (from order_suppliers)
            $table->decimal('cheapest_price', 12, 2)->nullable()->after('satuan');
            $table->decimal('most_expensive_price', 12, 2)->nullable()->after('cheapest_price');
            $table->decimal('recommended_price', 12, 2)->nullable()->after('most_expensive_price');
            
            // Margin analysis for decision making
            $table->decimal('best_margin_percentage', 5, 2)->nullable()->after('total_harga');
            $table->decimal('worst_margin_percentage', 5, 2)->nullable()->after('best_margin_percentage');
            $table->decimal('recommended_margin_percentage', 5, 2)->nullable()->after('worst_margin_percentage');
            
            // Supplier availability
            $table->integer('available_suppliers_count')->default(0)->after('recommended_margin_percentage');
            $table->foreignId('recommended_supplier_id')->nullable()->after('available_suppliers_count')->constrained('suppliers')->onDelete('set null');
            
            // Fulfillment tracking (calculated from pengiriman via order_suppliers)
            $table->decimal('total_shipped_quantity', 10, 2)->default(0)->after('qty_shipped');
            $table->decimal('remaining_quantity', 10, 2)->default(0)->after('total_shipped_quantity');
            $table->integer('suppliers_used_count')->default(0)->after('remaining_quantity');
            
            // Auto-population status
            $table->boolean('supplier_options_populated')->default(false)->after('suppliers_used_count');
            $table->timestamp('options_populated_at')->nullable()->after('supplier_options_populated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            // Restore single supplier fields
            $table->foreignId('supplier_id')->after('bahan_baku_klien_id')->constrained('suppliers')->onDelete('restrict');
            
            // Restore supplier-specific pricing
            $table->decimal('harga_supplier', 12, 2)->after('satuan');
            $table->decimal('total_hpp', 15, 2)->after('harga_supplier');
            
            // Restore single margin calculation
            $table->decimal('margin_per_unit', 12, 2)->after('total_harga');
            $table->decimal('total_margin', 15, 2)->after('margin_per_unit');
            $table->decimal('margin_percentage', 5, 2)->after('total_margin');
            
            // Remove new fields
            $table->dropForeign(['recommended_supplier_id']);
            $table->dropColumn([
                'cheapest_price',
                'most_expensive_price',
                'recommended_price',
                'best_margin_percentage',
                'worst_margin_percentage',
                'recommended_margin_percentage',
                'available_suppliers_count',
                'recommended_supplier_id',
                'total_shipped_quantity',
                'remaining_quantity',
                'suppliers_used_count',
                'supplier_options_populated',
                'options_populated_at'
            ]);
        });
    }
};
