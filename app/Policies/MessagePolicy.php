<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    /**
     * View message history for a conversation — must be a participant.
     */
    public function viewAny(User $user, Conversation $conversation): bool
    {
        return $conversation->participants()
            ->where('users.id', $user->id)
            ->exists();
    }

    /**
     * Send a message into a conversation — must be a participant.
     */
    public function create(User $user, Conversation $conversation): bool
    {
        return $conversation->participants()
            ->where('users.id', $user->id)
            ->exists();
    }

    /**
     * Delete a message — only the original sender may delete it.
     */
    public function delete(User $user, Message $message): bool
    {
        return $message->sender_id === $user->id;
    }
}
