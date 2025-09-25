<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BahanBakuSupplier;
use App\Models\Supplier;

class BahanBakuSupplierFactory extends Factory
{
    protected $model = BahanBakuSupplier::class;

    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'nama' => fake()->words(3, true),
            'slug' => fake()->unique()->slug(),
            'harga_per_satuan' => fake()->randomFloat(2, 1000, 100000),
            'satuan' => fake()->randomElement(['kg', 'liter', 'pcs', 'meter']),
            'stok' => fake()->randomFloat(2, 1, 1000),
        ];
    }
}
