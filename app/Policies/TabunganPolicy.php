<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Tabungan;
use Illuminate\Auth\Access\HandlesAuthorization;

class TabunganPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the admin can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->can('view_any_tabungan');
    }

    /**
     * Determine whether the admin can view the model.
     */
    public function view(Admin $admin, Tabungan $tabungan): bool
    {
        return $admin->can('view_tabungan');
    }

    /**
     * Determine whether the admin can create models.
     */
    public function create(Admin $admin): bool
    {
        return $admin->can('create_tabungan');
    }

    /**
     * Determine whether the admin can update the model.
     */
    public function update(Admin $admin, Tabungan $tabungan): bool
    {
        return $admin->can('update_tabungan');
    }

    /**
     * Determine whether the admin can delete the model.
     */
    public function delete(Admin $admin, Tabungan $tabungan): bool
    {
        return $admin->can('delete_tabungan');
    }

    /**
     * Determine whether the admin can bulk delete.
     */
    public function deleteAny(Admin $admin): bool
    {
        return $admin->can('delete_any_tabungan');
    }

    /**
     * Determine whether the admin can permanently delete.
     */
    public function forceDelete(Admin $admin, Tabungan $tabungan): bool
    {
        return $admin->can('force_delete_tabungan');
    }

    /**
     * Determine whether the admin can permanently bulk delete.
     */
    public function forceDeleteAny(Admin $admin): bool
    {
        return $admin->can('force_delete_any_tabungan');
    }

    /**
     * Determine whether the admin can restore.
     */
    public function restore(Admin $admin, Tabungan $tabungan): bool
    {
        return $admin->can('restore_tabungan');
    }

    /**
     * Determine whether the admin can bulk restore.
     */
    public function restoreAny(Admin $admin): bool
    {
        return $admin->can('restore_any_tabungan');
    }

    /**
     * Determine whether the admin can replicate.
     */
    public function replicate(Admin $admin, Tabungan $tabungan): bool
    {
        return $admin->can('replicate_tabungan');
    }

    /**
     * Determine whether the admin can reorder.
     */
    public function reorder(Admin $admin): bool
    {
        return $admin->can('reorder_tabungan');
    }
}
