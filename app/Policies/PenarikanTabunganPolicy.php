<?php

namespace App\Policies;

use App\Enums\StatusPenarikan;
use App\Models\Admin;
use App\Models\PenarikanTabungan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable;

class PenarikanTabunganPolicy
{
    use HandlesAuthorization;

    public function viewAny(Authenticatable $user): bool
    {
        return $user instanceof Admin || $user instanceof User;
    }

    public function view(Authenticatable $user, PenarikanTabungan $penarikan): bool
    {
        if ($user instanceof Admin) {
            return true;
        }

        if ($user instanceof User) {
            return $penarikan->user_id === $user->id;
        }

        return false;
    }

    public function create(Authenticatable $user): bool
    {
        if ($user instanceof Admin) {
            return $user->can('create_penarikan_tabungan') || $user->hasRole('super_admin');
        }

        return $user instanceof User;
    }

    public function kirimRevisi(Authenticatable $user, PenarikanTabungan $penarikan): bool
    {
        if ($user instanceof User) {
            return $penarikan->user_id === $user->id;
        }

        return false;
    }

    public function batalkan(Authenticatable $user, PenarikanTabungan $penarikan): bool
    {
        if ($user instanceof User) {
            return $penarikan->user_id === $user->id
                && $penarikan->status === StatusPenarikan::MENUNGGU_VERIFIKASI;
        }

        return false;
    }

    public function mulaiReview(Authenticatable $user, PenarikanTabungan $penarikan): bool
    {
        return $user instanceof Admin;
    }

    public function setujui(Authenticatable $user, PenarikanTabungan $penarikan): bool
    {
        return $user instanceof Admin;
    }

    public function tolak(Authenticatable $user, PenarikanTabungan $penarikan): bool
    {
        return $user instanceof Admin;
    }

    public function mintaRevisi(Authenticatable $user, PenarikanTabungan $penarikan): bool
    {
        return $user instanceof Admin;
    }

    public function cobaUlangPosting(Authenticatable $user, PenarikanTabungan $penarikan): bool
    {
        return $user instanceof Admin;
    }
}
