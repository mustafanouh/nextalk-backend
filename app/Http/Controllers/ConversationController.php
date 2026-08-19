<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConversationRequest;
use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use App\Models\User;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ConversationController extends Controller
{
    public function __construct(
        protected ConversationService $conversations,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $conversations = $this->conversations->listForUser($request->user());

        // ::collection() on a LengthAwarePaginator automatically preserves
        // pagination meta (current_page, last_page, etc.) in the response —
        // this is what the frontend's PaginatedResponse<T> type expects.
        return ConversationResource::collection($conversations);
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
