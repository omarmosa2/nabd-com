<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Patient $patient): bool
    {
        if ($user->isAdmin() || $user->isReception()) {
            return true;
        }

        if ($user->isDoctor()) {
            return $patient->visits()->where('doctor_id', $user->id)->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Reception]);
    }

    public function update(User $user, Patient $patient): bool
    {
        if ($user->isAdmin() || $user->isReception()) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Patient $patient): bool
    {
        if (!($user->isAdmin() || $user->isReception())) {
            return false;
        }

        if ($patient->visits()->exists()) {
            return false;
        }

        return true;
    }
}
