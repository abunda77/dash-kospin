<?php

namespace Database\Factories;

use App\Models\Tabungan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransaksiTabungan>
 */
class TransaksiTabunganFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_tabungan' => Tabungan::factory(),
            'jenis_transaksi' => fake()->randomElement(['debit', 'kredit']),
            'jumlah' => fake()->randomFloat(2, 10000, 1000000),
            'tanggal_transaksi' => fake()->dateTimeBetween('-1 year', 'now'),
            'keterangan' => fake()->sentence(),
            'kode_transaksi' => fake()->unique()->numerify('TRX##########'),
            'kode_teller' => fake()->optional()->numerify('TLR###'),
        ];
    }
    
    public function debit(): static
    {
        return $this->state(fn (array $attributes) => [
            'jenis_transaksi' => 'debit',
        ]);
    }
    
    public function kredit(): static
    {
        return $this->state(fn (array $attributes) => [
            'jenis_transaksi' => 'kredit',
        ]);
    }
}
