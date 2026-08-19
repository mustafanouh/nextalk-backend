<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Carries an SDP offer between peers via Reverb. Implements
 * ShouldBroadcastNow (not ShouldBroadcast) so it is NOT pushed through the
 * queue — signaling must happen synchronously/immediately, any queue delay
 * would break call setup latency.
 *
 * NOTE: SDP payloads are transient signaling data and are intentionally
 * never persisted to the database (see architectural rule #16).
 */
class WebRTCOffer implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $callId,
        public int $fromUserId,
        public array $sdp,
    ) {}

    public function broadcastOn(): array
    {
        return [new PresenceChannel("call.{$this->callId}")];
    }

    public function broadcastAs(): string
    {
        return 'WebRTCOffer';
    }

    public function broadcastWith(): array
    {
        return [
            'call_id' => $this->callId,
            'from_user_id' => $this->fromUserId,
            'sdp' => $this->sdp,
        ];
    }
}
