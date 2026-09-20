<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        protected MessageService $messages,
    ) {}

    public function index(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('viewAny', [Message::class, $conversation]);

        $messages = $this->messages->history($conversation, (int) $request->integer('per_page', 30));

        // See Controller::paginated() — flat shape, required for
        // useMessages()'s getNextPageParam (current_page/last_page) to work.
        return response()->json($this->paginated($messages, MessageResource::class));
    }

    public function store(StoreMessageRequest $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('create', [Message::class, $conversation]);

        $message = $this->messages->send(
            $conversation,
            $request->user(),
            $request->validated(),
            $request->file('attachment')
        );

        return response()->json(new MessageResource($message), 201);
    }

    public function destroy(Message $message): JsonResponse
    {
        $this->authorize('delete', $message);

        $this->messages->delete($message);

        return response()->json(['message' => 'Message deleted.']);
    }
}
