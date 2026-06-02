<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppointmentDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<string, mixed>  $snapshot
     */
    public function __construct(
        public array $snapshot,
        public ?\App\Models\User $actor = null,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('appointments')];
    }

    public function broadcastAs(): string
    {
        return 'appointment.deleted';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->snapshot['id'] ?? null,
            'actor_id' => $this->actor?->id,
        ];
    }
}
