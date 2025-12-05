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
        Schema::create('order_suppliers', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('order_detail_id')->constrained('order_details')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            
            // Link to material-supplier relationship
            $table->foreignId('bahan_baku_supplier_id')->constrained('bahan_baku_supplier')->onDelete('cascade');
            
            // Pricing info (from bahan_baku_supplier)
            $table->decimal('unit_price', 15, 2); // Price per unit from this supplier
            
            // Shipped quantity tracking (calculated from pengiriman)
            $table->decimal('shipped_quantity', 15, 2)->default(0); // Total delivered by this supplier
            $table->decimal('shipped_amount', 15, 2)->default(0); // Total value delivered
            
            // Auto-calculated margin data (for comparison purposes)
            $table->decimal('calculated_margin', 8, 4)->nullable(); // Margin percentage if this supplier is used
            $table->decimal('potential_profit', 15, 2)->nullable(); // Potential profit per unit
            
            // Supplier ranking and selection
            $table->boolean('is_recommended')->default(false); // Best margin supplier
            $table->integer('price_rank')->nullable(); // 1 = cheapest, 2 = second cheapest, etc.
            
            // Status tracking
            $table->boolean('is_available')->default(true); // Supplier can fulfill this material
            $table->boolean('has_been_used')->default(false); // Purchasing has ordered from this supplier
            
            // Auto-population metadata
            $table->timestamp('price_updated_at')->nullable(); // When price was last updated
            
            $table->timestamps();
            
            // Indexes
            $table->index(['order_detail_id', 'is_available']);
            $table->index(['supplier_id', 'has_been_used']);
            $table->index(['bahan_baku_supplier_id']);
            $table->index(['price_rank', 'calculated_margin']);
            
            // Unique constraint
            $table->unique(['order_detail_id', 'supplier_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_suppliers');
    }
};
