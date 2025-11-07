<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Pengiriman;
use App\Models\PurchaseOrder;
use App\Models\Forecast;
use App\Models\ApprovalPembayaran;
use App\Models\ApprovalPenagihan;
use App\Models\InvoicePenagihan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PengirimanAccountingSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Get users
            $staffAccounting = User::where('role', 'staff_accounting')->first();
            $managerAccounting = User::where('role', 'manager_accounting')->first();
            $direktur = User::where('role', 'direktur')->first();
            $staffPurchasing = User::where('role', 'staff_purchasing')->first();

            // Ensure staff_accounting user exists
            if (!$staffAccounting) {
                $staffAccounting = User::create([
                    'nama' => 'Rina Staff Accounting',
                    'username' => 'staff_accounting',
                    'email' => 'rina.accounting@kmp.com',
                    'role' => 'staff_accounting',
                    'password' => bcrypt('password123'),
                    'status' => 'aktif'
                ]);
            }

            // Get or create test Purchase Order
            $purchaseOrder = PurchaseOrder::first();

            if (!$purchaseOrder) {
                $this->command->error('❌ Error: Tidak ada data PurchaseOrder!');
                $this->command->info('💡 Jalankan seeder lain terlebih dahulu:');
                $this->command->info('   php artisan db:seed');
                return;
            }

            // Create Forecast first (since it's required for Pengiriman)
            $this->command->info('📝 Creating forecast data...');
            $forecast = Forecast::create([
                'purchase_order_id' => $purchaseOrder->id,
                'purchasing_id' => $staffPurchasing->id ?? 1,
                'no_forecast' => 'FC-ACC-TEST-' . date('Ymd'),
                'tanggal_forecast' => Carbon::now()->subDays(10),
                'hari_kirim_forecast' => Carbon::now()->subDays(10)->locale('id')->isoFormat('dddd'),
                'total_qty_forecast' => 5000,
                'total_harga_forecast' => 110000000,
                'catatan' => 'Forecast untuk testing approval accounting',
            ]);

            // Create Pengiriman data with different statuses
            $pengirimanData = [
                // Status: menunggu_verifikasi (untuk Approval Pembayaran)
                [
                    'no_pengiriman' => 'SHIP-2024-001',
                    'status' => 'menunggu_verifikasi',
                    'tanggal_kirim' => Carbon::now()->subDays(5),
                    'total_harga_kirim' => 15000000,
                    'approval_status' => 'pending', // Belum ada yang approve
                ],
                [
                    'no_pengiriman' => 'SHIP-2024-002',
                    'status' => 'menunggu_verifikasi',
                    'tanggal_kirim' => Carbon::now()->subDays(4),
                    'total_harga_kirim' => 25000000,
                    'approval_status' => 'staff_approved', // Staff sudah approve
                ],
                [
                    'no_pengiriman' => 'SHIP-2024-003',
                    'status' => 'menunggu_verifikasi',
                    'tanggal_kirim' => Carbon::now()->subDays(3),
                    'total_harga_kirim' => 18000000,
                    'approval_status' => 'manager_approved', // Manager sudah approve
                ],

                // Status: berhasil (untuk Approval Penagihan)
                [
                    'no_pengiriman' => 'SHIP-2024-004',
                    'status' => 'berhasil',
                    'tanggal_kirim' => Carbon::now()->subDays(2),
                    'total_harga_kirim' => 30000000,
                    'approval_status' => 'completed', // Semua sudah approve, siap buat invoice
                ],
                [
                    'no_pengiriman' => 'SHIP-2024-005',
                    'status' => 'berhasil',
                    'tanggal_kirim' => Carbon::now()->subDays(1),
                    'total_harga_kirim' => 22000000,
                    'approval_status' => 'completed', // Semua sudah approve, siap buat invoice
                ],
            ];

            foreach ($pengirimanData as $data) {
                $approvalStatus = $data['approval_status'];
                unset($data['approval_status']);

                // Create Pengiriman (Observer will auto-create approval_pembayaran if status = menunggu_verifikasi)
                $pengiriman = Pengiriman::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'purchasing_id' => $staffPurchasing->id ?? 1,
                    'forecast_id' => $forecast->id,
                    'no_pengiriman' => $data['no_pengiriman'],
                    'tanggal_kirim' => $data['tanggal_kirim'],
                    'hari_kirim' => $data['tanggal_kirim']->locale('id')->isoFormat('dddd'),
                    'total_qty_kirim' => 1000,
                    'total_harga_kirim' => $data['total_harga_kirim'],
                    'status' => $data['status'],
                    'catatan' => 'Data untuk testing sistem approval accounting',
                ]);

                // Update Approval Pembayaran jika status menunggu_verifikasi
                // (Observer sudah create dengan status 'pending', kita update sesuai kebutuhan)
                if ($data['status'] === 'menunggu_verifikasi' && $approvalStatus !== 'pending') {
                    $approval = ApprovalPembayaran::where('pengiriman_id', $pengiriman->id)->first();

                    if ($approval) {
                        $updateData = ['status' => $approvalStatus];

                        // Set approval berdasarkan status
                        if (in_array($approvalStatus, ['staff_approved', 'manager_approved', 'completed'])) {
                            $updateData['staff_id'] = $staffAccounting->id;
                            $updateData['staff_approved_at'] = Carbon::now()->subHours(3);
                        }

                        if (in_array($approvalStatus, ['manager_approved', 'completed'])) {
                            $updateData['manager_id'] = $managerAccounting->id;
                            $updateData['manager_approved_at'] = Carbon::now()->subHours(2);
                        }

                        if ($approvalStatus === 'completed') {
                            $updateData['superadmin_id'] = $direktur->id;
                            $updateData['superadmin_approved_at'] = Carbon::now()->subHours(1);
                        }

                        $approval->update($updateData);
                    }
                }

                // Approval Penagihan dibuat saat user create invoice dari UI
            }

            $this->command->info('✅ Pengiriman Accounting Seeder berhasil dijalankan!');
            $this->command->info('📦 5 data pengiriman dibuat:');
            $this->command->info('   - 3 dengan status "menunggu_verifikasi" (untuk Approval Pembayaran)');
            $this->command->info('   - 2 dengan status "berhasil" (siap untuk Approval Penagihan)');
        });
    }
}
