<?php

namespace App\Events;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppointmentStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Appointment $appointment,
        public AppointmentStatus $previousStatus,
        public ?\App\Models\User $actor = null,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('appointments')];
    }

    public function broadcastAs(): string
    {
        return 'appointment.status_changed';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->appointment->id,
            'previous_status' => $this->previousStatus->value,
            'status' => $this->appointment->status->value,
            'status_label' => $this->appointment->status->label(),
            'actor_id' => $this->actor?->id,
        ];
    }
}
