<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConversationRequest;
use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use App\Models\User;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function __construct(
        protected ConversationService $conversations,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $conversations = $this->conversations->listForUser($request->user());

        // See Controller::paginated() — flattens Laravel's default nested
        // {data, links, meta} resource-collection shape to match the
        // frontend's PaginatedResponse<T> type exactly.
        return response()->json($this->paginated($conversations, ConversationResource::class));
    }

    public function store(StoreConversationRequest $request): JsonResponse
    {
        $this->authorize('create', Conversation::class);

        $other = User::findOrFail($request->user_id);

        $conversation = $this->conversations->findOrCreatePrivate($request->user(), $other);

        return response()->json(new ConversationResource($conversation->load('participants')), 201);
    }

    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        return response()->json(new ConversationResource($conversation->load('participants')));
    }
}
