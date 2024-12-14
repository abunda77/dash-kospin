<?php

namespace App\Policies;


use App\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Activitylog\Models\Activity;

class ActivityLogPolicy
{
    use HandlesAuthorization;
    /**
     * Determine whether the Admin can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->can('view_any_activity');
    }

    /**
     * Determine whether the Admin can view the model.
     */
    public function view(Admin $admin, Activity $activity): bool
    {
        return $admin->can('view_activity');
    }

    /**
     * Determine whether the Admin can create models.
     */
    public function create(Admin $admin): bool
    {
        return $admin->can('create_activity');
    }

    /**
     * Determine whether the Admin can update the model.
     */
    public function update(Admin $admin, Activity $activity): bool
    {
        return $admin->can('update_activity');
    }

    /**
     * Determine whether the Admin can delete the model.
     */
    public function delete(Admin $admin, Activity $activity): bool
    {
        return $admin->can('delete_activity');
    }

    /**
     * Determine whether the Admin can restore the model.
     */
    public function restore(Admin $admin, Activity $activity): bool
    {
        return $admin->can('restore_activity');
    }

    /**
     * Determine whether the Admin can permanently delete the model.
     */
    public function forceDelete(Admin $admin, Activity $activity): bool
    {
        return $admin->can('force_delete_activity');
    }
}
