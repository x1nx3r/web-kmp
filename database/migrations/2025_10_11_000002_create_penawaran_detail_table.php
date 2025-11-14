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
        Schema::create('penawaran_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penawaran_id')->constrained('penawaran')->cascadeOnDelete();
            $table->foreignId('bahan_baku_klien_id')->constrained('bahan_baku_klien')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete()->comment('Selected supplier for this material');
            $table->foreignId('bahan_baku_supplier_id')->constrained('bahan_baku_supplier')->cascadeOnDelete()->comment('Specific supplier material entry');
            
            // Material reference (cached for historical record)
            $table->string('nama_material')->comment('Material name at time of quotation');
            $table->string('satuan', 50)->comment('Unit (kg, pcs, m, etc.)');
            
            // Quantities and prices
            $table->decimal('quantity', 10, 2);
            $table->decimal('harga_klien', 15, 2)->comment('Client price per unit');
            $table->decimal('harga_supplier', 15, 2)->comment('Supplier cost per unit at time of quotation');
            $table->boolean('is_custom_price')->default(false)->comment('If custom client price was used');
            
            // Calculated totals
            $table->decimal('subtotal_revenue', 15, 2)->comment('quantity * harga_klien');
            $table->decimal('subtotal_cost', 15, 2)->comment('quantity * harga_supplier');
            $table->decimal('subtotal_profit', 15, 2)->comment('subtotal_revenue - subtotal_cost');
            $table->decimal('margin_percentage', 5, 2)->comment('(subtotal_profit / subtotal_revenue) * 100');
            
            // Notes
            $table->text('notes')->nullable()->comment('Item-specific notes');
            
            $table->timestamps();
            
            // Indexes
            $table->index('penawaran_id');
            $table->index('bahan_baku_klien_id');
            $table->index('supplier_id');
            $table->index('bahan_baku_supplier_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penawaran_detail');
    }
};
