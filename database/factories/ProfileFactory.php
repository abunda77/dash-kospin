<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'address' => fake()->address(),
            'sign_identity' => fake()->randomElement(['KTP', 'SIM', 'Passport']),
            'no_identity' => fake()->numerify('################'),
            'image_identity' => json_encode(['url' => fake()->imageUrl()]),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->email(),
            'whatsapp' => fake()->phoneNumber(),
            'gender' => fake()->randomElement(['L', 'P']),
            'birthday' => fake()->date(),
            'mariage' => fake()->randomElement(['Menikah', 'Belum Menikah']),
            'job' => fake()->jobTitle(),
            'province_id' => fake()->numberBetween(1, 34),
            'district_id' => fake()->numberBetween(1, 100),
            'city_id' => fake()->numberBetween(1, 100),
            'village_id' => fake()->numberBetween(1, 100),
            'monthly_income' => fake()->randomFloat(2, 1000000, 10000000),
            'is_active' => fake()->boolean(),
            'type_member' => fake()->randomElement(['regular', 'premium']),
            'avatar' => fake()->imageUrl(),
            'remote_url' => fake()->url(),
            'notes' => fake()->text()
        ];
    }
}
