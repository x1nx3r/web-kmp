<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Klien;
use App\Models\BahanBakuKlien;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class EnhancedOrderSeeder extends Seeder
{
    /**
     * Run the database seeds for the enhanced multi-supplier order system.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Enhanced Order Seeder...');

        // Get required data
        $kliens = Klien::all();
        $materials = BahanBakuKlien::all();
        $marketingUser = User::where('role', 'marketing')->first() ?? User::first();
        
        if ($kliens->isEmpty() || $materials->isEmpty() || !$marketingUser) {
            $this->command->error('❌ Missing required data! Please run other seeders first:');
            $this->command->line('   - UserSeeder (for marketing user)');
            $this->command->line('   - KlienSeeder (for clients)');
            $this->command->line('   - BahanBakuKlienSeeder (for materials)');
            $this->command->line('   - BahanBakuSupplierSeeder (for supplier prices)');
            return;
        }

        // Filter materials that have matching suppliers (by name)
        $materialsWithSuppliers = $materials->filter(function($material) {
            return \App\Models\BahanBakuSupplier::where('nama', 'LIKE', '%' . $material->nama . '%')
                ->orWhere('nama', 'LIKE', '%' . trim(explode(' ', $material->nama)[0]) . '%')
                ->exists();
        });

        if ($materialsWithSuppliers->isEmpty()) {
            $this->command->error('❌ No materials found with matching supplier names!');
            $this->command->info('📝 Available materials: ' . $materials->pluck('nama')->take(5)->implode(', '));
            $this->command->info('📝 Available supplier materials: ' . \App\Models\BahanBakuSupplier::take(5)->pluck('nama')->implode(', '));
            return;
        }

        $this->command->info("📦 Found {$materialsWithSuppliers->count()} materials with suppliers");
        $this->command->info("👥 Found {$kliens->count()} clients");

        // Create sample orders with different scenarios
        $orders = [
            $this->createLargeProductionOrder($kliens, $materialsWithSuppliers, $marketingUser),
            $this->createUrgentOrder($kliens, $materialsWithSuppliers, $marketingUser),
            $this->createRoutineOrder($kliens, $materialsWithSuppliers, $marketingUser),
            $this->createMultiItemOrder($kliens, $materialsWithSuppliers, $marketingUser),
        ];

        foreach ($orders as $orderData) {
            $this->createOrderWithAutoSuppliers($orderData, $marketingUser);
        }

        $this->command->info('✅ Enhanced Order Seeder completed successfully!');
        $this->command->info("📊 Created " . count($orders) . " orders with auto-supplier population");
    }

    private function createLargeProductionOrder($kliens, $materials, $user): array
    {
        return [
            'klien_id' => $kliens->where('nama', 'PT Central Proteina')->first()?->id ?? $kliens->first()->id,
            'tanggal_order' => Carbon::now()->subDays(7),
            'priority' => 'tinggi',
            'status' => 'dikonfirmasi',
            'catatan' => 'Order besar untuk produksi Q4 - prioritas tinggi',
            'dikonfirmasi_at' => Carbon::now()->subDays(6),
            'details' => [
                [
                    'material_name' => 'Mie Kuning',
                    'qty' => 5000,
                    'satuan' => 'kg',
                    'harga_jual' => 18500,
                    'spesifikasi_khusus' => 'Kemasan vacuum pack, protein tinggi',
                ],
                [
                    'material_name' => 'Bone Meal',
                    'qty' => 2000,
                    'satuan' => 'kg', 
                    'harga_jual' => 24000,
                    'catatan' => 'Prioritas tinggi untuk batch produksi',
                ]
            ]
        ];
    }

    private function createUrgentOrder($kliens, $materials, $user): array
    {
        return [
            'klien_id' => $kliens->where('nama', 'CJ Feed')->first()?->id ?? $kliens->first()->id,
            'tanggal_order' => Carbon::now()->subDays(2),
            'priority' => 'mendesak',
            'status' => 'diproses',
            'catatan' => 'Order mendesak untuk stock emergency',
            'dikonfirmasi_at' => Carbon::now()->subDays(1),
            'details' => [
                [
                    'material_name' => 'Katul',
                    'qty' => 1500,
                    'satuan' => 'kg',
                    'harga_jual' => 15800,
                    'spesifikasi_khusus' => 'Kualitas premium, moisture rendah',
                ]
            ]
        ];
    }

    private function createRoutineOrder($kliens, $materials, $user): array
    {
        return [
            'klien_id' => $kliens->where('nama', 'PT Sreya Sewu')->first()?->id ?? $kliens->first()->id,
            'tanggal_order' => Carbon::now()->subDays(5),
            'priority' => 'normal',
            'status' => 'draft',
            'catatan' => 'Order rutin bulanan - jadwal normal',
            'details' => [
                [
                    'material_name' => 'Tepung Biskuit',
                    'qty' => 800,
                    'satuan' => 'kg',
                    'harga_jual' => 22000,
                ]
            ]
        ];
    }

    private function createMultiItemOrder($kliens, $materials, $user): array
    {
        return [
            'klien_id' => $kliens->skip(2)->first()?->id ?? $kliens->first()->id,
            'tanggal_order' => Carbon::now()->subDays(3),
            'priority' => 'normal',
            'status' => 'dikonfirmasi',
            'catatan' => 'Order campuran untuk formula pakan',
            'dikonfirmasi_at' => Carbon::now()->subDays(2),
            'details' => [
                [
                    'material_name' => 'Garam',
                    'qty' => 300,
                    'satuan' => 'kg',
                    'harga_jual' => 8500,
                ],
                [
                    'material_name' => 'Molases',
                    'qty' => 1200,
                    'satuan' => 'kg',
                    'harga_jual' => 12000,
                ],
                [
                    'material_name' => 'Menir',
                    'qty' => 600,
                    'satuan' => 'kg',
                    'harga_jual' => 16500,
                ]
            ]
        ];
    }

    private function createOrderWithAutoSuppliers(array $orderData, $user): void
    {
        $this->command->info("📝 Creating order for: " . Klien::find($orderData['klien_id'])->nama);

        // Create the order
        $order = Order::create([
            'klien_id' => $orderData['klien_id'],
            'created_by' => $user->id,
            'tanggal_order' => $orderData['tanggal_order'],
            'priority' => $orderData['priority'],
            'status' => $orderData['status'],
            'catatan' => $orderData['catatan'],
            'dikonfirmasi_at' => $orderData['dikonfirmasi_at'] ?? null,
        ]);

        $this->command->line("   📋 Order created: {$order->no_order}");

        // Create order details and auto-populate suppliers
        foreach ($orderData['details'] as $detailData) {
            $material = BahanBakuKlien::where('nama', 'LIKE', '%' . $detailData['material_name'] . '%')
                ->first();

            if (!$material) {
                $this->command->warn("   ⚠️  Skipping {$detailData['material_name']} - material not found");
                continue;
            }

            // Check if there are suppliers for this material (by name matching)
            $matchingSuppliers = \App\Models\BahanBakuSupplier::where('nama', 'LIKE', '%' . $material->nama . '%')
                ->orWhere('nama', 'LIKE', '%' . trim(explode(' ', $material->nama)[0]) . '%')
                ->count();

            if ($matchingSuppliers == 0) {
                $this->command->warn("   ⚠️  Skipping {$detailData['material_name']} - no matching suppliers found");
                continue;
            }

            // Create order detail with new structure (no supplier_id)
            $orderDetail = OrderDetail::create([
                'order_id' => $order->id,
                'bahan_baku_klien_id' => $material->id,
                'qty' => $detailData['qty'],
                'satuan' => $detailData['satuan'],
                'harga_jual' => $detailData['harga_jual'],
                'total_harga' => $detailData['qty'] * $detailData['harga_jual'],
                'status' => 'menunggu',
                'spesifikasi_khusus' => $detailData['spesifikasi_khusus'] ?? null,
                'catatan' => $detailData['catatan'] ?? null,
            ]);

            // 🚀 AUTO-POPULATE SUPPLIERS - This is the key feature!
            $orderDetail->populateSupplierOptions();

            $supplierCount = $orderDetail->orderSuppliers()->count();
            $bestMargin = $orderDetail->best_margin_percentage ?? 0;
            
            $this->command->line("   ✅ {$detailData['material_name']}: {$supplierCount} suppliers, best margin: {$bestMargin}%");
        }

        // Calculate order totals
        $order->calculateTotals();
        
        $totalSuppliers = $order->orderSuppliers()->distinct('supplier_id')->count();
        $this->command->line("   💰 Total: Rp " . number_format($order->total_amount, 0, ',', '.'));
        $this->command->line("   🏪 Available suppliers: {$totalSuppliers}");
        $this->command->line("");
    }
}
