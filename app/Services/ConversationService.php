<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ConversationService
{
    /**
     * Get an existing private conversation between two users, or create one.
     * This is the single source of truth for "prevent duplicate private
     * conversations" — never create a Conversation anywhere else.
     */
    public function findOrCreatePrivate(User $initiator, User $other): Conversation
    {
        $existing = Conversation::findPrivateBetween($initiator->id, $other->id);

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($initiator, $other) {
            $conversation = Conversation::create(['type' => 'private']);
            $conversation->participants()->attach([$initiator->id, $other->id]);

            return $conversation;
        });
    }

    /**
     * List conversations for a user, ordered by most recent activity,
     * with the last message and other participant preloaded for a chat list UI.
     */
    public function listForUser(User $user, int $perPage = 20)
    {
        return $user->conversations()
            ->with(['latestMessage.sender', 'participants' => function ($q) use ($user) {
                $q->where('users.id', '!=', $user->id);
            }])
            ->withMax('messages', 'created_at')
            ->orderByDesc('messages_max_created_at')
            ->paginate($perPage);
    }
}
