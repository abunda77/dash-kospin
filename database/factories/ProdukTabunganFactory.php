<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProdukTabungan>
 */
class ProdukTabunganFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_produk' => fake()->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'nama_produk' => fake()->words(3, true),
            'jenis_tabungan_id' => 1,
            'bunga_beaya_id' => 1,
            'keterangan' => fake()->sentence(),
        ];
    }
}
