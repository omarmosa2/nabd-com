<?php

namespace App\Policies;

use App\Models\User;

class DoctorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isReception() || $user->isDoctor();
    }

    public function view(User $user, User $doctor): bool
    {
        if ($user->isAdmin() || $user->isReception()) return true;
        if ($user->isDoctor()) return $user->id === $doctor->id;
        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $doctor): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, User $doctor): bool
    {
        return $user->isAdmin();
    }

    public function archive(User $user, User $doctor): bool
    {
        return $user->isAdmin();
    }

    public function activate(User $user, User $doctor): bool
    {
        return $user->isAdmin();
    }

    public function deactivate(User $user, User $doctor): bool
    {
        return $user->isAdmin();
    }

    public function addDeduction(User $user, User $doctor): bool
    {
        return $user->isAdmin();
    }

    public function viewFinance(User $user, User $doctor): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isDoctor()) return $user->id === $doctor->id;
        return false;
    }
}
