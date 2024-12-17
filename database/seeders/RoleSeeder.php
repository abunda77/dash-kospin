<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Buat role panel_user
        Role::create(['name' => 'panel_user']);

        // Bisa tambahkan role lain jika diperlukan
        // Role::create(['name' => 'admin']);
        // Role::create(['name' => 'user']);
    }
}
