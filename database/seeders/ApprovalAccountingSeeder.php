<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Pengiriman;
use App\Models\PengirimanDetail;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderBahanBaku;
use App\Models\Forecast;
use App\Models\ForecastDetail;
use App\Models\BahanBakuSupplier;
use App\Models\BahanBakuKlien;
use App\Models\ApprovalPembayaran;
use App\Models\ApprovalPenagihan;
use App\Models\InvoicePenagihan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ApprovalAccountingSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('🚀 Starting Approval Accounting Seeder...');

            // Get users
            $staffAccounting = User::where('role', 'staff_accounting')->first();
            $managerAccounting = User::where('role', 'manager_accounting')->first();
            $direktur = User::where('role', 'direktur')->first();
            $staffPurchasing = User::where('role', 'staff_purchasing')->first();

            // Create users if not exist
            if (!$staffAccounting) {
                $this->command->warn('⚠️  Staff Accounting tidak ditemukan, membuat user baru...');
                $staffAccounting = User::create([
                    'nama' => 'Staff Accounting',
                    'username' => 'staff_accounting',
                    'email' => 'staff.accounting@kmp.com',
                    'role' => 'staff_accounting',
                    'password' => bcrypt('password123'),
                    'status' => 'aktif'
                ]);
            }

            if (!$managerAccounting) {
                $this->command->warn('⚠️  Manager Accounting tidak ditemukan, membuat user baru...');
                $managerAccounting = User::create([
                    'nama' => 'Manager Accounting',
                    'username' => 'manager_accounting',
                    'email' => 'manager.accounting@kmp.com',
                    'role' => 'manager_accounting',
                    'password' => bcrypt('password123'),
                    'status' => 'aktif'
                ]);
            }

            if (!$direktur) {
                $this->command->warn('⚠️  Direktur tidak ditemukan, membuat user baru...');
                $direktur = User::create([
                    'nama' => 'Direktur',
                    'username' => 'direktur',
                    'email' => 'direktur@kmp.com',
                    'role' => 'direktur',
                    'password' => bcrypt('password123'),
                    'status' => 'aktif'
                ]);
            }

            if (!$staffPurchasing) {
                $this->command->warn('⚠️  Staff Purchasing tidak ditemukan, membuat user baru...');
                $staffPurchasing = User::create([
                    'nama' => 'Staff Purchasing',
                    'username' => 'staff_purchasing',
                    'email' => 'staff.purchasing@kmp.com',
                    'role' => 'staff_purchasing',
                    'password' => bcrypt('password123'),
                    'status' => 'aktif'
                ]);
            }

            // Get Purchase Order
            $purchaseOrder = PurchaseOrder::with('klien')->first();

            if (!$purchaseOrder) {
                $this->command->error('❌ Error: Tidak ada data PurchaseOrder!');
                $this->command->info('💡 Jalankan PurchaseOrderSeeder terlebih dahulu');
                return;
            }

            // Get Bahan Baku Supplier & PO Bahan Baku yang sudah ada
            $poBahanBakus = PurchaseOrderBahanBaku::where('purchase_order_id', $purchaseOrder->id)
                ->with(['bahanBakuKlien'])
                ->take(3)
                ->get();

            if ($poBahanBakus->isEmpty()) {
                $this->command->error('❌ Error: Tidak ada data PO Bahan Baku!');
                $this->command->info('💡 Jalankan PurchaseOrderBahanBakuSeeder terlebih dahulu');
                return;
            }

            // Get bahan baku suppliers untuk detail pengiriman
            $bahanBakuSuppliers = BahanBakuSupplier::with('supplier')->take(3)->get();

            if ($bahanBakuSuppliers->isEmpty()) {
                $this->command->error('❌ Error: Tidak ada data Bahan Baku Supplier!');
                return;
            }

            // Create Forecast
            $this->command->info('📝 Creating forecast data...');
            $forecast = Forecast::create([
                'purchase_order_id' => $purchaseOrder->id,
                'purchasing_id' => $staffPurchasing->id ?? $staffAccounting->id,
                'no_forecast' => 'FC-ACC-' . now()->format('Ymd-His'),
                'tanggal_forecast' => Carbon::now()->subDays(15),
                'hari_kirim_forecast' => Carbon::now()->subDays(15)->locale('id')->isoFormat('dddd'),
                'total_qty_forecast' => 5000,
                'total_harga_forecast' => 85000000,
                'catatan' => 'Forecast untuk testing approval accounting dengan detail items',
            ]);

            $this->command->info('📦 Creating pengiriman with details...');

            // ========================================
            // 1. APPROVAL PEMBAYARAN - PENDING
            // ========================================
            $pengiriman1 = Pengiriman::create([
                'purchase_order_id' => $purchaseOrder->id,
                'purchasing_id' => $staffPurchasing->id ?? $staffAccounting->id,
                'forecast_id' => $forecast->id,
                'no_pengiriman' => 'SHIP-' . now()->format('Ymd') . '-001',
                'tanggal_kirim' => Carbon::now()->subDays(7),
                'hari_kirim' => Carbon::now()->subDays(7)->locale('id')->isoFormat('dddd'),
                'total_qty_kirim' => 0,
                'total_harga_kirim' => 0,
                'status' => 'menunggu_verifikasi',
                'catatan' => 'Pengiriman untuk approval pembayaran - Status: Pending',
            ]);

            // Create details untuk pengiriman 1
            $totalQty1 = 0;
            $totalHarga1 = 0;
            foreach ($poBahanBakus as $index => $poBahanBaku) {
                $bahanBakuSupplier = $bahanBakuSuppliers->get($index) ?? $bahanBakuSuppliers->first();
                $qty = 300 + ($index * 100);
                $harga = $poBahanBaku->harga_satuan;
                $total = $qty * $harga;

                PengirimanDetail::create([
                    'pengiriman_id' => $pengiriman1->id,
                    'purchase_order_bahan_baku_id' => $poBahanBaku->id,
                    'bahan_baku_supplier_id' => $bahanBakuSupplier->id,
                    'qty_kirim' => $qty,
                    'harga_satuan' => $harga,
                    'total_harga' => $total,
                    'catatan_detail' => 'Item ' . ($index + 1) . ' - ' . ($bahanBakuSupplier->nama ?? 'Bahan Baku'),
                ]);

                $totalQty1 += $qty;
                $totalHarga1 += $total;
            }

            $pengiriman1->update([
                'total_qty_kirim' => $totalQty1,
                'total_harga_kirim' => $totalHarga1,
            ]);

            // Approval Pembayaran (created by observer, update to add refraksi)
            $approval1 = ApprovalPembayaran::where('pengiriman_id', $pengiriman1->id)->first();
            if ($approval1) {
                $approval1->update([
                    'status' => 'pending',
                    'refraksi_type' => 'qty',
                    'refraksi_value' => 2.5,
                    'qty_before_refraksi' => $totalQty1,
                    'qty_after_refraksi' => $totalQty1 - ($totalQty1 * 0.025),
                    'amount_before_refraksi' => $totalHarga1,
                    'refraksi_amount' => $totalHarga1 * 0.025,
                    'amount_after_refraksi' => $totalHarga1 - ($totalHarga1 * 0.025),
                ]);
            }

            // ========================================
            // 2. APPROVAL PEMBAYARAN - COMPLETED (untuk generate penagihan)
            // ========================================
            $pengiriman2 = Pengiriman::create([
                'purchase_order_id' => $purchaseOrder->id,
                'purchasing_id' => $staffPurchasing->id ?? $staffAccounting->id,
                'forecast_id' => $forecast->id,
                'no_pengiriman' => 'SHIP-' . now()->format('Ymd') . '-002',
                'tanggal_kirim' => Carbon::now()->subDays(5),
                'hari_kirim' => Carbon::now()->subDays(5)->locale('id')->isoFormat('dddd'),
                'total_qty_kirim' => 0,
                'total_harga_kirim' => 0,
                'status' => 'menunggu_verifikasi',
                'catatan' => 'Pengiriman untuk approval pembayaran - Status: Completed (siap penagihan)',
            ]);

            // Create details untuk pengiriman 2
            $totalQty2 = 0;
            $totalHarga2 = 0;
            foreach ($poBahanBakus as $index => $poBahanBaku) {
                $bahanBakuSupplier = $bahanBakuSuppliers->get($index) ?? $bahanBakuSuppliers->first();
                $qty = 400 + ($index * 150);
                $harga = $poBahanBaku->harga_satuan;
                $total = $qty * $harga;

                PengirimanDetail::create([
                    'pengiriman_id' => $pengiriman2->id,
                    'purchase_order_bahan_baku_id' => $poBahanBaku->id,
                    'bahan_baku_supplier_id' => $bahanBakuSupplier->id,
                    'qty_kirim' => $qty,
                    'harga_satuan' => $harga,
                    'total_harga' => $total,
                    'catatan_detail' => 'Item ' . ($index + 1) . ' - ' . ($bahanBakuSupplier->nama ?? 'Bahan Baku'),
                ]);

                $totalQty2 += $qty;
                $totalHarga2 += $total;
            }

            $pengiriman2->update([
                'total_qty_kirim' => $totalQty2,
                'total_harga_kirim' => $totalHarga2,
                'status' => 'berhasil',
            ]);

            // Approval Pembayaran - COMPLETED
            $approval2 = ApprovalPembayaran::where('pengiriman_id', $pengiriman2->id)->first();
            if ($approval2) {
                $refraksiAmount = $totalHarga2 * 0.03;
                $approval2->update([
                    'status' => 'completed',
                    'staff_id' => $staffAccounting->id,
                    'staff_approved_at' => Carbon::now()->subDays(4),
                    'manager_id' => $managerAccounting->id,
                    'manager_approved_at' => Carbon::now()->subDays(4),
                    'refraksi_type' => 'qty',
                    'refraksi_value' => 3.0,
                    'qty_before_refraksi' => $totalQty2,
                    'qty_after_refraksi' => $totalQty2 - ($totalQty2 * 0.03),
                    'amount_before_refraksi' => $totalHarga2,
                    'refraksi_amount' => $refraksiAmount,
                    'amount_after_refraksi' => $totalHarga2 - $refraksiAmount,
                ]);

                // Create Invoice Penagihan
                $klien = $purchaseOrder->klien;
                $invoiceNumber = 'INV-' . now()->format('Ym') . '-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);

                $items = [];
                foreach ($pengiriman2->pengirimanDetails as $detail) {
                    $items[] = [
                        'description' => $detail->bahanBakuSupplier->nama ?? 'Bahan Baku',
                        'quantity' => $detail->qty_kirim,
                        'unit_price' => $detail->harga_satuan,
                        'total' => $detail->total_harga,
                    ];
                }

                $subtotal = $totalHarga2 - $refraksiAmount;
                $taxPercentage = 11;
                $taxAmount = $subtotal * ($taxPercentage / 100);
                $totalAmount = $subtotal + $taxAmount;

                $invoice = InvoicePenagihan::create([
                    'pengiriman_id' => $pengiriman2->id,
                    'invoice_number' => $invoiceNumber,
                    'invoice_date' => now(),
                    'due_date' => now()->addDays(30),
                    'customer_name' => $klien->nama ?? 'Customer',
                    'customer_address' => $klien->cabang ?? '-',
                    'customer_phone' => $klien->no_hp ?? null,
                    'customer_email' => null,
                    'items' => $items,
                    'subtotal' => $subtotal,
                    'tax_percentage' => $taxPercentage,
                    'tax_amount' => $taxAmount,
                    'discount_amount' => 0,
                    'total_amount' => $totalAmount,
                    'refraksi_type' => 'qty',
                    'refraksi_value' => 3.0,
                    'refraksi_amount' => $refraksiAmount,
                    'qty_before_refraksi' => $totalQty2,
                    'qty_after_refraksi' => $totalQty2 - ($totalQty2 * 0.03),
                    'amount_before_refraksi' => $totalHarga2,
                    'amount_after_refraksi' => $subtotal,
                    'status' => 'pending',
                    'notes' => 'Invoice dibuat otomatis dari approval pembayaran',
                    'created_by' => $staffAccounting->id,
                ]);

                // Create Approval Penagihan
                ApprovalPenagihan::create([
                    'pengiriman_id' => $pengiriman2->id,
                    'invoice_id' => $invoice->id,
                    'status' => 'pending',
                ]);
            }

            // ========================================
            // 3. APPROVAL PEMBAYARAN - TANPA REFRAKSI
            // ========================================
            $pengiriman3 = Pengiriman::create([
                'purchase_order_id' => $purchaseOrder->id,
                'purchasing_id' => $staffPurchasing->id ?? $staffAccounting->id,
                'forecast_id' => $forecast->id,
                'no_pengiriman' => 'SHIP-' . now()->format('Ymd') . '-003',
                'tanggal_kirim' => Carbon::now()->subDays(3),
                'hari_kirim' => Carbon::now()->subDays(3)->locale('id')->isoFormat('dddd'),
                'total_qty_kirim' => 0,
                'total_harga_kirim' => 0,
                'status' => 'menunggu_verifikasi',
                'catatan' => 'Pengiriman tanpa refraksi - untuk testing',
            ]);

            // Create details untuk pengiriman 3
            $totalQty3 = 0;
            $totalHarga3 = 0;
            foreach ($poBahanBakus->take(2) as $index => $poBahanBaku) {
                $bahanBakuSupplier = $bahanBakuSuppliers->get($index) ?? $bahanBakuSuppliers->first();
                $qty = 500 + ($index * 200);
                $harga = $poBahanBaku->harga_satuan;
                $total = $qty * $harga;

                PengirimanDetail::create([
                    'pengiriman_id' => $pengiriman3->id,
                    'purchase_order_bahan_baku_id' => $poBahanBaku->id,
                    'bahan_baku_supplier_id' => $bahanBakuSupplier->id,
                    'qty_kirim' => $qty,
                    'harga_satuan' => $harga,
                    'total_harga' => $total,
                    'catatan_detail' => 'Item ' . ($index + 1) . ' - ' . ($bahanBakuSupplier->nama ?? 'Bahan Baku'),
                ]);

                $totalQty3 += $qty;
                $totalHarga3 += $total;
            }

            $pengiriman3->update([
                'total_qty_kirim' => $totalQty3,
                'total_harga_kirim' => $totalHarga3,
            ]);

            // Approval Pembayaran - TANPA REFRAKSI
            $approval3 = ApprovalPembayaran::where('pengiriman_id', $pengiriman3->id)->first();
            if ($approval3) {
                $approval3->update([
                    'status' => 'pending',
                    'refraksi_type' => null,
                    'refraksi_value' => 0,
                    'qty_before_refraksi' => $totalQty3,
                    'qty_after_refraksi' => $totalQty3,
                    'amount_before_refraksi' => $totalHarga3,
                    'refraksi_amount' => 0,
                    'amount_after_refraksi' => $totalHarga3,
                ]);
            }

            // ========================================
            // 4. APPROVAL PENAGIHAN - COMPLETED
            // ========================================
            $pengiriman4 = Pengiriman::create([
                'purchase_order_id' => $purchaseOrder->id,
                'purchasing_id' => $staffPurchasing->id ?? $staffAccounting->id,
                'forecast_id' => $forecast->id,
                'no_pengiriman' => 'SHIP-' . now()->format('Ymd') . '-004',
                'tanggal_kirim' => Carbon::now()->subDays(10),
                'hari_kirim' => Carbon::now()->subDays(10)->locale('id')->isoFormat('dddd'),
                'total_qty_kirim' => 0,
                'total_harga_kirim' => 0,
                'status' => 'berhasil',
                'catatan' => 'Pengiriman completed - Invoice sudah dibuat',
            ]);

            // Create details untuk pengiriman 4
            $totalQty4 = 0;
            $totalHarga4 = 0;
            foreach ($poBahanBakus as $index => $poBahanBaku) {
                $bahanBakuSupplier = $bahanBakuSuppliers->get($index) ?? $bahanBakuSuppliers->first();
                $qty = 350 + ($index * 120);
                $harga = $poBahanBaku->harga_satuan;
                $total = $qty * $harga;

                PengirimanDetail::create([
                    'pengiriman_id' => $pengiriman4->id,
                    'purchase_order_bahan_baku_id' => $poBahanBaku->id,
                    'bahan_baku_supplier_id' => $bahanBakuSupplier->id,
                    'qty_kirim' => $qty,
                    'harga_satuan' => $harga,
                    'total_harga' => $total,
                    'catatan_detail' => 'Item ' . ($index + 1) . ' - ' . ($bahanBakuSupplier->nama ?? 'Bahan Baku'),
                ]);

                $totalQty4 += $qty;
                $totalHarga4 += $total;
            }

            $pengiriman4->update([
                'total_qty_kirim' => $totalQty4,
                'total_harga_kirim' => $totalHarga4,
            ]);

            // Create Approval Pembayaran - COMPLETED
            $refraksiAmount4 = 50000; // Manual refraksi
            $approval4 = ApprovalPembayaran::create([
                'pengiriman_id' => $pengiriman4->id,
                'status' => 'completed',
                'staff_id' => $staffAccounting->id,
                'staff_approved_at' => Carbon::now()->subDays(9),
                'manager_id' => $managerAccounting->id,
                'manager_approved_at' => Carbon::now()->subDays(9),
                'refraksi_type' => 'rupiah',
                'refraksi_value' => 35, // Rp 35/kg
                'qty_before_refraksi' => $totalQty4,
                'qty_after_refraksi' => $totalQty4,
                'amount_before_refraksi' => $totalHarga4,
                'refraksi_amount' => $refraksiAmount4,
                'amount_after_refraksi' => $totalHarga4 - $refraksiAmount4,
            ]);

            // Create Invoice & Approval Penagihan - COMPLETED
            $klien = $purchaseOrder->klien;
            $invoiceNumber4 = 'INV-' . now()->format('Ym') . '-' . str_pad(rand(5000, 9999), 4, '0', STR_PAD_LEFT);

            $items4 = [];
            foreach ($pengiriman4->pengirimanDetails as $detail) {
                $items4[] = [
                    'description' => $detail->bahanBakuSupplier->nama ?? 'Bahan Baku',
                    'quantity' => $detail->qty_kirim,
                    'unit_price' => $detail->harga_satuan,
                    'total' => $detail->total_harga,
                ];
            }

            $subtotal4 = $totalHarga4 - $refraksiAmount4;
            $taxPercentage4 = 11;
            $taxAmount4 = $subtotal4 * ($taxPercentage4 / 100);
            $totalAmount4 = $subtotal4 + $taxAmount4;

            $invoice4 = InvoicePenagihan::create([
                'pengiriman_id' => $pengiriman4->id,
                'invoice_number' => $invoiceNumber4,
                'invoice_date' => now()->subDays(8),
                'due_date' => now()->addDays(22),
                'customer_name' => $klien->nama ?? 'Customer',
                'customer_address' => $klien->cabang ?? '-',
                'customer_phone' => $klien->no_hp ?? null,
                'customer_email' => null,
                'items' => $items4,
                'subtotal' => $subtotal4,
                'tax_percentage' => $taxPercentage4,
                'tax_amount' => $taxAmount4,
                'discount_amount' => 0,
                'total_amount' => $totalAmount4,
                'refraksi_type' => 'rupiah',
                'refraksi_value' => 35,
                'refraksi_amount' => $refraksiAmount4,
                'qty_before_refraksi' => $totalQty4,
                'qty_after_refraksi' => $totalQty4,
                'amount_before_refraksi' => $totalHarga4,
                'amount_after_refraksi' => $subtotal4,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'notes' => 'Invoice completed dengan refraksi manual',
                'created_by' => $staffAccounting->id,
            ]);

            ApprovalPenagihan::create([
                'pengiriman_id' => $pengiriman4->id,
                'invoice_id' => $invoice4->id,
                'status' => 'completed',
                'staff_id' => $staffAccounting->id,
                'staff_approved_at' => Carbon::now()->subDays(8),
                'manager_id' => $managerAccounting->id,
                'manager_approved_at' => Carbon::now()->subDays(8),
            ]);

            $this->command->info('');
            $this->command->info('✅ Approval Accounting Seeder berhasil dijalankan!');
            $this->command->info('');
            $this->command->line('📊 Summary Data yang Dibuat:');
            $this->command->line('═══════════════════════════════════════════════════════');
            $this->command->info("📦 Pengiriman 1: {$pengiriman1->no_pengiriman}");
            $this->command->info("   - Status: menunggu_verifikasi");
            $this->command->info("   - Approval Pembayaran: PENDING");
            $this->command->info("   - Total Item: " . $pengiriman1->pengirimanDetails->count());
            $this->command->info("   - Total Qty: " . number_format($totalQty1, 2) . " kg");
            $this->command->info("   - Total Harga: Rp " . number_format($totalHarga1, 0, ',', '.'));
            $this->command->info("   - Refraksi: Qty 2.5%");
            $this->command->line('');

            $this->command->info("📦 Pengiriman 2: {$pengiriman2->no_pengiriman}");
            $this->command->info("   - Status: berhasil");
            $this->command->info("   - Approval Pembayaran: COMPLETED");
            $this->command->info("   - Invoice: {$invoiceNumber}");
            $this->command->info("   - Approval Penagihan: PENDING");
            $this->command->info("   - Total Item: " . $pengiriman2->pengirimanDetails->count());
            $this->command->info("   - Total Qty: " . number_format($totalQty2, 2) . " kg");
            $this->command->info("   - Total Harga: Rp " . number_format($totalHarga2, 0, ',', '.'));
            $this->command->info("   - Refraksi: Qty 3.0%");
            $this->command->line('');

            $this->command->info("📦 Pengiriman 3: {$pengiriman3->no_pengiriman}");
            $this->command->info("   - Status: menunggu_verifikasi");
            $this->command->info("   - Approval Pembayaran: PENDING");
            $this->command->info("   - Total Item: " . $pengiriman3->pengirimanDetails->count());
            $this->command->info("   - Total Qty: " . number_format($totalQty3, 2) . " kg");
            $this->command->info("   - Total Harga: Rp " . number_format($totalHarga3, 0, ',', '.'));
            $this->command->info("   - Refraksi: TANPA REFRAKSI");
            $this->command->line('');

            $this->command->info("📦 Pengiriman 4: {$pengiriman4->no_pengiriman}");
            $this->command->info("   - Status: berhasil");
            $this->command->info("   - Approval Pembayaran: COMPLETED");
            $this->command->info("   - Invoice: {$invoiceNumber4}");
            $this->command->info("   - Approval Penagihan: COMPLETED");
            $this->command->info("   - Total Item: " . $pengiriman4->pengirimanDetails->count());
            $this->command->info("   - Total Qty: " . number_format($totalQty4, 2) . " kg");
            $this->command->info("   - Total Harga: Rp " . number_format($totalHarga4, 0, ',', '.'));
            $this->command->info("   - Refraksi: Manual Rp " . number_format($refraksiAmount4, 0, ',', '.'));
            $this->command->line('═══════════════════════════════════════════════════════');
            $this->command->info('');
            $this->command->info('🎯 Testing Scenarios:');
            $this->command->info('   1. Approval Pembayaran Pending (dengan refraksi)');
            $this->command->info('   2. Approval Penagihan Pending (auto-generated)');
            $this->command->info('   3. Approval Pembayaran Pending (tanpa refraksi)');
            $this->command->info('   4. Approval Penagihan Completed (sudah approve)');
        });
    }
}
