<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isReception() || $user->isDoctor();
    }

    public function view(User $user, Appointment $appointment): bool
    {
        if ($user->isAdmin() || $user->isReception()) {
            return true;
        }
        if ($user->isDoctor()) {
            return $appointment->doctor_id === $user->id;
        }
        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isReception();
    }

    public function update(User $user, Appointment $appointment): bool
    {
        if ($user->isAdmin() || $user->isReception()) {
            return true;
        }
        if ($user->isDoctor()) {
            return $appointment->doctor_id === $user->id
                && $appointment->status === \App\Enums\AppointmentStatus::Scheduled;
        }
        return false;
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->isAdmin() || $user->isReception();
    }

    public function convert(User $user, Appointment $appointment): bool
    {
        if (!$user->isAdmin() && !$user->isReception()) {
            return false;
        }
        return $appointment->canBeConverted();
    }
}
