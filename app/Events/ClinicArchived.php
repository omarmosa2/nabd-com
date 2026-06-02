<?php

namespace App\Events;

use App\Models\Clinic;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClinicArchived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Clinic $clinic, public ?\App\Models\User $actor = null) {}

    public function broadcastOn(): array
    {
        return [new Channel('clinics')];
    }

    public function broadcastAs(): string
    {
        return 'clinic.archived';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->clinic->id,
            'name' => $this->clinic->name,
            'archived_at' => $this->clinic->archived_at?->toDateTimeString(),
            'actor_id' => $this->actor?->id,
        ];
    }
}
