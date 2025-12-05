<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('order_details')) {
            return;
        }

        if (Schema::hasColumn('order_details', 'supplier_id')) {
            try {
                DB::statement('ALTER TABLE `order_details` DROP FOREIGN KEY `order_details_supplier_id_foreign`');
            } catch (\Throwable $e) {
                // Constraint already removed or never created.
            }

            foreach ([
                'order_details_supplier_id_status_index',
                'order_details_bahan_baku_klien_id_supplier_id_index',
            ] as $indexName) {
                try {
                    DB::statement("ALTER TABLE `order_details` DROP INDEX `{$indexName}`");
                } catch (\Throwable $e) {
                    // Index already removed or never created.
                }
            }
        }

        $columns = [
            'supplier_id',
            'harga_supplier',
            'total_hpp',
            'margin_per_unit',
            'total_margin',
            'margin_percentage',
        ];

        $existing = array_filter($columns, static fn (string $column) => Schema::hasColumn('order_details', $column));

        if (!empty($existing)) {
            Schema::table('order_details', function (Blueprint $table) use ($existing) {
                $table->dropColumn($existing);
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('order_details')) {
            return;
        }

        Schema::table('order_details', function (Blueprint $table) {
            if (!Schema::hasColumn('order_details', 'supplier_id')) {
                $table->foreignId('supplier_id')
                    ->nullable()
                    ->after('bahan_baku_klien_id')
                    ->constrained('suppliers')
                    ->onDelete('restrict');
            }

            if (!Schema::hasColumn('order_details', 'harga_supplier')) {
                $table->decimal('harga_supplier', 12, 2)->nullable()->after('satuan');
            }

            if (!Schema::hasColumn('order_details', 'total_hpp')) {
                $table->decimal('total_hpp', 15, 2)->nullable()->after('harga_supplier');
            }

            if (!Schema::hasColumn('order_details', 'margin_per_unit')) {
                $table->decimal('margin_per_unit', 12, 2)->nullable()->after('total_harga');
            }

            if (!Schema::hasColumn('order_details', 'total_margin')) {
                $table->decimal('total_margin', 15, 2)->nullable()->after('margin_per_unit');
            }

            if (!Schema::hasColumn('order_details', 'margin_percentage')) {
                $table->decimal('margin_percentage', 5, 2)->nullable()->after('total_margin');
            }
        });
    }
};
