<?php

namespace Database\Factories;

use App\Models\OrderDetail;
use App\Models\Order;
use App\Models\BahanBakuKlien;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderDetail>
 */
class OrderDetailFactory extends Factory
{
    protected $model = OrderDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $qty = $this->faker->randomFloat(2, 1, 1000);
        $cheapestPrice = $this->faker->randomFloat(2, 1000, 30000);
        $mostExpensivePrice =
            $cheapestPrice * $this->faker->randomFloat(2, 1.2, 1.8);
        $recommendedPrice = ($cheapestPrice + $mostExpensivePrice) / 2;
        $markup = $this->faker->randomFloat(2, 1.1, 2.5); // 10% to 150% markup
        $hargaJual = $recommendedPrice * $markup;

        $qtyShipped = $this->faker->randomFloat(2, 0, $qty);
        $remainingQty = max(0, $qty - $qtyShipped);

        return [
            "order_id" => Order::factory(),
            "bahan_baku_klien_id" => BahanBakuKlien::factory(),
            "nama_material_po" => $this->faker->optional()->words(3, true),
            "qty" => $qty,
            "satuan" => $this->faker->randomElement([
                "kg",
                "ton",
                "liter",
                "meter",
                "pcs",
                "box",
            ]),
            "cheapest_price" => $cheapestPrice,
            "most_expensive_price" => $mostExpensivePrice,
            "recommended_price" => $recommendedPrice,
            "harga_jual" => $hargaJual,
            "total_harga" => $qty * $hargaJual,
            "best_margin_percentage" => $this->faker->randomFloat(2, 20, 50),
            "worst_margin_percentage" => $this->faker->randomFloat(2, 5, 20),
            "recommended_margin_percentage" => $this->faker->randomFloat(
                2,
                15,
                35,
            ),
            "available_suppliers_count" => $this->faker->numberBetween(1, 5),
            "recommended_supplier_id" => null,
            "qty_shipped" => $qtyShipped,
            "total_shipped_quantity" => $qtyShipped,
            "remaining_quantity" => $remainingQty,
            "suppliers_used_count" => 0,
            "supplier_options_populated" => false,
            "options_populated_at" => null,
            "status" => $this->faker->randomElement([
                "menunggu",
                "diproses",
                "sebagian_dikirim",
                "selesai",
            ]),
            "spesifikasi_khusus" => $this->faker->optional()->paragraph(),
            "catatan" => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the order detail is pending.
     */
    public function pending(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "status" => "menunggu",
                "qty_shipped" => 0,
                "total_shipped_quantity" => 0,
                "remaining_quantity" => $attributes["qty"],
            ],
        );
    }

    /**
     * Indicate that the order detail is being processed.
     */
    public function processing(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "status" => "diproses",
                "qty_shipped" => 0,
                "total_shipped_quantity" => 0,
                "remaining_quantity" => $attributes["qty"],
            ],
        );
    }

    /**
     * Indicate that the order detail is completed.
     */
    public function completed(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "status" => "selesai",
                "qty_shipped" => $attributes["qty"],
                "total_shipped_quantity" => $attributes["qty"],
                "remaining_quantity" => 0,
            ],
        );
    }

    /**
     * Indicate that the order detail is partially shipped.
     */
    public function partiallyShipped(): static
    {
        return $this->state(function (array $attributes) {
            $shipped = $this->faker->randomFloat(
                2,
                0.1,
                $attributes["qty"] * 0.9,
            );
            return [
                "status" => "sebagian_dikirim",
                "qty_shipped" => $shipped,
                "total_shipped_quantity" => $shipped,
                "remaining_quantity" => $attributes["qty"] - $shipped,
            ];
        });
    }

    /**
     * Indicate high margin item.
     */
    public function highMargin(): static
    {
        return $this->state(function (array $attributes) {
            $recommendedPrice = $attributes["recommended_price"];
            $hargaJual =
                $recommendedPrice * $this->faker->randomFloat(2, 1.3, 2.0); // 30-100% markup

            return [
                "harga_jual" => $hargaJual,
                "total_harga" => $attributes["qty"] * $hargaJual,
                "best_margin_percentage" => $this->faker->randomFloat(
                    2,
                    30,
                    50,
                ),
            ];
        });
    }

    /**
     * Indicate low margin item.
     */
    public function lowMargin(): static
    {
        return $this->state(function (array $attributes) {
            $recommendedPrice = $attributes["recommended_price"];
            $hargaJual =
                $recommendedPrice * $this->faker->randomFloat(2, 1.01, 1.15); // 1-15% markup

            return [
                "harga_jual" => $hargaJual,
                "total_harga" => $attributes["qty"] * $hargaJual,
                "worst_margin_percentage" => $this->faker->randomFloat(
                    2,
                    1,
                    15,
                ),
            ];
        });
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (OrderDetail $orderDetail) {
            // Update parent order totals
            $orderDetail->order->calculateTotals();
        });
    }
}
