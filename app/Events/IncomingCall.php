<?php

namespace App\Events;

use App\Http\Resources\CallResource;
use App\Models\Call;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncomingCall implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Call $call,
        public int $calleeId,
    ) {}

    /**
     * Only the callee needs the ring — the caller already knows they called.
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel("user.{$this->calleeId}")];
    }

    public function broadcastAs(): string
    {
        return 'IncomingCall';
    }

    public function broadcastWith(): array
    {
        return [
            'call' => (new CallResource($this->call))->resolve(),
        ];
    }
}
