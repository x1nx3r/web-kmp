<?php

namespace Database\Factories;

use App\Models\Klien;
use App\Models\Penawaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Penawaran>
 */
class PenawaranFactory extends Factory
{
    protected $model = Penawaran::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tanggalPenawaran = fake()->dateTimeBetween('-3 months', 'now');
        $tanggalBerlaku = (clone $tanggalPenawaran)->modify('+30 days');

        return [
            'klien_id' => Klien::factory(),
            'tanggal_penawaran' => $tanggalPenawaran,
            'tanggal_berlaku_sampai' => $tanggalBerlaku,
            'status' => fake()->randomElement(['draft', 'menunggu_verifikasi', 'disetujui', 'ditolak']),
            'total_revenue' => 0, // Will be calculated from details
            'total_cost' => 0,
            'total_profit' => 0,
            'margin_percentage' => 0,
            'created_by' => User::factory(),
            'catatan' => fake()->optional(0.3)->sentence(),
        ];
    }

    /**
     * State for draft penawaran
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'verified_by' => null,
            'verified_at' => null,
        ]);
    }

    /**
     * State for pending verification
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'menunggu_verifikasi',
            'verified_by' => null,
            'verified_at' => null,
        ]);
    }

    /**
     * State for approved penawaran
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'disetujui',
            'verified_by' => User::factory(),
            'verified_at' => fake()->dateTimeBetween($attributes['tanggal_penawaran'], 'now'),
        ]);
    }

    /**
     * State for rejected penawaran
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ditolak',
            'verified_by' => User::factory(),
            'verified_at' => fake()->dateTimeBetween($attributes['tanggal_penawaran'], 'now'),
            'alasan_penolakan' => fake()->sentence(),
        ]);
    }

    /**
     * State for expired penawaran
     */
    public function expired(): static
    {
        $expiredDate = fake()->dateTimeBetween('-60 days', '-31 days');
        
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'tanggal_penawaran' => (clone $expiredDate)->modify('-30 days'),
            'tanggal_berlaku_sampai' => $expiredDate,
        ]);
    }
}
