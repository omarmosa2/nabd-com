<?php

namespace App\Events;

use App\Models\DoctorDeduction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeductionAdded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public DoctorDeduction $deduction, public ?\App\Models\User $actor = null) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('doctors')];
    }

    public function broadcastAs(): string
    {
        return 'doctor.deduction-added';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->deduction->id,
            'doctor_id' => $this->deduction->doctor_id,
            'amount' => (float) $this->deduction->amount,
            'type' => $this->deduction->type?->value,
            'reason' => $this->deduction->reason,
        ];
    }
}
