<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Visit;

class VisitPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Visit $visit): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isDoctor()) {
            return $visit->doctor_id === $user->id;
        }

        return $user->isReception();
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Reception, UserRole::Doctor]);
    }

    public function update(User $user, Visit $visit): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isDoctor()) {
            return $visit->doctor_id === $user->id;
        }

        return false;
    }

    public function delete(User $user, Visit $visit): bool
    {
        return $user->isAdmin();
    }
}
