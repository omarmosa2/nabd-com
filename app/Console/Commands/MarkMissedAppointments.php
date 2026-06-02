<?php

namespace App\Console\Commands;

use App\Services\AppointmentService;
use Illuminate\Console\Command;

class MarkMissedAppointments extends Command
{
    protected $signature = 'appointments:mark-missed';

    protected $description = 'Mark all scheduled appointments whose end time has passed as missed.';

    public function handle(AppointmentService $service): int
    {
        $count = $service->markMissedAppointments();
        $this->info("Marked {$count} appointment(s) as missed.");
        return self::SUCCESS;
    }
}
