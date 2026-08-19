<?php

namespace App\Events;

use App\Models\Call;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallEnded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Call $call,
    ) {}

    public function broadcastOn(): array
    {
        return [new PresenceChannel("call.{$this->call->id}")];
    }

    public function broadcastAs(): string
    {
        return 'CallEnded';
    }

    public function broadcastWith(): array
    {
        return [
            'call_id' => $this->call->id,
            'status' => $this->call->status,
            'ended_at' => $this->call->ended_at,
            'duration' => $this->call->durationInSeconds(),
        ];
    }
}
