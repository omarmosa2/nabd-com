<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppointmentCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $appointment_id,
        public string $patient,
        public string $doctor,
        public string $appointment_date,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('appointments'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'appointment.created';
    }
}
