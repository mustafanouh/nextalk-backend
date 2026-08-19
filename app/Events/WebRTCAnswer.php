<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebRTCAnswer implements ShouldBroadcastNow
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
        return 'WebRTCAnswer';
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
