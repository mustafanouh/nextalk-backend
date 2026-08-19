<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MessageController extends Controller
{
    public function __construct(
        protected MessageService $messages,
    ) {}

    public function index(Request $request, Conversation $conversation): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [Message::class, $conversation]);

        return MessageResource::collection(
            $this->messages->history($conversation, (int) $request->integer('per_page', 30))
        );
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
