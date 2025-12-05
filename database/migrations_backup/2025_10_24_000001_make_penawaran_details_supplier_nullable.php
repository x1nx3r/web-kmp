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
        Schema::table('penawaran_detail', function (Blueprint $table) {
            // Drop FK constraints so we can alter nullability
            if (Schema::hasColumn('penawaran_detail', 'supplier_id')) {
                $table->dropForeign(['supplier_id']);
            }
            if (Schema::hasColumn('penawaran_detail', 'bahan_baku_supplier_id')) {
                $table->dropForeign(['bahan_baku_supplier_id']);
            }

            // Make supplier references and computed cost fields nullable
            $table->unsignedBigInteger('supplier_id')->nullable()->change();
            $table->unsignedBigInteger('bahan_baku_supplier_id')->nullable()->change();

            // Prices/totals may be empty when no supplier is selected on save
            $table->decimal('harga_supplier', 15, 2)->nullable()->change();
            $table->decimal('subtotal_cost', 15, 2)->nullable()->change();
            $table->decimal('subtotal_profit', 15, 2)->nullable()->change();
            $table->decimal('margin_percentage', 5, 2)->nullable()->change();

            // Recreate foreign keys (allowing NULL)
            $table->foreign('supplier_id')->references('id')->on('suppliers')->cascadeOnDelete();
            $table->foreign('bahan_baku_supplier_id')->references('id')->on('bahan_baku_supplier')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penawaran_detail', function (Blueprint $table) {
            // Reverting nullability back to NOT NULL may be destructive if NULLs exist.
            // This down migration attempts to restore the original non-nullable schema,
            // but you should ensure there are no NULL values before running it.

            // Drop foreign keys
            if (Schema::hasColumn('penawaran_detail', 'supplier_id')) {
                $table->dropForeign(['supplier_id']);
            }
            if (Schema::hasColumn('penawaran_detail', 'bahan_baku_supplier_id')) {
                $table->dropForeign(['bahan_baku_supplier_id']);
            }

            // Change columns back to not nullable
            $table->unsignedBigInteger('supplier_id')->nullable(false)->change();
            $table->unsignedBigInteger('bahan_baku_supplier_id')->nullable(false)->change();
            $table->decimal('harga_supplier', 15, 2)->nullable(false)->change();
            $table->decimal('subtotal_cost', 15, 2)->nullable(false)->change();
            $table->decimal('subtotal_profit', 15, 2)->nullable(false)->change();
            $table->decimal('margin_percentage', 5, 2)->nullable(false)->change();

            // Recreate FKs
            $table->foreign('supplier_id')->references('id')->on('suppliers')->cascadeOnDelete();
            $table->foreign('bahan_baku_supplier_id')->references('id')->on('bahan_baku_supplier')->cascadeOnDelete();
        });
    }
};
