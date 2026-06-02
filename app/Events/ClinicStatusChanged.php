<?php

namespace App\Events;

use App\Enums\ClinicStatus;
use App\Models\Clinic;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClinicStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Clinic $clinic,
        public ClinicStatus $previousStatus,
        public ?\App\Models\User $actor = null,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('clinics')];
    }

    public function broadcastAs(): string
    {
        return 'clinic.status_changed';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->clinic->id,
            'name' => $this->clinic->name,
            'previous_status' => $this->previousStatus->value,
            'status' => $this->clinic->status->value,
            'status_label' => $this->clinic->status->label(),
            'actor_id' => $this->actor?->id,
        ];
    }
}
