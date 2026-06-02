<?php

namespace App\Events;

use App\Models\Appointment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppointmentCancelled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Appointment $appointment,
        public ?string $reason = null,
        public ?\App\Models\User $actor = null,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('appointments')];
    }

    public function broadcastAs(): string
    {
        return 'appointment.cancelled';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->appointment->id,
            'reason' => $this->reason,
            'cancelled_at' => $this->appointment->cancelled_at?->toDateTimeString(),
            'actor_id' => $this->actor?->id,
        ];
    }
}
