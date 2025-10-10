<?php

namespace Database\Seeders;

use App\Models\BahanBakuKlien;
use App\Models\BahanBakuSupplier;
use App\Models\Klien;
use App\Models\Penawaran;
use App\Models\PenawaranAlternativeSupplier;
use App\Models\PenawaranDetail;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PenawaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users - must exist from UserSeeder
        $marketingUser = User::where('role', 'marketing')->first();
        if (!$marketingUser) {
            $this->command->error('No marketing user found! Please run UserSeeder first.');
            return;
        }
        
        $managerUser = User::where('role', 'manager_purchasing')->first();
        if (!$managerUser) {
            $this->command->error('No manager_purchasing user found! Please run UserSeeder first.');
            return;
        }

        // Get existing kliens - must exist from KlienSeeder
        $kliens = Klien::all();
        if ($kliens->isEmpty()) {
            $this->command->error('No kliens found! Please run KlienSeeder first.');
            return;
        }

        // Get existing suppliers and materials - must exist from their seeders
        $suppliers = Supplier::all();
        if ($suppliers->isEmpty()) {
            $this->command->error('No suppliers found! Please run SupplierSeeder first.');
            return;
        }

        $bahanBakuSuppliers = BahanBakuSupplier::all();
        if ($bahanBakuSuppliers->isEmpty()) {
            $this->command->error('No bahan baku suppliers found! Please run BahanBakuSupplierSeeder first.');
            return;
        }        // Create multiple penawaran with different statuses
        $this->command->info('Creating penawaran entries...');

        // 1. Draft penawaran (2 entries)
        foreach (range(1, 2) as $i) {
            $klien = $kliens->random();
            
            $penawaran = Penawaran::create([
                'klien_id' => $klien->id,
                'tanggal_penawaran' => now()->subDays(rand(1, 10)),
                'tanggal_berlaku_sampai' => now()->addDays(30),
                'status' => 'draft',
                'created_by' => $marketingUser->id,
                'catatan' => 'Draft penawaran masih dalam proses penyusunan',
            ]);

            $this->createPenawaranDetails($penawaran, $klien, $bahanBakuSuppliers, rand(2, 4));
        }

        // 2. Pending verification (3 entries)
        foreach (range(1, 3) as $i) {
            $klien = $kliens->random();
            
            $penawaran = Penawaran::create([
                'klien_id' => $klien->id,
                'tanggal_penawaran' => now()->subDays(rand(5, 15)),
                'tanggal_berlaku_sampai' => now()->addDays(25),
                'status' => 'menunggu_verifikasi',
                'created_by' => $marketingUser->id,
                'catatan' => 'Menunggu approval dari manager',
            ]);

            $this->createPenawaranDetails($penawaran, $klien, $bahanBakuSuppliers, rand(3, 6));
        }

        // 3. Approved penawaran (5 entries)
        foreach (range(1, 5) as $i) {
            $klien = $kliens->random();
            $tanggalPenawaran = now()->subDays(rand(10, 60));
            
            $penawaran = Penawaran::create([
                'klien_id' => $klien->id,
                'tanggal_penawaran' => $tanggalPenawaran,
                'tanggal_berlaku_sampai' => $tanggalPenawaran->copy()->addDays(30),
                'status' => 'disetujui',
                'created_by' => $marketingUser->id,
                'verified_by' => $managerUser->id,
                'verified_at' => $tanggalPenawaran->copy()->addDays(rand(1, 3)),
                'catatan' => 'Penawaran telah disetujui dan siap diproses',
            ]);

            $this->createPenawaranDetails($penawaran, $klien, $bahanBakuSuppliers, rand(4, 8));
        }

        // 4. Rejected penawaran (2 entries)
        foreach (range(1, 2) as $i) {
            $klien = $kliens->random();
            $tanggalPenawaran = now()->subDays(rand(15, 45));
            
            $penawaran = Penawaran::create([
                'klien_id' => $klien->id,
                'tanggal_penawaran' => $tanggalPenawaran,
                'tanggal_berlaku_sampai' => $tanggalPenawaran->copy()->addDays(30),
                'status' => 'ditolak',
                'created_by' => $marketingUser->id,
                'verified_by' => $managerUser->id,
                'verified_at' => $tanggalPenawaran->copy()->addDays(rand(1, 2)),
                'catatan' => 'Penawaran ditolak karena margin terlalu rendah',
                'alasan_penolakan' => 'Margin di bawah standar perusahaan. Harap review ulang harga supplier.',
            ]);

            $this->createPenawaranDetails($penawaran, $klien, $bahanBakuSuppliers, rand(2, 4), true);
        }

        // 5. Expired penawaran (1 entry)
        $klien = $kliens->random();
        $tanggalPenawaran = now()->subDays(65);
        
        $penawaran = Penawaran::create([
            'klien_id' => $klien->id,
            'tanggal_penawaran' => $tanggalPenawaran,
            'tanggal_berlaku_sampai' => $tanggalPenawaran->copy()->addDays(30),
            'status' => 'expired',
            'created_by' => $marketingUser->id,
            'verified_by' => $managerUser->id,
            'verified_at' => $tanggalPenawaran->copy()->addDays(1),
            'catatan' => 'Penawaran sudah kadaluarsa',
        ]);

        $this->createPenawaranDetails($penawaran, $klien, $bahanBakuSuppliers, rand(3, 5));

        $this->command->info('Penawaran seeding completed!');
        $this->command->info('Created: 2 draft, 3 pending, 5 approved, 2 rejected, 1 expired = 13 total');
    }

    /**
     * Create penawaran details with multiple suppliers
     */
    private function createPenawaranDetails(
        Penawaran $penawaran, 
        Klien $klien,
        $bahanBakuSuppliers, 
        int $count,
        bool $lowMargin = false
    ): void {
        // Get materials for this specific klien
        $bahanBakuKliens = BahanBakuKlien::where('klien_id', $klien->id)
            ->whereNotNull('harga_approved')
            ->get();

        if ($bahanBakuKliens->isEmpty()) {
            $this->command->warn("No materials found for klien: {$klien->nama}. Skipping penawaran.");
            $penawaran->delete();
            return;
        }

        // Limit count to available materials
        $count = min($count, $bahanBakuKliens->count());
        $usedMaterials = [];

        foreach (range(1, $count) as $i) {
            // Get a unique material for this klien
            do {
                $bahanBakuKlien = $bahanBakuKliens->random();
            } while (in_array($bahanBakuKlien->id, $usedMaterials) && count($usedMaterials) < $bahanBakuKliens->count());
            
            $usedMaterials[] = $bahanBakuKlien->id;

            // Get available suppliers for this material based on name matching
            $materialName = $bahanBakuKlien->nama ?? 'Material';
            $materialKeywords = explode(' ', $materialName);
            $availableSuppliers = collect();
            
            // Try to find suppliers with matching material names
            foreach ($materialKeywords as $keyword) {
                if (strlen($keyword) > 3) { // Only use significant keywords
                    $matches = $bahanBakuSuppliers->filter(function($s) use ($keyword) {
                        return stripos($s->nama, $keyword) !== false && $s->harga_per_satuan > 0;
                    });
                    if ($matches->isNotEmpty()) {
                        $availableSuppliers = $matches->take(4); // Get up to 4 matching suppliers
                        break;
                    }
                }
            }
            
            // If no match, get random valid suppliers
            if ($availableSuppliers->isEmpty()) {
                $validSuppliers = $bahanBakuSuppliers->filter(fn($s) => $s->harga_per_satuan > 0);
                if ($validSuppliers->isNotEmpty()) {
                    $availableSuppliers = $validSuppliers->random(min(3, $validSuppliers->count()));
                }
            }

            // Select the cheapest supplier as the primary choice
            $selectedSupplier = $availableSuppliers->sortBy('harga_per_satuan')->first();
            
            // If no valid supplier, skip this material
            if (!$selectedSupplier || !$selectedSupplier->harga_per_satuan) {
                continue;
            }
            
            $quantity = rand(50, 500);
            $hargaSupplier = (float) $selectedSupplier->harga_per_satuan;
            
            // Set margin based on whether it's rejected or not
            if ($lowMargin) {
                $marginPercentage = rand(3, 8); // Low margin for rejected
            } else {
                $marginPercentage = rand(12, 28); // Normal margin
            }
            
            $hargaKlien = $hargaSupplier * (1 + ($marginPercentage / 100));

            $subtotalRevenue = $quantity * $hargaKlien;
            $subtotalCost = $quantity * $hargaSupplier;
            $subtotalProfit = $subtotalRevenue - $subtotalCost;
            $calculatedMargin = $subtotalRevenue > 0 ? ($subtotalProfit / $subtotalRevenue) * 100 : 0;

            $detail = PenawaranDetail::create([
                'penawaran_id' => $penawaran->id,
                'bahan_baku_klien_id' => $bahanBakuKlien->id,
                'supplier_id' => $selectedSupplier->supplier_id,
                'bahan_baku_supplier_id' => $selectedSupplier->id,
                'nama_material' => $bahanBakuKlien->nama ?? 'Unknown Material',
                'satuan' => $bahanBakuKlien->satuan ?? 'pcs',
                'quantity' => $quantity,
                'harga_klien' => $hargaKlien,
                'harga_supplier' => $hargaSupplier,
                'is_custom_price' => rand(0, 100) < 20, // 20% custom price
                'subtotal_revenue' => $subtotalRevenue,
                'subtotal_cost' => $subtotalCost,
                'subtotal_profit' => $subtotalProfit,
                'margin_percentage' => $calculatedMargin,
            ]);

            // Add alternative suppliers (skip the selected one and ensure unique suppliers)
            $addedSupplierIds = [$selectedSupplier->supplier_id];
            foreach ($availableSuppliers->skip(1) as $altSupplier) {
                // Skip if already added or if price is invalid
                if (in_array($altSupplier->supplier_id, $addedSupplierIds)) {
                    continue;
                }
                
                if ($altSupplier->harga_per_satuan && $altSupplier->harga_per_satuan > 0) {
                    PenawaranAlternativeSupplier::create([
                        'penawaran_detail_id' => $detail->id,
                        'supplier_id' => $altSupplier->supplier_id,
                        'bahan_baku_supplier_id' => $altSupplier->id,
                        'harga_supplier' => $altSupplier->harga_per_satuan,
                        'notes' => 'Harga lebih tinggi dari supplier terpilih',
                    ]);
                    $addedSupplierIds[] = $altSupplier->supplier_id;
                }
            }
        }

        // Recalculate totals
        $penawaran->calculateTotals();
    }
}
