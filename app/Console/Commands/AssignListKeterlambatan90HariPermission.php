<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AssignListKeterlambatan90HariPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:assign-keterlambatan-90-hari';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign permissions for List Keterlambatan 90 Hari page to super_admin role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Check if permission exists, if not create it
            $permission = Permission::firstOrCreate([
                'name' => 'page_ListKeterlambatan90Hari',
                'guard_name' => 'admin',
            ]);

            // Get super_admin role
            $superAdminRole = Role::where('name', 'super_admin')->first();
            
            if (!$superAdminRole) {
                $this->error('Super admin role not found. Please create it first.');
                return 1;
            }

            // Assign permission to super_admin role
            if (!$superAdminRole->hasPermissionTo($permission)) {
                $superAdminRole->givePermissionTo($permission);
                $this->info("Permission 'page_ListKeterlambatan90Hari' assigned to super_admin role successfully.");
            } else {
                $this->info("Permission 'page_ListKeterlambatan90Hari' already assigned to super_admin role.");
            }

            // Also assign to admin role if exists
            $adminRole = Role::where('name', 'admin')->first();
            if ($adminRole) {
                if (!$adminRole->hasPermissionTo($permission)) {
                    $adminRole->givePermissionTo($permission);
                    $this->info("Permission 'page_ListKeterlambatan90Hari' assigned to admin role successfully.");
                } else {
                    $this->info("Permission 'page_ListKeterlambatan90Hari' already assigned to admin role.");
                }
            }

            $this->info('Permissions setup completed!');
            return 0;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
