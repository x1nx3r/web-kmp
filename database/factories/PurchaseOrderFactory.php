<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PurchaseOrder;
use App\Models\Klien;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        return [
            'klien_id' => Klien::factory(),
            'no_po' => 'PO-' . fake()->unique()->numerify('######'),
            'qty_total' => fake()->randomFloat(2, 1, 1000),
            'total_amount' => fake()->randomFloat(2, 100000, 10000000),
            'spesifikasi' => fake()->sentence(),
            'catatan' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(['siap', 'proses', 'selesai']),
        ];
    }
}
