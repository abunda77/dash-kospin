<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\ProdukTabungan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tabungan>
 */
class TabunganFactory extends Factory
{
    public function definition(): array
    {
        return [
            'no_tabungan' => fake()->unique()->numerify('TAB##########'),
            'id_profile' => Profile::factory(),
            'produk_tabungan' => ProdukTabungan::factory(),
            'saldo' => fake()->randomFloat(2, 100000, 5000000),
            'tanggal_buka_rekening' => fake()->dateTimeBetween('-2 years', 'now'),
            'status_rekening' => 'aktif',
            'notes' => fake()->sentence(),
        ];
    }
}
