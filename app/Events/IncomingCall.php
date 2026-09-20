<?php

namespace App\Events;

use App\Http\Resources\CallResource;
use App\Models\Call;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * ShouldBroadcastNow (not ShouldBroadcast) — see backend compatibility
 * audit: the queued version silently never arrives if no `queue:work`
 * process happens to be running (very easy to forget locally — reverb:start,
 * serve, AND queue:work are three separate processes). A ringing call is
 * exactly the kind of time-critical, low-volume event that shouldn't ever
 * depend on a worker being alive, same reasoning as WebRTCOffer/Answer/ICE.
 */
class IncomingCall implements ShouldBroadcastNow
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
