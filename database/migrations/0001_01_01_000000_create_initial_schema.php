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
        // =====================================================================
        // CORE TABLES (No foreign key dependencies)
        // =====================================================================

        // Users table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('user');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        // Password reset tokens
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Cache
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        // Jobs
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['notifiable_type', 'notifiable_id', 'read_at']);
        });

        // Company settings
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->text('company_address');
            $table->string('company_phone');
            $table->string('company_email');
            $table->string('company_website')->nullable();
            $table->string('company_logo')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('tax_number')->nullable();
            $table->text('invoice_terms_conditions')->nullable();
            $table->text('invoice_footer_notes')->nullable();
            $table->decimal('tax_percentage', 5, 2)->default(11.00);
            $table->integer('invoice_due_days')->default(30);
            $table->timestamps();
        });

        // =====================================================================
        // KONTAK KLIEN (Must be before kliens due to contact_person_id FK)
        // =====================================================================

        Schema::create('kontak_klien', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('klien_nama')->index();
            $table->string('nomor_hp')->nullable();
            $table->string('jabatan')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // =====================================================================
        // KLIENS
        // =====================================================================

        Schema::create('kliens', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('cabang');
            $table->text('alamat_lengkap')->nullable();
            $table->foreignId('contact_person_id')->nullable()
                ->constrained('kontak_klien')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // =====================================================================
        // SUPPLIERS
        // =====================================================================

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('alamat')->nullable();
            $table->string('telepon')->nullable();
            $table->string('email')->nullable();
            $table->string('pic')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // =====================================================================
        // BAHAN BAKU KLIEN
        // =====================================================================

        Schema::create('bahan_baku_klien', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klien_id')->nullable()
                ->constrained('kliens');
            $table->string('nama');
            $table->string('satuan')->nullable();
            $table->text('spesifikasi')->nullable();
            $table->decimal('harga_approved', 15, 2)->nullable()
                ->comment('Client approved price per unit');
            $table->timestamp('approved_at')->nullable()
                ->comment('When price was approved');
            $table->foreignId('approved_by_marketing')->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('status');
            $table->boolean('post')->default(false)
                ->comment('Post checkmark status');
            $table->enum('present', [
                'NotUsed', 'Ready', 'Not Reasonable Price', 'Pos Closed',
                'Not Qualified Raw', 'Not Updated Yet', 'Didnt Have Supplier',
                'Factory No Need Yet', 'Confirmed', 'Sample Sent', 'Hold', 'Negotiate'
            ])->default('NotUsed')->comment('Present status dropdown');
            $table->text('cause')->nullable()
                ->comment('Note explaining Present status');
            $table->json('jenis')->nullable()
                ->comment('Category tags: Aqua, Poultry, Ruminansia (can have multiple)');
            $table->timestamps();
            $table->softDeletes();

            $table->index('klien_id', 'idx_bahan_baku_klien_klien');
            $table->index(['klien_id', 'status'], 'idx_klien_status');
            $table->index('approved_by_marketing', 'idx_approved_by');
        });

        // =====================================================================
        // BAHAN BAKU SUPPLIER
        // =====================================================================

        Schema::create('bahan_baku_supplier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')
                ->constrained('suppliers');
            $table->string('nama')->nullable();
            $table->string('slug')->nullable()->unique();
            $table->decimal('harga_per_satuan', 15, 2)->nullable();
            $table->string('satuan')->nullable();
            $table->decimal('stok', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['supplier_id', 'nama'], 'supplier_bahan_baku_unique');
        });

        // =====================================================================
        // RIWAYAT HARGA KLIEN
        // =====================================================================

        Schema::create('riwayat_harga_klien', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_klien_id')
                ->constrained('bahan_baku_klien');
            $table->decimal('harga_lama', 15, 2)->nullable()
                ->comment('Previous approved price, null for first record');
            $table->decimal('harga_approved_baru', 15, 2)
                ->comment('New approved price');
            $table->decimal('selisih_harga', 15, 2)->default(0)
                ->comment('Price difference (new - old)');
            $table->decimal('persentase_perubahan', 8, 4)->default(0)
                ->comment('Percentage change');
            $table->enum('tipe_perubahan', ['naik', 'turun', 'tetap', 'awal'])->default('awal');
            $table->text('keterangan')->nullable()
                ->comment('Notes about price change');
            $table->timestamp('tanggal_perubahan')->useCurrent()
                ->comment('When price was changed');
            $table->foreignId('updated_by_marketing')
                ->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['bahan_baku_klien_id', 'tanggal_perubahan'], 'riwayat_harga_klien_material_tanggal_idx');
            $table->index('tanggal_perubahan', 'riwayat_harga_klien_tanggal_idx');
        });

        // =====================================================================
        // RIWAYAT HARGA BAHAN BAKU (SUPPLIER)
        // =====================================================================

        Schema::create('riwayat_harga_bahan_baku', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_supplier_id')
                ->constrained('bahan_baku_supplier')
                ->cascadeOnDelete();
            $table->decimal('harga_lama', 15, 2)->nullable()
                ->comment('Harga sebelum update, null jika data pertama');
            $table->decimal('harga_baru', 15, 2)
                ->comment('Harga setelah update');
            $table->decimal('selisih_harga', 15, 2)->default(0)
                ->comment('Selisih harga (harga_baru - harga_lama)');
            $table->decimal('persentase_perubahan', 8, 4)->default(0)
                ->comment('Persentase perubahan harga');
            $table->enum('tipe_perubahan', ['naik', 'turun', 'tetap', 'awal'])->default('awal');
            $table->text('keterangan')->nullable()
                ->comment('Keterangan tambahan untuk perubahan harga');
            $table->timestamp('tanggal_perubahan')->useCurrent()
                ->comment('Waktu perubahan harga');
            $table->string('updated_by')->nullable()
                ->comment('User yang melakukan update');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['bahan_baku_supplier_id', 'tanggal_perubahan'], 'riwayat_harga_supplier_tanggal_idx');
            $table->index('tanggal_perubahan', 'riwayat_harga_tanggal_idx');
            $table->index('tipe_perubahan', 'riwayat_harga_tipe_idx');
        });

        // =====================================================================
        // ORDERS
        // =====================================================================

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klien_id')
                ->constrained('kliens');
            $table->foreignId('created_by')->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('no_po');
            $table->date('tanggal_po');
            $table->date('tanggal_pengiriman_awal')->nullable();
            $table->date('tanggal_pengiriman_akhir')->nullable();
            $table->string('hari_kirim')->nullable();
            $table->decimal('total_harga', 15, 2)->default(0);
            $table->enum('status', ['menunggu', 'diproses', 'selesai', 'dibatalkan'])->default('menunggu');
            $table->text('catatan')->nullable();
            $table->string('file_po')->nullable();
            $table->timestamp('file_po_uploaded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // =====================================================================
        // ORDER DETAILS
        // =====================================================================

        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();
            $table->foreignId('bahan_baku_klien_id')
                ->constrained('bahan_baku_klien');
            $table->string('nama_material_po')->nullable()->index();
            $table->decimal('qty', 10, 2);
            $table->string('satuan', 20);
            $table->decimal('cheapest_price', 12, 2)->nullable();
            $table->decimal('most_expensive_price', 12, 2)->nullable();
            $table->decimal('recommended_price', 12, 2)->nullable();
            $table->decimal('harga_jual', 12, 2);
            $table->decimal('total_harga', 15, 2);
            $table->decimal('best_margin_percentage', 5, 2)->nullable();
            $table->decimal('worst_margin_percentage', 5, 2)->nullable();
            $table->decimal('recommended_margin_percentage', 5, 2)->nullable();
            $table->integer('available_suppliers_count')->default(0);
            $table->foreignId('recommended_supplier_id')->nullable()
                ->constrained('suppliers')
                ->nullOnDelete();
            $table->decimal('qty_shipped', 10, 2)->default(0);
            $table->decimal('total_shipped_quantity', 10, 2)->default(0);
            $table->decimal('remaining_quantity', 10, 2)->default(0);
            $table->integer('suppliers_used_count')->default(0);
            $table->boolean('supplier_options_populated')->default(false);
            $table->timestamp('options_populated_at')->nullable();
            $table->enum('status', ['menunggu', 'diproses', 'sebagian_dikirim', 'selesai'])->default('menunggu');
            $table->text('spesifikasi_khusus')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['order_id', 'status']);
            $table->index('bahan_baku_klien_id');
            $table->index('status');
        });

        // =====================================================================
        // ORDER SUPPLIERS
        // =====================================================================

        Schema::create('order_suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_detail_id')
                ->constrained('order_details')
                ->cascadeOnDelete();
            $table->foreignId('supplier_id')
                ->constrained('suppliers');
            $table->foreignId('bahan_baku_supplier_id')
                ->constrained('bahan_baku_supplier');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('shipped_quantity', 15, 2)->default(0);
            $table->decimal('margin_percentage', 5, 2)->nullable();
            $table->boolean('is_selected')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['order_detail_id', 'supplier_id', 'bahan_baku_supplier_id'], 'order_suppliers_unique');
        });

        // =====================================================================
        // ORDER WINNERS
        // =====================================================================

        Schema::create('order_winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_detail_id')
                ->constrained('order_details')
                ->cascadeOnDelete();
            $table->foreignId('order_supplier_id')
                ->constrained('order_suppliers')
                ->cascadeOnDelete();
            $table->foreignId('supplier_id')
                ->constrained('suppliers');
            $table->foreignId('bahan_baku_supplier_id')
                ->constrained('bahan_baku_supplier');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('allocated_quantity', 15, 2);
            $table->decimal('shipped_quantity', 15, 2)->default(0);
            $table->decimal('margin_percentage', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['order_detail_id', 'order_supplier_id'], 'order_winners_unique');
        });

        // =====================================================================
        // FORECASTS
        // =====================================================================

        Schema::create('forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')
                ->constrained('orders')
                ->cascadeOnDelete();
            $table->foreignId('purchasing_id')->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('no_forecast')->unique();
            $table->date('tanggal_forecast');
            $table->string('hari_kirim_forecast');
            $table->decimal('total_qty_forecast', 15, 2)->default(0);
            $table->decimal('total_harga_forecast', 15, 2)->default(0);
            $table->enum('status', ['pending', 'sukses', 'gagal'])->default('pending');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // =====================================================================
        // FORECAST DETAILS
        // =====================================================================

        Schema::create('forecast_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forecast_id')
                ->constrained('forecasts')
                ->cascadeOnDelete();
            $table->foreignId('purchase_order_bahan_baku_id')
                ->constrained('order_details')
                ->cascadeOnDelete();
            $table->foreignId('bahan_baku_supplier_id')
                ->constrained('bahan_baku_supplier');
            $table->decimal('qty_forecast', 15, 2);
            $table->decimal('harga_satuan_forecast', 15, 2);
            $table->decimal('total_harga_forecast', 15, 2);
            $table->decimal('harga_satuan_po', 15, 2)->nullable();
            $table->decimal('total_harga_po', 15, 2)->nullable();
            $table->text('catatan_detail')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['forecast_id', 'bahan_baku_supplier_id']);
        });

        // =====================================================================
        // PENGIRIMAN
        // =====================================================================

        Schema::create('pengiriman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')
                ->constrained('orders')
                ->cascadeOnDelete();
            $table->foreignId('purchasing_id')->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('forecast_id')
                ->constrained('forecasts')
                ->cascadeOnDelete();
            $table->string('no_pengiriman')->nullable();
            $table->date('tanggal_kirim')->nullable();
            $table->string('hari_kirim')->nullable();
            $table->decimal('total_qty_kirim', 15, 2)->nullable()->default(0);
            $table->decimal('total_harga_kirim', 15, 2)->nullable()->default(0);
            $table->string('bukti_foto_bongkar')->nullable();
            $table->timestamp('bukti_foto_bongkar_uploaded_at')->nullable();
            $table->string('foto_tanda_terima')->nullable();
            $table->timestamp('foto_tanda_terima_uploaded_at')->nullable();
            $table->enum('status', ['pending', 'menunggu_verifikasi', 'berhasil', 'gagal'])->default('pending');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('rating')->nullable()->comment('Rating pengiriman (1-5 bintang)');
            $table->text('ulasan')->nullable()->comment('Ulasan/review pengiriman');
        });

        // =====================================================================
        // PENGIRIMAN DETAILS
        // =====================================================================

        Schema::create('pengiriman_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengiriman_id')
                ->constrained('pengiriman')
                ->cascadeOnDelete();
            $table->foreignId('purchase_order_bahan_baku_id')
                ->constrained('order_details')
                ->cascadeOnDelete();
            $table->foreignId('bahan_baku_supplier_id')
                ->constrained('bahan_baku_supplier');
            $table->decimal('qty_kirim', 15, 2)->nullable();
            $table->decimal('harga_satuan', 15, 2)->nullable();
            $table->decimal('total_harga', 15, 2)->nullable();
            $table->text('catatan_detail')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['pengiriman_id', 'bahan_baku_supplier_id']);
        });

        // =====================================================================
        // SUPPLIER EVALUATIONS
        // =====================================================================

        Schema::create('supplier_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengiriman_id')
                ->constrained('pengiriman')
                ->cascadeOnDelete();
            $table->decimal('overall_rating', 3, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('supplier_evaluation_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_evaluation_id')
                ->constrained('supplier_evaluations')
                ->cascadeOnDelete();
            $table->foreignId('supplier_id')
                ->constrained('suppliers');
            $table->foreignId('bahan_baku_supplier_id')
                ->constrained('bahan_baku_supplier');
            $table->integer('quality_rating')->default(0);
            $table->integer('timeliness_rating')->default(0);
            $table->integer('quantity_rating')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // =====================================================================
        // INVOICE PENAGIHAN
        // =====================================================================

        Schema::create('invoice_penagihan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengiriman_id')->unique()
                ->constrained('pengiriman')
                ->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            $table->date('due_date');
            $table->string('customer_name');
            $table->text('customer_address');
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->json('items');
            $table->enum('refraksi_type', ['qty', 'rupiah'])->nullable();
            $table->decimal('refraksi_value', 15, 2)->default(0);
            $table->decimal('refraksi_amount', 15, 2)->default(0);
            $table->decimal('qty_before_refraksi', 15, 2)->nullable();
            $table->decimal('qty_after_refraksi', 15, 2)->nullable();
            $table->decimal('amount_before_refraksi', 15, 2)->nullable();
            $table->decimal('amount_after_refraksi', 15, 2)->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(11);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid', 'overdue'])->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('created_by')->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // =====================================================================
        // PEMBAYARAN
        // =====================================================================

        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengiriman_id')
                ->constrained('pengiriman')
                ->cascadeOnDelete();
            $table->decimal('jumlah_pembayaran', 15, 2);
            $table->date('tanggal_pembayaran');
            $table->string('metode_pembayaran');
            $table->enum('status', ['pending', 'lunas', 'gagal'])->default('pending');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // =====================================================================
        // CATATAN PIUTANGS (SUPPLIER)
        // =====================================================================

        Schema::create('catatan_piutangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')
                ->constrained('suppliers');
            $table->date('tanggal_piutang');
            $table->date('tanggal_jatuh_tempo')->nullable();
            $table->decimal('jumlah_piutang', 15, 2);
            $table->decimal('jumlah_dibayar', 15, 2)->default(0);
            $table->decimal('sisa_piutang', 15, 2);
            $table->enum('status', ['belum_lunas', 'cicilan', 'lunas'])->default('belum_lunas');
            $table->text('keterangan')->nullable();
            $table->string('bukti_transaksi')->nullable();
            $table->foreignId('created_by')->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('updated_by')->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // =====================================================================
        // CATATAN PIUTANG PABRIKS (CLIENT)
        // =====================================================================

        Schema::create('catatan_piutang_pabriks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klien_id')
                ->constrained('kliens');
            $table->string('no_invoice')->unique();
            $table->date('tanggal_invoice');
            $table->date('tanggal_jatuh_tempo');
            $table->decimal('jumlah_piutang', 15, 2);
            $table->decimal('jumlah_dibayar', 15, 2)->default(0);
            $table->decimal('sisa_piutang', 15, 2);
            $table->enum('status', ['belum_jatuh_tempo', 'jatuh_tempo', 'terlambat', 'cicilan', 'lunas'])->default('belum_jatuh_tempo');
            $table->integer('hari_keterlambatan')->default(0);
            $table->text('keterangan')->nullable();
            $table->string('bukti_transaksi')->nullable();
            $table->foreignId('created_by')->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('updated_by')->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // =====================================================================
        // PEMBAYARAN PIUTANG (SUPPLIER)
        // =====================================================================

        Schema::create('pembayaran_piutang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catatan_piutang_id')
                ->constrained('catatan_piutangs');
            $table->string('no_pembayaran')->unique();
            $table->date('tanggal_bayar');
            $table->decimal('jumlah_bayar', 15, 2);
            $table->enum('metode_pembayaran', ['tunai', 'transfer', 'cek', 'giro', 'potong_pembayaran'])->default('transfer');
            $table->string('bukti_pembayaran')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // =====================================================================
        // PEMBAYARAN PIUTANG PABRIKS (CLIENT)
        // =====================================================================

        Schema::create('pembayaran_piutang_pabriks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_penagihan_id')
                ->constrained('invoice_penagihan');
            $table->string('no_pembayaran')->unique();
            $table->date('tanggal_bayar');
            $table->decimal('jumlah_bayar', 15, 2);
            $table->enum('metode_pembayaran', ['tunai', 'transfer', 'cek', 'giro']);
            $table->text('catatan')->nullable();
            $table->string('bukti_pembayaran')->nullable();
            $table->foreignId('created_by')->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // =====================================================================
        // APPROVAL PEMBAYARAN
        // =====================================================================

        Schema::create('approval_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengiriman_id')->unique()
                ->constrained('pengiriman')
                ->cascadeOnDelete();
            $table->foreignId('staff_id')->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('staff_approved_at')->nullable();
            $table->foreignId('manager_id')->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('manager_approved_at')->nullable();
            $table->foreignId('superadmin_id')->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('superadmin_approved_at')->nullable();
            $table->enum('status', ['pending', 'staff_approved', 'manager_approved', 'completed'])->default('pending');
            $table->foreignId('catatan_piutang_id')->nullable()
                ->constrained('catatan_piutangs')
                ->nullOnDelete();
            $table->decimal('piutang_amount', 15, 2)->default(0);
            $table->text('piutang_notes')->nullable();
            $table->string('bukti_pembayaran')->nullable();
            $table->enum('refraksi_type', ['qty', 'rupiah'])->nullable();
            $table->decimal('refraksi_value', 15, 2)->default(0);
            $table->decimal('refraksi_amount', 15, 2)->default(0);
            $table->decimal('qty_before_refraksi', 15, 2)->nullable();
            $table->decimal('qty_after_refraksi', 15, 2)->nullable();
            $table->decimal('amount_before_refraksi', 15, 2)->nullable();
            $table->decimal('amount_after_refraksi', 15, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // =====================================================================
        // APPROVAL PENAGIHAN
        // =====================================================================

        Schema::create('approval_penagihan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->unique()
                ->constrained('invoice_penagihan')
                ->cascadeOnDelete();
            $table->foreignId('pengiriman_id')
                ->constrained('pengiriman')
                ->cascadeOnDelete();
            $table->foreignId('staff_id')->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('staff_approved_at')->nullable();
            $table->foreignId('manager_id')->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('manager_approved_at')->nullable();
            $table->foreignId('superadmin_id')->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('superadmin_approved_at')->nullable();
            $table->enum('status', ['pending', 'staff_approved', 'manager_approved', 'completed'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });

        // =====================================================================
        // APPROVAL HISTORY
        // =====================================================================

        Schema::create('approval_history', function (Blueprint $table) {
            $table->id();
            $table->enum('approval_type', ['pembayaran', 'penagihan']);
            $table->unsignedBigInteger('approval_id');
            $table->foreignId('pengiriman_id')
                ->constrained('pengiriman')
                ->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()
                ->constrained('invoice_penagihan')
                ->cascadeOnDelete();
            $table->enum('role', ['staff', 'manager_keuangan', 'direktur', 'superadmin']);
            $table->foreignId('user_id')->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('action')->default('approved');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['approval_type', 'approval_id']);
        });

        // =====================================================================
        // PENAWARAN
        // =====================================================================

        Schema::create('penawaran', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_penawaran', 50)->unique()
                ->comment('Format: PNW-YYYY-XXXX');
            $table->foreignId('klien_id')
                ->constrained('kliens');
            $table->date('tanggal_penawaran');
            $table->date('tanggal_berlaku_sampai');
            $table->enum('status', ['draft', 'menunggu_verifikasi', 'disetujui', 'ditolak', 'expired'])->default('draft');
            $table->decimal('total_revenue', 15, 2)->default(0)
                ->comment('Total client price');
            $table->decimal('total_cost', 15, 2)->default(0)
                ->comment('Total supplier cost');
            $table->decimal('total_profit', 15, 2)->default(0)
                ->comment('Total profit');
            $table->decimal('margin_percentage', 5, 2)->default(0)
                ->comment('Overall margin %');
            $table->foreignId('created_by')
                ->constrained('users');
            $table->foreignId('verified_by')->nullable()
                ->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->text('catatan')->nullable()
                ->comment('General notes');
            $table->text('alasan_penolakan')->nullable()
                ->comment('Rejection reason if status=ditolak');
            $table->timestamps();
            $table->softDeletes();

            $table->index('nomor_penawaran');
            $table->index('klien_id');
            $table->index('status');
            $table->index('tanggal_penawaran');
            $table->index('created_by');
        });

        // =====================================================================
        // PENAWARAN DETAIL
        // =====================================================================

        Schema::create('penawaran_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penawaran_id')
                ->constrained('penawaran')
                ->cascadeOnDelete();
            $table->foreignId('bahan_baku_klien_id')
                ->constrained('bahan_baku_klien');
            $table->foreignId('supplier_id')->nullable()
                ->constrained('suppliers')
                ->nullOnDelete();
            $table->foreignId('bahan_baku_supplier_id')->nullable()
                ->constrained('bahan_baku_supplier')
                ->nullOnDelete();
            $table->string('nama_material')
                ->comment('Material name at time of quotation');
            $table->string('satuan', 50)
                ->comment('Unit (kg, pcs, m, etc.)');
            $table->decimal('quantity', 10, 2);
            $table->decimal('harga_klien', 15, 2)
                ->comment('Client price per unit');
            $table->decimal('harga_supplier', 15, 2)->nullable();
            $table->boolean('is_custom_price')->default(false)
                ->comment('If custom client price was used');
            $table->decimal('subtotal_revenue', 15, 2)
                ->comment('quantity * harga_klien');
            $table->decimal('subtotal_cost', 15, 2)->nullable();
            $table->decimal('subtotal_profit', 15, 2)->nullable();
            $table->decimal('margin_percentage', 5, 2)->nullable();
            $table->text('notes')->nullable()
                ->comment('Item-specific notes');
            $table->timestamps();

            $table->index('penawaran_id');
            $table->index('bahan_baku_klien_id');
            $table->index('supplier_id');
            $table->index('bahan_baku_supplier_id');
        });

        // =====================================================================
        // PENAWARAN ALTERNATIVE SUPPLIERS
        // =====================================================================

        Schema::create('penawaran_alternative_suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penawaran_detail_id')
                ->constrained('penawaran_detail')
                ->cascadeOnDelete();
            $table->foreignId('supplier_id')
                ->constrained('suppliers');
            $table->foreignId('bahan_baku_supplier_id')
                ->constrained('bahan_baku_supplier');
            $table->decimal('harga_supplier', 15, 2)
                ->comment('Alternative supplier price at time of quotation');
            $table->text('notes')->nullable()
                ->comment('Why this alternative was not chosen');
            $table->timestamps();

            $table->unique(['penawaran_detail_id', 'supplier_id'], 'unique_detail_supplier');
            $table->index('penawaran_detail_id');
            $table->index('supplier_id');
        });

        // =====================================================================
        // TARGET OMSET
        // =====================================================================

        Schema::create('target_omset', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('month');
            $table->decimal('target_amount', 15, 2);
            $table->decimal('achieved_amount', 15, 2)->default(0);
            $table->decimal('achievement_percentage', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('updated_by')->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['year', 'month']);
        });

        Schema::create('target_omset_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('target_omset_id')
                ->constrained('target_omset')
                ->cascadeOnDelete();
            $table->date('snapshot_date');
            $table->decimal('achieved_amount', 15, 2);
            $table->decimal('achievement_percentage', 5, 2);
            $table->timestamps();

            $table->index(['target_omset_id', 'snapshot_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tables in reverse order of creation to respect foreign key constraints
        Schema::dropIfExists('target_omset_snapshots');
        Schema::dropIfExists('target_omset');
        Schema::dropIfExists('penawaran_alternative_suppliers');
        Schema::dropIfExists('penawaran_detail');
        Schema::dropIfExists('penawaran');
        Schema::dropIfExists('approval_history');
        Schema::dropIfExists('approval_penagihan');
        Schema::dropIfExists('approval_pembayaran');
        Schema::dropIfExists('pembayaran_piutang_pabriks');
        Schema::dropIfExists('pembayaran_piutang');
        Schema::dropIfExists('catatan_piutang_pabriks');
        Schema::dropIfExists('catatan_piutangs');
        Schema::dropIfExists('pembayaran');
        Schema::dropIfExists('invoice_penagihan');
        Schema::dropIfExists('supplier_evaluation_details');
        Schema::dropIfExists('supplier_evaluations');
        Schema::dropIfExists('pengiriman_details');
        Schema::dropIfExists('pengiriman');
        Schema::dropIfExists('forecast_details');
        Schema::dropIfExists('forecasts');
        Schema::dropIfExists('order_winners');
        Schema::dropIfExists('order_suppliers');
        Schema::dropIfExists('order_details');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('riwayat_harga_bahan_baku');
        Schema::dropIfExists('riwayat_harga_klien');
        Schema::dropIfExists('bahan_baku_supplier');
        Schema::dropIfExists('bahan_baku_klien');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('kliens');
        Schema::dropIfExists('kontak_klien');
        Schema::dropIfExists('company_settings');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
