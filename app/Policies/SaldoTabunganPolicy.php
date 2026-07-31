<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Tabungan;
use Illuminate\Auth\Access\HandlesAuthorization;

class SaldoTabunganPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin): bool
    {
        return $admin->can('view_any_saldo::tabungan');
    }

    public function view(Admin $admin, Tabungan $tabungan): bool
    {
        return $admin->can('view_saldo::tabungan');
    }

    public function create(Admin $admin): bool
    {
        return $admin->can('create_saldo::tabungan');
    }

    public function update(Admin $admin, Tabungan $tabungan): bool
    {
        return $admin->can('update_saldo::tabungan');
    }

    public function delete(Admin $admin, Tabungan $tabungan): bool
    {
        return $admin->can('delete_saldo::tabungan');
    }

    public function deleteAny(Admin $admin): bool
    {
        return $admin->can('delete_any_saldo::tabungan');
    }

    public function forceDelete(Admin $admin, Tabungan $tabungan): bool
    {
        return $admin->can('force_delete_saldo::tabungan');
    }

    public function forceDeleteAny(Admin $admin): bool
    {
        return $admin->can('force_delete_any_saldo::tabungan');
    }

    public function restore(Admin $admin, Tabungan $tabungan): bool
    {
        return $admin->can('restore_saldo::tabungan');
    }

    public function restoreAny(Admin $admin): bool
    {
        return $admin->can('restore_any_saldo::tabungan');
    }

    public function replicate(Admin $admin, Tabungan $tabungan): bool
    {
        return $admin->can('replicate_saldo::tabungan');
    }

    public function reorder(Admin $admin): bool
    {
        return $admin->can('reorder_saldo::tabungan');
    }
}
