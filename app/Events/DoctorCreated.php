<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DoctorCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public User $doctor, public ?User $actor = null) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('doctors')];
    }

    public function broadcastAs(): string
    {
        return 'doctor.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->doctor->id,
            'full_name' => $this->doctor->full_name,
            'clinic_id' => $this->doctor->clinic_id,
            'specialization' => $this->doctor->specialization,
        ];
    }
}
