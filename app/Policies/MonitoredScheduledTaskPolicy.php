<?php

namespace App\Policies;

use App\Models\Admin;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTask;
use Illuminate\Auth\Access\HandlesAuthorization;

class MonitoredScheduledTaskPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the admin can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->can('view_any_monitored::scheduled::task');
    }

    /**
     * Determine whether the admin can view the model.
     */
    public function view(Admin $admin, MonitoredScheduledTask $monitoredScheduledTask): bool
    {
        return $admin->can('view_monitored::scheduled::task');
    }

    /**
     * Determine whether the admin can create models.
     */
    public function create(Admin $admin): bool
    {
        return $admin->can('create_monitored::scheduled::task');
    }

    /**
     * Determine whether the admin can update the model.
     */
    public function update(Admin $admin, MonitoredScheduledTask $monitoredScheduledTask): bool
    {
        return $admin->can('update_monitored::scheduled::task');
    }

    /**
     * Determine whether the admin can delete the model.
     */
    public function delete(Admin $admin, MonitoredScheduledTask $monitoredScheduledTask): bool
    {
        return $admin->can('delete_monitored::scheduled::task');
    }

    /**
     * Determine whether the admin can bulk delete.
     */
    public function deleteAny(Admin $admin): bool
    {
        return $admin->can('delete_any_monitored::scheduled::task');
    }

    /**
     * Determine whether the admin can permanently delete.
     */
    public function forceDelete(Admin $admin, MonitoredScheduledTask $monitoredScheduledTask): bool
    {
        return $admin->can('force_delete_monitored::scheduled::task');
    }

    /**
     * Determine whether the admin can permanently bulk delete.
     */
    public function forceDeleteAny(Admin $admin): bool
    {
        return $admin->can('force_delete_any_monitored::scheduled::task');
    }

    /**
     * Determine whether the admin can restore.
     */
    public function restore(Admin $admin, MonitoredScheduledTask $monitoredScheduledTask): bool
    {
        return $admin->can('restore_monitored::scheduled::task');
    }

    /**
     * Determine whether the admin can bulk restore.
     */
    public function restoreAny(Admin $admin): bool
    {
        return $admin->can('restore_any_monitored::scheduled::task');
    }

    /**
     * Determine whether the admin can replicate.
     */
    public function replicate(Admin $admin, MonitoredScheduledTask $monitoredScheduledTask): bool
    {
        return $admin->can('replicate_monitored::scheduled::task');
    }

    /**
     * Determine whether the admin can reorder.
     */
    public function reorder(Admin $admin): bool
    {
        return $admin->can('reorder_monitored::scheduled::task');
    }
}
