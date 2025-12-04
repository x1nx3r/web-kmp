<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Klien;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tanggalOrder = $this->faker->dateTimeBetween("-6 months", "now");

        return [
            "no_order" => $this->generateOrderNumber(),
            "klien_id" => Klien::factory(),
            "created_by" => User::factory(),
            "tanggal_order" => $tanggalOrder,
            "catatan" => $this->faker->optional()->sentence(),
            "status" => $this->faker->randomElement([
                "draft",
                "dikonfirmasi",
                "diproses",
                "selesai",
                "dibatalkan",
            ]),
            "priority" => $this->faker->randomElement([
                "rendah",
                "normal",
                "tinggi",
                "mendesak",
            ]),
        ];
    }

    /**
     * Generate a unique order number
     */
    private function generateOrderNumber(): string
    {
        return "ORD-" .
            date(
                "Ymd",
                strtotime(
                    $this->faker
                        ->dateTimeBetween("-1 year", "now")
                        ->format("Y-m-d"),
                ),
            ) .
            "-" .
            str_pad(
                $this->faker->unique()->numberBetween(1, 9999),
                4,
                "0",
                STR_PAD_LEFT,
            );
    }

    /**
     * Indicate that the order is in draft status.
     */
    public function draft(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "status" => "draft",
                "dikonfirmasi_at" => null,
                "selesai_at" => null,
                "dibatalkan_at" => null,
            ],
        );
    }

    /**
     * Indicate that the order is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "status" => "dikonfirmasi",
                "dikonfirmasi_at" => $this->faker->dateTimeBetween(
                    $attributes["tanggal_order"],
                    "now",
                ),
            ],
        );
    }

    /**
     * Indicate that the order is being processed.
     */
    public function processing(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "status" => "diproses",
                "dikonfirmasi_at" => $this->faker->dateTimeBetween(
                    $attributes["tanggal_order"],
                    "-1 week",
                ),
            ],
        );
    }

    /**
     * Indicate that the order is completed.
     */
    public function completed(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "status" => "selesai",
                "dikonfirmasi_at" => $this->faker->dateTimeBetween(
                    $attributes["tanggal_order"],
                    "-2 weeks",
                ),
                "selesai_at" => $this->faker->dateTimeBetween("-1 week", "now"),
            ],
        );
    }

    /**
     * Indicate that the order is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "status" => "dibatalkan",
                "dibatalkan_at" => $this->faker->dateTimeBetween(
                    $attributes["tanggal_order"],
                    "now",
                ),
                "alasan_pembatalan" => $this->faker->sentence(),
            ],
        );
    }

    /**
     * Indicate that the order has high priority.
     */
    public function urgent(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "priority" => "mendesak",
            ],
        );
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Order $order) {
            // Calculate totals after order details are created
            $order->calculateTotals();
        });
    }
}
