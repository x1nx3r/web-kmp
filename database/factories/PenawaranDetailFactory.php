<?php

namespace Database\Factories;

use App\Models\BahanBakuKlien;
use App\Models\BahanBakuSupplier;
use App\Models\Penawaran;
use App\Models\PenawaranDetail;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PenawaranDetail>
 */
class PenawaranDetailFactory extends Factory
{
    protected $model = PenawaranDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Get a random bahan baku klien
        $bahanBakuKlien = BahanBakuKlien::inRandomOrder()->first() 
            ?? BahanBakuKlien::factory()->create();

        // Get a random supplier material for this klien material
        $bahanBakuSupplier = BahanBakuSupplier::inRandomOrder()->first()
            ?? BahanBakuSupplier::factory()->create();

        $quantity = fake()->randomFloat(2, 10, 1000);
        $hargaSupplier = (float) $bahanBakuSupplier->harga_per_satuan;
        
        // Add margin between 10-30%
        $marginPercentage = fake()->randomFloat(2, 10, 30);
        $hargaKlien = $hargaSupplier * (1 + ($marginPercentage / 100));

        $subtotalRevenue = $quantity * $hargaKlien;
        $subtotalCost = $quantity * $hargaSupplier;
        $subtotalProfit = $subtotalRevenue - $subtotalCost;
        $calculatedMargin = ($subtotalProfit / $subtotalRevenue) * 100;

        return [
            'penawaran_id' => Penawaran::factory(),
            'bahan_baku_klien_id' => $bahanBakuKlien->id,
            'supplier_id' => $bahanBakuSupplier->supplier_id,
            'bahan_baku_supplier_id' => $bahanBakuSupplier->id,
            'nama_material' => $bahanBakuKlien->nama ?? fake()->word(),
            'satuan' => $bahanBakuKlien->satuan ?? fake()->randomElement(['kg', 'pcs', 'm', 'liter']),
            'quantity' => $quantity,
            'harga_klien' => $hargaKlien,
            'harga_supplier' => $hargaSupplier,
            'is_custom_price' => fake()->boolean(20), // 20% chance of custom price
            'subtotal_revenue' => $subtotalRevenue,
            'subtotal_cost' => $subtotalCost,
            'subtotal_profit' => $subtotalProfit,
            'margin_percentage' => $calculatedMargin,
            'notes' => fake()->optional(0.2)->sentence(),
        ];
    }

    /**
     * State with custom client price
     */
    public function withCustomPrice(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_custom_price' => true,
        ]);
    }

    /**
     * State with high margin
     */
    public function highMargin(): static
    {
        return $this->state(function (array $attributes) {
            $hargaSupplier = $attributes['harga_supplier'];
            $hargaKlien = $hargaSupplier * 1.5; // 50% margin
            $quantity = $attributes['quantity'];

            $subtotalRevenue = $quantity * $hargaKlien;
            $subtotalCost = $quantity * $hargaSupplier;
            $subtotalProfit = $subtotalRevenue - $subtotalCost;

            return [
                'harga_klien' => $hargaKlien,
                'subtotal_revenue' => $subtotalRevenue,
                'subtotal_profit' => $subtotalProfit,
                'margin_percentage' => 50.00,
            ];
        });
    }

    /**
     * State with low margin
     */
    public function lowMargin(): static
    {
        return $this->state(function (array $attributes) {
            $hargaSupplier = $attributes['harga_supplier'];
            $hargaKlien = $hargaSupplier * 1.05; // 5% margin
            $quantity = $attributes['quantity'];

            $subtotalRevenue = $quantity * $hargaKlien;
            $subtotalCost = $quantity * $hargaSupplier;
            $subtotalProfit = $subtotalRevenue - $subtotalCost;

            return [
                'harga_klien' => $hargaKlien,
                'subtotal_revenue' => $subtotalRevenue,
                'subtotal_profit' => $subtotalProfit,
                'margin_percentage' => 5.00,
            ];
        });
    }
}
