<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Klien;

class KlienFactory extends Factory
{
    protected $model = Klien::class;

    public function definition(): array
    {
        return [
            "nama" => fake()->company(),
            "cabang" => fake()->city(),
            "alamat_lengkap" => fake()->optional()->address(),
        ];
    }
}
