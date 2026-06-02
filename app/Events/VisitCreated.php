<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VisitCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $visit_id,
        public string $patient,
        public string $doctor,
        public float $amount,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('visits'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'visit.created';
    }
}
