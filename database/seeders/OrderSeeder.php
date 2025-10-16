<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Klien;
use App\Models\BahanBakuKlien;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing data to create relations
        $kliens = Klien::take(5)->get(); // Limit to first 5 kliens for simplicity
        $materials = BahanBakuKlien::take(10)->get(); // Limit materials
        $suppliers = Supplier::take(5)->get(); // Limit suppliers
        $users = User::where('role', '!=', 'direktur')->take(3)->get(); // Exclude director
        
        if ($kliens->isEmpty() || $materials->isEmpty() || $suppliers->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Please run other seeders first (UserSeeder, KlienSeeder, BahanBakuKlienSeeder, SupplierSeeder)');
            return;
        }
        
        $this->command->info('Creating sample orders...');

        // Create sample orders with different statuses
        $orders = [
            [
                'klien_id' => $kliens->first()->id,
                'created_by' => $users->where('role', 'marketing')->first()->id,
                'tanggal_order' => Carbon::now()->subDays(10),
                'priority' => 'tinggi',
                'status' => 'selesai',
                'catatan' => 'Order besar untuk produksi Q4',
                'dikonfirmasi_at' => Carbon::now()->subDays(9),
                'selesai_at' => Carbon::now()->subDays(2),
                'details' => [
                    [
                        'bahan_baku_klien_id' => $materials->first()->id,
                        'supplier_id' => $suppliers->first()->id,
                        'qty' => 1000,
                        'satuan' => 'kg',
                        'harga_supplier' => 15000,
                        'harga_jual' => 18000,
                        'qty_shipped' => 1000,
                        'status' => 'selesai',
                        'spesifikasi_khusus' => 'Kemasan vacuum pack',
                    ],
                    [
                        'bahan_baku_klien_id' => $materials->skip(1)->first()->id,
                        'supplier_id' => $suppliers->skip(1)->first()->id,
                        'qty' => 500,
                        'satuan' => 'kg',
                        'harga_supplier' => 22000,
                        'harga_jual' => 26000,
                        'qty_shipped' => 500,
                        'status' => 'selesai',
                        'catatan' => 'Prioritas tinggi',
                    ]
                ]
            ],
            [
                'klien_id' => $kliens->skip(1)->first()->id ?? $kliens->first()->id,
                'created_by' => $users->where('role', 'marketing')->first()->id,
                'tanggal_order' => Carbon::now()->subDays(5),
                'priority' => 'normal',
                'status' => 'diproses',
                'catatan' => 'Order rutin bulanan',
                'dikonfirmasi_at' => Carbon::now()->subDays(4),
                'details' => [
                    [
                        'bahan_baku_klien_id' => $materials->skip(2)->first()->id,
                        'supplier_id' => $suppliers->first()->id,
                        'qty' => 750,
                        'satuan' => 'kg',
                        'harga_supplier' => 18000,
                        'harga_jual' => 21000,
                        'qty_shipped' => 400,
                        'status' => 'sebagian_dikirim',
                    ]
                ]
            ],
            [
                'klien_id' => $kliens->skip(2)->first()->id ?? $kliens->first()->id,
                'created_by' => $users->where('role', 'purchasing')->first()->id ?? $users->first()->id,
                'tanggal_order' => Carbon::now()->subDays(3),
                'priority' => 'mendesak',
                'status' => 'dikonfirmasi',
                'catatan' => 'Order mendesak untuk stock emergency',
                'dikonfirmasi_at' => Carbon::now()->subDays(2),
                'details' => [
                    [
                        'bahan_baku_klien_id' => $materials->skip(3)->first()->id,
                        'supplier_id' => $suppliers->skip(2)->first()->id,
                        'qty' => 300,
                        'satuan' => 'kg',
                        'harga_supplier' => 25000,
                        'harga_jual' => 28000,
                        'qty_shipped' => 0,
                        'status' => 'menunggu',
                        'spesifikasi_khusus' => 'Packaging khusus anti lembab',
                        'catatan' => 'Segera proses, stock menipis',
                    ],
                    [
                        'bahan_baku_klien_id' => $materials->skip(4)->first()->id,
                        'supplier_id' => $suppliers->first()->id,
                        'qty' => 200,
                        'satuan' => 'kg',
                        'harga_supplier' => 12000,
                        'harga_jual' => 15000,
                        'qty_shipped' => 0,
                        'status' => 'menunggu',
                    ]
                ]
            ],
            [
                'klien_id' => $kliens->first()->id,
                'created_by' => $users->where('role', 'marketing')->first()->id,
                'tanggal_order' => Carbon::now()->subDay(),
                'priority' => 'normal',
                'status' => 'draft',
                'catatan' => 'Draft order untuk evaluasi harga',
                'details' => [
                    [
                        'bahan_baku_klien_id' => $materials->skip(5)->first()->id,
                        'supplier_id' => $suppliers->first()->id,
                        'qty' => 600,
                        'satuan' => 'kg',
                        'harga_supplier' => 16000,
                        'harga_jual' => 19000,
                        'qty_shipped' => 0,
                        'status' => 'menunggu',
                    ]
                ]
            ],
            [
                'klien_id' => $kliens->skip(3)->first()->id ?? $kliens->first()->id,
                'created_by' => $users->first()->id,
                'tanggal_order' => Carbon::now()->subDays(7),
                'priority' => 'rendah',
                'status' => 'dibatalkan',
                'catatan' => 'Order dibatalkan karena perubahan spesifikasi',
                'dikonfirmasi_at' => Carbon::now()->subDays(6),
                'dibatalkan_at' => Carbon::now()->subDays(5),
                'alasan_pembatalan' => 'Spesifikasi berubah, butuh supplier lain',
                'details' => [
                    [
                        'bahan_baku_klien_id' => $materials->skip(6)->first()->id,
                        'supplier_id' => $suppliers->skip(1)->first()->id,
                        'qty' => 400,
                        'satuan' => 'kg',
                        'harga_supplier' => 20000,
                        'harga_jual' => 24000,
                        'qty_shipped' => 0,
                        'status' => 'menunggu',
                        'catatan' => 'Spesifikasi berubah, butuh supplier lain',
                    ]
                ]
            ]
        ];

        foreach ($orders as $orderData) {
            $details = $orderData['details'];
            unset($orderData['details']);
            
            $order = Order::create($orderData);
            
            foreach ($details as $detailData) {
                $detail = $order->orderDetails()->create($detailData);
                
                // Calculate subtotals based on migration structure
                $detail->total_hpp = $detail->qty * $detail->harga_supplier;
                $detail->total_harga = $detail->qty * $detail->harga_jual;
                $detail->margin_per_unit = $detail->harga_jual - $detail->harga_supplier;
                $detail->total_margin = $detail->qty * $detail->margin_per_unit;
                $detail->margin_percentage = $detail->harga_supplier > 0 
                    ? ($detail->margin_per_unit / $detail->harga_supplier * 100) 
                    : 0;
                $detail->save();
            }
            
            // Calculate order totals
            $order->calculateTotals();
            
            $this->command->info("Created order #{$order->nomor_order} for {$order->klien->nama}");
        }

        $this->command->info('OrderSeeder completed successfully!');
        $this->command->info('Created ' . Order::count() . ' orders with ' . OrderDetail::count() . ' order details');
        
        // Display status summary
        $statusCounts = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
            
        $this->command->table(
            ['Status', 'Count'],
            collect($statusCounts)->map(fn($count, $status) => [$status, $count])->values()
        );
    }
}
