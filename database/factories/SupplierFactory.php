<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Supplier;
use App\Models\User;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'nama' => fake()->company(),
            'slug' => fake()->unique()->slug(),
            'alamat' => fake()->address(),
            'no_hp' => fake()->phoneNumber(),
            'pic_purchasing_id' => User::factory()->state(['role' => 'staff_purchasing']),
        ];
    }
}
