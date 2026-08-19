<?php

use App\Models\Call;
use Illuminate\Support\Facades\Broadcast;

// A user may only listen on their own private channel.
Broadcast::channel('user.{userId}', function ($user, int $userId) {
    return (int) $user->id === $userId;
});

// A user may join a call's presence channel only if they're a participant
// of that call's conversation. Returning an array publishes presence info
// (id/name) to other members of the channel.
Broadcast::channel('call.{callId}', function ($user, int $callId) {
    $call = Call::with('conversation.participants')->find($callId);

    if (! $call) {
        return false;
    }

    $isParticipant = $call->conversation->participants
        ->contains('id', $user->id);

    return $isParticipant
        ? ['id' => $user->id, 'name' => $user->name, 'username' => $user->username]
        : false;
});
