<?php

namespace App\Providers;

use App\Models\Patient;
use App\Models\Visit;
use App\Policies\PatientPolicy;
use App\Policies\VisitPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Visit::class, VisitPolicy::class);
        Gate::policy(Patient::class, PatientPolicy::class);
    }
}
