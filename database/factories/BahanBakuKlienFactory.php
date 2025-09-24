<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BahanBakuKlien;

class BahanBakuKlienFactory extends Factory
{
    protected $model = BahanBakuKlien::class;

    public function definition(): array
    {
        return [
            'nama' => fake()->words(3, true),
            'satuan' => fake()->randomElement(['kg', 'liter', 'pcs', 'meter']),
            'spesifikasi' => fake()->optional()->sentence(),
            'status' => 'aktif',
        ];
    }
}
