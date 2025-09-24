<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Forecast;
use App\Models\PurchaseOrder;
use App\Models\User;

class ForecastFactory extends Factory
{
    protected $model = Forecast::class;

    public function definition(): array
    {
        return [
            'purchase_order_id' => PurchaseOrder::factory(),
            'purchasing_id' => User::factory(),
            'no_forecast' => 'FC-' . fake()->unique()->numerify('######-####'),
            'tanggal_forecast' => fake()->date(),
            'hari_kirim_forecast' => fake()->dayOfWeek(),
            'status' => fake()->randomElement(['pending', 'sukses', 'gagal']),
            'total_qty_forecast' => fake()->randomFloat(2, 1, 1000),
            'total_harga_forecast' => fake()->randomFloat(2, 100000, 10000000),
            'catatan' => fake()->optional()->sentence(),
        ];
    }
}
