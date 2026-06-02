<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('appointments', function (User $user) {
    return $user->isAdmin() || $user->isReception() || $user->isDoctor();
});

Broadcast::channel('clinics', function (User $user) {
    return $user->isAdmin() || $user->isReception() || $user->isDoctor();
});

Broadcast::channel('visits', function (User $user) {
    return $user->isAdmin() || $user->isReception() || $user->isDoctor();
});

Broadcast::channel('doctors', function (User $user) {
    return $user->isAdmin() || $user->isReception() || $user->isDoctor();
});

