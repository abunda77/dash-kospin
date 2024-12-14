<?php

namespace App\Policies;

use App\Models\Admin;
use TomatoPHP\FilamentArtisan\Models\Command;
use Illuminate\Auth\Access\HandlesAuthorization;

class ArtisanPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin): bool
    {
        return $admin->can('view_any_artisan');
    }

    public function view(Admin $admin, Command $command): bool
    {
        return $admin->can('view_artisan');
    }

    public function create(Admin $admin): bool
    {
        return $admin->can('create_artisan');
    }

    public function update(Admin $admin, Command $command): bool
    {
        return $admin->can('update_artisan');
    }

    public function delete(Admin $admin, Command $command): bool
    {
        return $admin->can('delete_artisan');
    }
}
