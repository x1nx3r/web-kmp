<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Klien;
use App\Models\BahanBakuKlien;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SimpleOrderSeeder extends Seeder
{
    public function run(): void
    {
        // Clean start (avoid foreign key issues)
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        OrderDetail::truncate();
        Order::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // Get data
        $klien = Klien::first();
        $material = BahanBakuKlien::first();
        $supplier = Supplier::first();
        $user = User::where('role', '!=', 'direktur')->first();
        
        if (!$klien || !$material || !$supplier || !$user) {
            $this->command->error('Missing required data');
            return;
        }
        
        $this->command->info("Creating order for klien: {$klien->nama}");
        
        // Create simple order
        $order = Order::create([
            'klien_id' => $klien->id,
            'created_by' => $user->id,
            'tanggal_order' => Carbon::now()->subDays(5),
            'priority' => 'normal',
            'status' => 'draft',
            'catatan' => 'Test order from SimpleOrderSeeder',
        ]);
        
        $this->command->info("Created order: {$order->no_order}");
        
        // Create order detail
        $detail = $order->orderDetails()->create([
            'bahan_baku_klien_id' => $material->id,
            'supplier_id' => $supplier->id,
            'qty' => 100,
            'satuan' => 'kg',
            'harga_supplier' => 15000,
            'harga_jual' => 18000,
            'qty_shipped' => 0,
            'status' => 'menunggu',
        ]);
        
        // Calculate totals
        $detail->total_hpp = $detail->qty * $detail->harga_supplier;
        $detail->total_harga = $detail->qty * $detail->harga_jual;
        $detail->margin_per_unit = $detail->harga_jual - $detail->harga_supplier;
        $detail->total_margin = $detail->qty * $detail->margin_per_unit;
        $detail->margin_percentage = ($detail->margin_per_unit / $detail->harga_supplier) * 100;
        $detail->save();
        
        // Calculate order totals
        $order->calculateTotals();
        
        $this->command->info("Order seeding completed successfully!");
    }
}