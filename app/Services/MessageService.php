<?php

namespace App\Services;

use App\Events\MessageDeleted;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class MessageService
{
    public function __construct(
        protected AttachmentService $attachments,
    ) {}

    /**
     * Paginated message history for a conversation, oldest-first per page
     * but paginated backwards from the newest message (typical chat UX).
     */
    public function history(Conversation $conversation, int $perPage = 30)
    {
        return $conversation->messages()
            ->with(['sender', 'attachments'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Create a message (text or with a single attachment) and broadcast it.
     * File upload + DB writes are wrapped in a transaction; the file itself
     * is stored before the transaction commits so we never reference a
     * database row for a file that failed to upload.
     *
     * broadcast() is dispatched via DB::afterCommit() rather than called
     * directly inside the transaction (see backend update plan #3): the
     * MessageSent event implements ShouldBroadcast, which pushes it onto
     * the queue. With Redis queues in particular, a worker can pick up and
     * process that job — querying for this exact message — before this
     * transaction has actually committed, since the two run on separate DB
     * connections. DB::afterCommit() guarantees the broadcast only fires
     * once the row is durably visible to every other connection.
     */
    public function send(Conversation $conversation, User $sender, array $data, ?UploadedFile $file = null): Message
    {
        return DB::transaction(function () use ($conversation, $sender, $data, $file) {
            $message = $conversation->messages()->create([
                'sender_id' => $sender->id,
                'type' => $data['type'],
                'body' => $data['body'] ?? null,
            ]);

            if ($file) {
                $this->attachments->storeForMessage($message, $file);
            }

            $message->load(['sender', 'attachments']);

            DB::afterCommit(fn () => broadcast(new MessageSent($message))->toOthers());

            return $message;
        });
    }

    public function delete(Message $message): void
    {
        $message->delete(); // soft delete — preserves conversation history/order

        broadcast(new MessageDeleted($message))->toOthers();
    }
}
