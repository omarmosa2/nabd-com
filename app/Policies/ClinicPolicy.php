<?php

namespace App\Policies;

use App\Models\Clinic;
use App\Models\User;

class ClinicPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isReception() || $user->isDoctor();
    }

    public function view(User $user, Clinic $clinic): bool
    {
        if ($user->isAdmin() || $user->isReception()) {
            return true;
        }
        if ($user->isDoctor()) {
            return $user->clinic_id === $clinic->id;
        }
        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Clinic $clinic): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Clinic $clinic): bool
    {
        return $user->isAdmin();
    }

    public function archive(User $user, Clinic $clinic): bool
    {
        return $user->isAdmin();
    }

    public function assignDoctor(User $user, Clinic $clinic): bool
    {
        return $user->isAdmin();
    }
}
