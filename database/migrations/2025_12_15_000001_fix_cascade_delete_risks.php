<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Comprehensive migration to fix cascade delete risks across the application.
 *
 * Strategy applied:
 * 1. User references → SET NULL (users can leave, data stays)
 * 2. Critical entities (Klien/Supplier) → RESTRICT (prevent accidental deletion)
 * 3. Financial records → RESTRICT (never lose payment data)
 * 4. Parent-Child hierarchies → Keep CASCADE (logical, but parent has SoftDeletes)
 * 5. Add SoftDeletes to models that don't have it
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // =========================================================================
        // PART 1: Add SoftDeletes columns to tables that don't have them
        // =========================================================================

        // Orders table - critical business data
        if (!Schema::hasColumn('orders', 'deleted_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Order details
        if (!Schema::hasColumn('order_details', 'deleted_at')) {
            Schema::table('order_details', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Order suppliers
        if (!Schema::hasColumn('order_suppliers', 'deleted_at')) {
            Schema::table('order_suppliers', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Order winners
        if (!Schema::hasColumn('order_winners', 'deleted_at')) {
            Schema::table('order_winners', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Catatan Piutang (Payables) - financial data
        if (!Schema::hasColumn('catatan_piutangs', 'deleted_at')) {
            Schema::table('catatan_piutangs', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Pembayaran Piutang (Payments) - financial data
        if (!Schema::hasColumn('pembayaran_piutang', 'deleted_at')) {
            Schema::table('pembayaran_piutang', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Catatan Piutang Pabrik - financial data
        if (!Schema::hasColumn('catatan_piutang_pabriks', 'deleted_at')) {
            Schema::table('catatan_piutang_pabriks', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Pembayaran Piutang Pabrik - financial data
        if (!Schema::hasColumn('pembayaran_piutang_pabriks', 'deleted_at')) {
            Schema::table('pembayaran_piutang_pabriks', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Invoice Penagihan - financial data
        if (!Schema::hasColumn('invoice_penagihan', 'deleted_at')) {
            Schema::table('invoice_penagihan', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Approval Pembayaran
        if (!Schema::hasColumn('approval_pembayaran', 'deleted_at')) {
            Schema::table('approval_pembayaran', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Approval Penagihan
        if (!Schema::hasColumn('approval_penagihan', 'deleted_at')) {
            Schema::table('approval_penagihan', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Approval History
        if (!Schema::hasColumn('approval_history', 'deleted_at')) {
            Schema::table('approval_history', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Riwayat Harga Klien
        if (!Schema::hasColumn('riwayat_harga_klien', 'deleted_at')) {
            Schema::table('riwayat_harga_klien', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Riwayat Harga Bahan Baku
        if (!Schema::hasColumn('riwayat_harga_bahan_baku', 'deleted_at')) {
            Schema::table('riwayat_harga_bahan_baku', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Target Omset
        if (!Schema::hasColumn('target_omset', 'deleted_at')) {
            Schema::table('target_omset', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Target Omset Snapshots
        if (!Schema::hasColumn('target_omset_snapshots', 'deleted_at')) {
            Schema::table('target_omset_snapshots', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Supplier Evaluations
        if (!Schema::hasColumn('supplier_evaluations', 'deleted_at')) {
            Schema::table('supplier_evaluations', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Supplier Evaluation Details
        if (!Schema::hasColumn('supplier_evaluation_details', 'deleted_at')) {
            Schema::table('supplier_evaluation_details', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // =========================================================================
        // PART 2: Fix User references - Change CASCADE to SET NULL
        // Users can leave the company, but their work should remain
        // =========================================================================

        // Orders.created_by - make nullable and set null on delete
        Schema::table('orders', function (Blueprint $table) {
            // Drop existing foreign key
            $table->dropForeign(['created_by']);
        });

        Schema::table('orders', function (Blueprint $table) {
            // Make column nullable
            $table->unsignedBigInteger('created_by')->nullable()->change();
            // Recreate with SET NULL
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });

        // Pengiriman.purchasing_id - make nullable and set null on delete
        Schema::table('pengiriman', function (Blueprint $table) {
            $table->dropForeign(['purchasing_id']);
        });

        Schema::table('pengiriman', function (Blueprint $table) {
            $table->unsignedBigInteger('purchasing_id')->nullable()->change();
            $table->foreign('purchasing_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });

        // Invoice_penagihan.created_by - make nullable and set null on delete
        Schema::table('invoice_penagihan', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        Schema::table('invoice_penagihan', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });

        // Forecasts.purchasing_id - already nullable, just fix the constraint
        if ($this->foreignKeyExists('forecasts', 'forecasts_purchasing_id_foreign')) {
            Schema::table('forecasts', function (Blueprint $table) {
                $table->dropForeign(['purchasing_id']);
            });

            Schema::table('forecasts', function (Blueprint $table) {
                $table->foreign('purchasing_id')
                      ->references('id')
                      ->on('users')
                      ->nullOnDelete();
            });
        }

        // Order_winners.user_id - make nullable and set null on delete
        Schema::table('order_winners', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('order_winners', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });

        // Approval_history.user_id - make nullable and set null on delete
        Schema::table('approval_history', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('approval_history', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });

        // Riwayat_harga_klien.updated_by_marketing - already has RESTRICT, leave it
        // This is correct - should not delete price history if user is deleted

        // Pembayaran_piutang.created_by - already nullable with SET NULL, leave it

        // Pembayaran_piutang_pabriks.created_by - change to SET NULL
        Schema::table('pembayaran_piutang_pabriks', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        Schema::table('pembayaran_piutang_pabriks', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });

        // =========================================================================
        // PART 3: Fix Klien references - Change CASCADE to RESTRICT
        // Prevent accidental deletion of clients with existing data
        // =========================================================================

        // Orders.klien_id - change to RESTRICT
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['klien_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('klien_id')
                  ->references('id')
                  ->on('kliens')
                  ->restrictOnDelete();
        });

        // Penawaran.klien_id - change to RESTRICT
        Schema::table('penawaran', function (Blueprint $table) {
            $table->dropForeign(['klien_id']);
        });

        Schema::table('penawaran', function (Blueprint $table) {
            $table->foreign('klien_id')
                  ->references('id')
                  ->on('kliens')
                  ->restrictOnDelete();
        });

        // Bahan_baku_klien.klien_id - change to RESTRICT
        if ($this->foreignKeyExists('bahan_baku_klien', 'bahan_baku_klien_klien_id_foreign')) {
            Schema::table('bahan_baku_klien', function (Blueprint $table) {
                $table->dropForeign(['klien_id']);
            });

            Schema::table('bahan_baku_klien', function (Blueprint $table) {
                $table->foreign('klien_id')
                      ->references('id')
                      ->on('kliens')
                      ->restrictOnDelete();
            });
        }

        // Catatan_piutang_pabriks.klien_id - change to RESTRICT
        Schema::table('catatan_piutang_pabriks', function (Blueprint $table) {
            $table->dropForeign(['klien_id']);
        });

        Schema::table('catatan_piutang_pabriks', function (Blueprint $table) {
            $table->foreign('klien_id')
                  ->references('id')
                  ->on('kliens')
                  ->restrictOnDelete();
        });

        // =========================================================================
        // PART 4: Fix Supplier references - Change CASCADE to RESTRICT
        // Prevent accidental deletion of suppliers with existing data
        // =========================================================================

        // Bahan_baku_supplier.supplier_id - change to RESTRICT
        Schema::table('bahan_baku_supplier', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
        });

        Schema::table('bahan_baku_supplier', function (Blueprint $table) {
            $table->foreign('supplier_id')
                  ->references('id')
                  ->on('suppliers')
                  ->restrictOnDelete();
        });

        // Catatan_piutangs.supplier_id - change to RESTRICT
        Schema::table('catatan_piutangs', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
        });

        Schema::table('catatan_piutangs', function (Blueprint $table) {
            $table->foreign('supplier_id')
                  ->references('id')
                  ->on('suppliers')
                  ->restrictOnDelete();
        });

        // Order_suppliers.supplier_id - change to RESTRICT
        Schema::table('order_suppliers', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
        });

        Schema::table('order_suppliers', function (Blueprint $table) {
            $table->foreign('supplier_id')
                  ->references('id')
                  ->on('suppliers')
                  ->restrictOnDelete();
        });

        // Penawaran_detail.supplier_id - change to SET NULL (nullable field)
        Schema::table('penawaran_detail', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
        });

        Schema::table('penawaran_detail', function (Blueprint $table) {
            $table->foreign('supplier_id')
                  ->references('id')
                  ->on('suppliers')
                  ->nullOnDelete();
        });

        // Penawaran_alternative_suppliers.supplier_id - change to RESTRICT
        Schema::table('penawaran_alternative_suppliers', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
        });

        Schema::table('penawaran_alternative_suppliers', function (Blueprint $table) {
            $table->foreign('supplier_id')
                  ->references('id')
                  ->on('suppliers')
                  ->restrictOnDelete();
        });

        // =========================================================================
        // PART 5: Fix BahanBakuSupplier references - Change CASCADE to RESTRICT
        // Prevent losing order/delivery data when material is deleted
        // =========================================================================

        // Order_suppliers.bahan_baku_supplier_id - change to RESTRICT
        Schema::table('order_suppliers', function (Blueprint $table) {
            $table->dropForeign(['bahan_baku_supplier_id']);
        });

        Schema::table('order_suppliers', function (Blueprint $table) {
            $table->foreign('bahan_baku_supplier_id')
                  ->references('id')
                  ->on('bahan_baku_supplier')
                  ->restrictOnDelete();
        });

        // Forecast_details.bahan_baku_supplier_id - change to RESTRICT
        Schema::table('forecast_details', function (Blueprint $table) {
            $table->dropForeign(['bahan_baku_supplier_id']);
        });

        Schema::table('forecast_details', function (Blueprint $table) {
            $table->foreign('bahan_baku_supplier_id')
                  ->references('id')
                  ->on('bahan_baku_supplier')
                  ->restrictOnDelete();
        });

        // Pengiriman_details.bahan_baku_supplier_id - change to RESTRICT
        Schema::table('pengiriman_details', function (Blueprint $table) {
            $table->dropForeign(['bahan_baku_supplier_id']);
        });

        Schema::table('pengiriman_details', function (Blueprint $table) {
            $table->foreign('bahan_baku_supplier_id')
                  ->references('id')
                  ->on('bahan_baku_supplier')
                  ->restrictOnDelete();
        });

        // Penawaran_detail.bahan_baku_supplier_id - change to SET NULL (nullable)
        Schema::table('penawaran_detail', function (Blueprint $table) {
            $table->dropForeign(['bahan_baku_supplier_id']);
        });

        Schema::table('penawaran_detail', function (Blueprint $table) {
            $table->foreign('bahan_baku_supplier_id')
                  ->references('id')
                  ->on('bahan_baku_supplier')
                  ->nullOnDelete();
        });

        // Penawaran_alternative_suppliers.bahan_baku_supplier_id - change to RESTRICT
        Schema::table('penawaran_alternative_suppliers', function (Blueprint $table) {
            $table->dropForeign(['bahan_baku_supplier_id']);
        });

        Schema::table('penawaran_alternative_suppliers', function (Blueprint $table) {
            $table->foreign('bahan_baku_supplier_id')
                  ->references('id')
                  ->on('bahan_baku_supplier')
                  ->restrictOnDelete();
        });

        // =========================================================================
        // PART 6: Fix BahanBakuKlien references - Change CASCADE to RESTRICT
        // Prevent losing order data when client material is deleted
        // =========================================================================

        // Order_details.bahan_baku_klien_id - change to RESTRICT
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropForeign(['bahan_baku_klien_id']);
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->foreign('bahan_baku_klien_id')
                  ->references('id')
                  ->on('bahan_baku_klien')
                  ->restrictOnDelete();
        });

        // Penawaran_detail.bahan_baku_klien_id - change to RESTRICT
        Schema::table('penawaran_detail', function (Blueprint $table) {
            $table->dropForeign(['bahan_baku_klien_id']);
        });

        Schema::table('penawaran_detail', function (Blueprint $table) {
            $table->foreign('bahan_baku_klien_id')
                  ->references('id')
                  ->on('bahan_baku_klien')
                  ->restrictOnDelete();
        });

        // =========================================================================
        // PART 7: Fix Financial record references - Change CASCADE to RESTRICT
        // Never lose payment records
        // =========================================================================

        // Pembayaran_piutang.catatan_piutang_id - change to RESTRICT
        Schema::table('pembayaran_piutang', function (Blueprint $table) {
            $table->dropForeign(['catatan_piutang_id']);
        });

        Schema::table('pembayaran_piutang', function (Blueprint $table) {
            $table->foreign('catatan_piutang_id')
                  ->references('id')
                  ->on('catatan_piutangs')
                  ->restrictOnDelete();
        });

        // Pembayaran_piutang_pabriks.invoice_penagihan_id - change to RESTRICT
        Schema::table('pembayaran_piutang_pabriks', function (Blueprint $table) {
            $table->dropForeign(['invoice_penagihan_id']);
        });

        Schema::table('pembayaran_piutang_pabriks', function (Blueprint $table) {
            $table->foreign('invoice_penagihan_id')
                  ->references('id')
                  ->on('invoice_penagihan')
                  ->restrictOnDelete();
        });

        // =========================================================================
        // PART 8: Keep appropriate CASCADE relationships
        // These are logical parent-child where deletion should cascade
        // (Parent models already have or now have SoftDeletes)
        // =========================================================================

        // The following relationships are intentionally kept as CASCADE:
        // - order_details.order_id → orders (order details belong to order)
        // - order_suppliers.order_detail_id → order_details (supplier options belong to detail)
        // - order_winners.order_id → orders (winner belongs to order)
        // - forecast_details.forecast_id → forecasts (details belong to forecast)
        // - forecast_details.purchase_order_bahan_baku_id → order_details (keeps cascade)
        // - pengiriman_details.pengiriman_id → pengiriman (details belong to delivery)
        // - pengiriman_details.purchase_order_bahan_baku_id → order_details (keeps cascade)
        // - penawaran_detail.penawaran_id → penawaran (details belong to quotation)
        // - penawaran_alternative_suppliers.penawaran_detail_id → penawaran_detail (alts belong to detail)
        // - riwayat_harga_klien.bahan_baku_klien_id → bahan_baku_klien (history belongs to material)
        // - riwayat_harga_bahan_baku.bahan_baku_supplier_id → bahan_baku_supplier (history belongs to material)
        // - target_omset_snapshots.target_omset_id → target_omset (snapshots belong to target)
        // - supplier_evaluation_details.supplier_evaluation_id → supplier_evaluations (details belong to eval)
        // - approval_pembayaran.pengiriman_id → pengiriman (approval belongs to delivery)
        // - invoice_penagihan.pengiriman_id → pengiriman (invoice belongs to delivery)
        // - approval_penagihan.invoice_id → invoice_penagihan (approval belongs to invoice)
        // - approval_penagihan.pengiriman_id → pengiriman (keeps relation)
        // - approval_history.pengiriman_id → pengiriman (history belongs to delivery)
        // - approval_history.invoice_id → invoice_penagihan (history belongs to invoice)
        // - supplier_evaluations.pengiriman_id → pengiriman (evaluation belongs to delivery)

        // Note: forecasts.purchase_order_id and pengiriman.purchase_order_id also keep CASCADE
        // since they are children of orders, and orders now has SoftDeletes
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // =========================================================================
        // Reverse Part 7: Financial records back to CASCADE
        // =========================================================================

        Schema::table('pembayaran_piutang_pabriks', function (Blueprint $table) {
            $table->dropForeign(['invoice_penagihan_id']);
        });
        Schema::table('pembayaran_piutang_pabriks', function (Blueprint $table) {
            $table->foreign('invoice_penagihan_id')
                  ->references('id')
                  ->on('invoice_penagihan')
                  ->cascadeOnDelete();
        });

        Schema::table('pembayaran_piutang', function (Blueprint $table) {
            $table->dropForeign(['catatan_piutang_id']);
        });
        Schema::table('pembayaran_piutang', function (Blueprint $table) {
            $table->foreign('catatan_piutang_id')
                  ->references('id')
                  ->on('catatan_piutangs')
                  ->cascadeOnDelete();
        });

        // =========================================================================
        // Reverse Part 6: BahanBakuKlien back to CASCADE
        // =========================================================================

        Schema::table('penawaran_detail', function (Blueprint $table) {
            $table->dropForeign(['bahan_baku_klien_id']);
        });
        Schema::table('penawaran_detail', function (Blueprint $table) {
            $table->foreign('bahan_baku_klien_id')
                  ->references('id')
                  ->on('bahan_baku_klien')
                  ->cascadeOnDelete();
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropForeign(['bahan_baku_klien_id']);
        });
        Schema::table('order_details', function (Blueprint $table) {
            $table->foreign('bahan_baku_klien_id')
                  ->references('id')
                  ->on('bahan_baku_klien')
                  ->cascadeOnDelete();
        });

        // =========================================================================
        // Reverse Part 5: BahanBakuSupplier back to CASCADE
        // =========================================================================

        Schema::table('penawaran_alternative_suppliers', function (Blueprint $table) {
            $table->dropForeign(['bahan_baku_supplier_id']);
        });
        Schema::table('penawaran_alternative_suppliers', function (Blueprint $table) {
            $table->foreign('bahan_baku_supplier_id')
                  ->references('id')
                  ->on('bahan_baku_supplier')
                  ->cascadeOnDelete();
        });

        Schema::table('penawaran_detail', function (Blueprint $table) {
            $table->dropForeign(['bahan_baku_supplier_id']);
        });
        Schema::table('penawaran_detail', function (Blueprint $table) {
            $table->foreign('bahan_baku_supplier_id')
                  ->references('id')
                  ->on('bahan_baku_supplier')
                  ->cascadeOnDelete();
        });

        Schema::table('pengiriman_details', function (Blueprint $table) {
            $table->dropForeign(['bahan_baku_supplier_id']);
        });
        Schema::table('pengiriman_details', function (Blueprint $table) {
            $table->foreign('bahan_baku_supplier_id')
                  ->references('id')
                  ->on('bahan_baku_supplier')
                  ->cascadeOnDelete();
        });

        Schema::table('forecast_details', function (Blueprint $table) {
            $table->dropForeign(['bahan_baku_supplier_id']);
        });
        Schema::table('forecast_details', function (Blueprint $table) {
            $table->foreign('bahan_baku_supplier_id')
                  ->references('id')
                  ->on('bahan_baku_supplier')
                  ->cascadeOnDelete();
        });

        Schema::table('order_suppliers', function (Blueprint $table) {
            $table->dropForeign(['bahan_baku_supplier_id']);
        });
        Schema::table('order_suppliers', function (Blueprint $table) {
            $table->foreign('bahan_baku_supplier_id')
                  ->references('id')
                  ->on('bahan_baku_supplier')
                  ->cascadeOnDelete();
        });

        // =========================================================================
        // Reverse Part 4: Supplier back to CASCADE
        // =========================================================================

        Schema::table('penawaran_alternative_suppliers', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
        });
        Schema::table('penawaran_alternative_suppliers', function (Blueprint $table) {
            $table->foreign('supplier_id')
                  ->references('id')
                  ->on('suppliers')
                  ->cascadeOnDelete();
        });

        Schema::table('penawaran_detail', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
        });
        Schema::table('penawaran_detail', function (Blueprint $table) {
            $table->foreign('supplier_id')
                  ->references('id')
                  ->on('suppliers')
                  ->cascadeOnDelete();
        });

        Schema::table('order_suppliers', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
        });
        Schema::table('order_suppliers', function (Blueprint $table) {
            $table->foreign('supplier_id')
                  ->references('id')
                  ->on('suppliers')
                  ->cascadeOnDelete();
        });

        Schema::table('catatan_piutangs', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
        });
        Schema::table('catatan_piutangs', function (Blueprint $table) {
            $table->foreign('supplier_id')
                  ->references('id')
                  ->on('suppliers')
                  ->cascadeOnDelete();
        });

        Schema::table('bahan_baku_supplier', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
        });
        Schema::table('bahan_baku_supplier', function (Blueprint $table) {
            $table->foreign('supplier_id')
                  ->references('id')
                  ->on('suppliers')
                  ->cascadeOnDelete();
        });

        // =========================================================================
        // Reverse Part 3: Klien back to CASCADE
        // =========================================================================

        Schema::table('catatan_piutang_pabriks', function (Blueprint $table) {
            $table->dropForeign(['klien_id']);
        });
        Schema::table('catatan_piutang_pabriks', function (Blueprint $table) {
            $table->foreign('klien_id')
                  ->references('id')
                  ->on('kliens')
                  ->cascadeOnDelete();
        });

        if ($this->foreignKeyExists('bahan_baku_klien', 'bahan_baku_klien_klien_id_foreign')) {
            Schema::table('bahan_baku_klien', function (Blueprint $table) {
                $table->dropForeign(['klien_id']);
            });
            Schema::table('bahan_baku_klien', function (Blueprint $table) {
                $table->foreign('klien_id')
                      ->references('id')
                      ->on('kliens')
                      ->cascadeOnDelete();
            });
        }

        Schema::table('penawaran', function (Blueprint $table) {
            $table->dropForeign(['klien_id']);
        });
        Schema::table('penawaran', function (Blueprint $table) {
            $table->foreign('klien_id')
                  ->references('id')
                  ->on('kliens')
                  ->cascadeOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['klien_id']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('klien_id')
                  ->references('id')
                  ->on('kliens')
                  ->cascadeOnDelete();
        });

        // =========================================================================
        // Reverse Part 2: User references back to CASCADE
        // =========================================================================

        Schema::table('pembayaran_piutang_pabriks', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });
        Schema::table('pembayaran_piutang_pabriks', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable(false)->change();
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
        });

        Schema::table('approval_history', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('approval_history', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
        });

        Schema::table('order_winners', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('order_winners', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
        });

        if ($this->foreignKeyExists('forecasts', 'forecasts_purchasing_id_foreign')) {
            Schema::table('forecasts', function (Blueprint $table) {
                $table->dropForeign(['purchasing_id']);
            });
            Schema::table('forecasts', function (Blueprint $table) {
                $table->foreign('purchasing_id')
                      ->references('id')
                      ->on('users')
                      ->nullOnDelete();
            });
        }

        Schema::table('invoice_penagihan', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });
        Schema::table('invoice_penagihan', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable(false)->change();
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
        });

        Schema::table('pengiriman', function (Blueprint $table) {
            $table->dropForeign(['purchasing_id']);
        });
        Schema::table('pengiriman', function (Blueprint $table) {
            $table->unsignedBigInteger('purchasing_id')->nullable(false)->change();
            $table->foreign('purchasing_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable(false)->change();
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
        });

        // =========================================================================
        // Reverse Part 1: Remove SoftDeletes columns
        // =========================================================================

        $tablesWithSoftDeletes = [
            'orders',
            'order_details',
            'order_suppliers',
            'order_winners',
            'catatan_piutangs',
            'pembayaran_piutang',
            'catatan_piutang_pabriks',
            'pembayaran_piutang_pabriks',
            'invoice_penagihan',
            'approval_pembayaran',
            'approval_penagihan',
            'approval_history',
            'riwayat_harga_klien',
            'riwayat_harga_bahan_baku',
            'target_omset',
            'target_omset_snapshots',
            'supplier_evaluations',
            'supplier_evaluation_details',
        ];

        foreach ($tablesWithSoftDeletes as $tableName) {
            if (Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }

    /**
     * Check if a foreign key exists on a table
     */
    private function foreignKeyExists(string $table, string $foreignKey): bool
    {
        $connection = Schema::getConnection();
        $databaseName = $connection->getDatabaseName();

        // For SQLite, we can't easily check foreign keys, assume they exist
        if ($connection->getDriverName() === 'sqlite') {
            return true;
        }

        $result = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = ?
            AND TABLE_NAME = ?
            AND CONSTRAINT_NAME = ?
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ", [$databaseName, $table, $foreignKey]);

        return count($result) > 0;
    }
};
