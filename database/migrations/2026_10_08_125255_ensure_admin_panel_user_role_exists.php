<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! DB::table('roles')->where('name', 'panel_user')->where('guard_name', 'admin')->exists()) {
            DB::table('roles')->insert([
                'name' => 'panel_user',
                'guard_name' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('roles')
            ->where('name', 'panel_user')
            ->where('guard_name', 'admin')
            ->delete();
    }
};
