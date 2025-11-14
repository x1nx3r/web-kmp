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
        $hargaSupplier = $this->faker->randomFloat(2, 1000, 50000);
        $markup = $this->faker->randomFloat(2, 1.1, 2.5); // 10% to 150% markup
        $hargaJual = $hargaSupplier * $markup;
        
        $qtyProcessed = $this->faker->randomFloat(2, 0, $qty);
        $qtyShipped = $this->faker->randomFloat(2, 0, $qtyProcessed);
        
        return [
            'order_id' => Order::factory(),
            'bahan_baku_klien_id' => BahanBakuKlien::factory(),
            'supplier_id' => Supplier::factory(),
            'qty' => $qty,
            'satuan' => $this->faker->randomElement(['kg', 'ton', 'liter', 'meter', 'pcs', 'box']),
            'harga_supplier' => $hargaSupplier,
            'harga_jual' => $hargaJual,
            'qty_shipped' => $qtyShipped,
            'status' => $this->faker->randomElement(['menunggu', 'diproses', 'sebagian_dikirim', 'selesai']),
            'spesifikasi_khusus' => $this->faker->optional()->paragraph(),
            'catatan' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the order detail is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'menunggu',
            'qty_shipped' => 0,
        ]);
    }

    /**
     * Indicate that the order detail is being processed.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'diproses',
            'qty_shipped' => 0,
        ]);
    }

    /**
     * Indicate that the order detail is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'selesai',
            'qty_shipped' => $attributes['qty'],
        ]);
    }

    /**
     * Indicate that the order detail is partially shipped.
     */
    public function partiallyShipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sebagian_dikirim',
            'qty_shipped' => $this->faker->randomFloat(2, 0.1, $attributes['qty'] * 0.9),
        ]);
    }

    /**
     * Indicate high margin item.
     */
    public function highMargin(): static
    {
        return $this->state(function (array $attributes) {
            $hargaSupplier = $attributes['harga_supplier'];
            $hargaJual = $hargaSupplier * $this->faker->randomFloat(2, 1.3, 2.0); // 30-100% markup
            
            return [
                'harga_jual' => $hargaJual,
            ];
        });
    }

    /**
     * Indicate low margin item.
     */
    public function lowMargin(): static
    {
        return $this->state(function (array $attributes) {
            $hargaSupplier = $attributes['harga_supplier'];
            $hargaJual = $hargaSupplier * $this->faker->randomFloat(2, 1.01, 1.15); // 1-15% markup
            
            return [
                'harga_jual' => $hargaJual,
            ];
        });
    }



    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (OrderDetail $orderDetail) {
            // Ensure calculations are correct
            $orderDetail->calculateTotals();
        })->afterCreating(function (OrderDetail $orderDetail) {
            // Update parent order totals
            $orderDetail->order->calculateTotals();
        });
    }
}