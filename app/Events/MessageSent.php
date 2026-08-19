<?php

namespace App\Events;

use App\Http\Resources\MessageResource;
use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Message $message,
    ) {
        $this->message->loadMissing(['sender', 'attachments']);
    }

    /**
     * Broadcast to every participant's private channel (except the sender —
     * handled via broadcastToOthers if dispatched from an HTTP request context).
     */
    public function broadcastOn(): array
    {
        return $this->message->conversation
            ->participants()
            ->pluck('users.id')
            ->map(fn ($id) => new PrivateChannel("user.{$id}"))
            ->all();
    }

    public function broadcastAs(): string
    {
        return 'MessageSent';
    }

    public function broadcastWith(): array
    {
        // Reuses MessageResource so the WebSocket payload has the exact same
        // shape as the REST response (attachments carry `url`, not the raw
        // internal `path` — see backend update plan #2).
        return [
            'message' => (new MessageResource($this->message))->resolve(),
        ];
    }
}
