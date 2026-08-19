<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    /**
     * A user may only view a conversation they are a participant of.
     */
    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->participants()
            ->where('users.id', $user->id)
            ->exists();
    }

    /**
     * Any authenticated user may start a new conversation.
     * (Duplicate-prevention is handled in ConversationService, not here —
     * that's a business rule, not an authorization rule.)
     */
    public function create(User $user): bool
    {
        return true;
    }
}
