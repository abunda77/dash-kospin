<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Jalankan RoleSeeder terlebih dahulu
        $this->call(RoleSeeder::class);

        // Buat user dengan role
        User::factory(10)
            ->has(Profile::factory())
            ->afterCreating(function ($user) {
                $user->assignRole('panel_user');
            })
            ->create();
    }
}
