<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PurchaseOrderBahanBaku;
use App\Models\PurchaseOrder;
use App\Models\BahanBakuKlien;

class PurchaseOrderBahanBakuFactory extends Factory
{
    protected $model = PurchaseOrderBahanBaku::class;

    public function definition(): array
    {
        return [
            'purchase_order_id' => PurchaseOrder::factory(),
            'bahan_baku_klien_id' => BahanBakuKlien::factory(),
            'jumlah' => fake()->randomFloat(2, 1, 1000),
            'harga_satuan' => fake()->randomFloat(2, 1000, 100000),
            'total_harga' => function (array $attributes) {
                return $attributes['jumlah'] * $attributes['harga_satuan'];
            },
        ];
    }
}
