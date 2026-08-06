<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\SetoranTabungan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable;

class SetoranTabunganPolicy
{
    use HandlesAuthorization;

    public function viewAny(Authenticatable $user): bool
    {
        return $user instanceof Admin || $user instanceof User;
    }

    public function view(Authenticatable $user, SetoranTabungan $setoran): bool
    {
        if ($user instanceof Admin) {
            return true;
        }

        if ($user instanceof User) {
            return $setoran->user_id === $user->id;
        }

        return false;
    }

    public function create(Authenticatable $user): bool
    {
        if ($user instanceof Admin) {
            return $user->can('create_setoran_tabungan') || $user->hasRole('super_admin');
        }

        return $user instanceof User;
    }

    public function kirimKlaim(Authenticatable $user, SetoranTabungan $setoran): bool
    {
        if ($user instanceof User) {
            return $setoran->user_id === $user->id;
        }

        return false;
    }

    public function unggahBukti(Authenticatable $user, SetoranTabungan $setoran): bool
    {
        if ($user instanceof User) {
            return $setoran->user_id === $user->id;
        }

        return false;
    }

    public function ajukanUlang(Authenticatable $user, SetoranTabungan $setoran): bool
    {
        if ($user instanceof User) {
            return $setoran->user_id === $user->id;
        }

        return false;
    }

    public function batalkan(Authenticatable $user, SetoranTabungan $setoran): bool
    {
        if ($user instanceof User) {
            return $setoran->user_id === $user->id;
        }

        return false;
    }

    public function mulaiReview(Authenticatable $user, SetoranTabungan $setoran): bool
    {
        return $user instanceof Admin;
    }

    public function setujui(Authenticatable $user, SetoranTabungan $setoran): bool
    {
        return $user instanceof Admin;
    }

    public function tolak(Authenticatable $user, SetoranTabungan $setoran): bool
    {
        return $user instanceof Admin;
    }

    public function mintaRevisi(Authenticatable $user, SetoranTabungan $setoran): bool
    {
        return $user instanceof Admin;
    }

    public function cobaUlangPosting(Authenticatable $user, SetoranTabungan $setoran): bool
    {
        return $user instanceof Admin;
    }
}
