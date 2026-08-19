<?php

namespace App\Policies;

use App\Models\Call;
use App\Models\Conversation;
use App\Models\User;

class CallPolicy
{
    /**
     * Start a call inside a conversation — must be a participant.
     */
    public function create(User $user, Conversation $conversation): bool
    {
        return $conversation->participants()
            ->where('users.id', $user->id)
            ->exists();
    }

    /**
     * Accept/reject/end a call — must be a participant of the call's
     * conversation. Business rules (e.g. can't accept an already-ended
     * call, can't accept your own outgoing call) live in CallService.
     */
    public function respond(User $user, Call $call): bool
    {
        return $call->conversation->participants()
            ->where('users.id', $user->id)
            ->exists();
    }
}
