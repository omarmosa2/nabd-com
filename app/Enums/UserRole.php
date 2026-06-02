<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Reception = 'reception';
    case Doctor = 'doctor';
}
