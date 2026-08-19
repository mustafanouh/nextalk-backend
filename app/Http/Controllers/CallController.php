<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCallRequest;
use App\Http\Resources\CallResource;
use App\Models\Call;
use App\Models\Conversation;
use App\Services\CallService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CallController extends Controller
{
    public function __construct(
        protected CallService $calls,
    ) {}

    public function store(StoreCallRequest $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('create', [Call::class, $conversation]);

        $call = $this->calls->start($conversation, $request->user(), $request->type);

        return response()->json(new CallResource($call), 201);
    }

    public function accept(Request $request, Call $call): JsonResponse
    {
        $this->authorize('respond', $call);

        return response()->json(new CallResource($this->calls->accept($call, $request->user())));
    }

    public function reject(Request $request, Call $call): JsonResponse
    {
        $this->authorize('respond', $call);

        return response()->json(new CallResource($this->calls->reject($call, $request->user())));
    }

    public function end(Request $request, Call $call): JsonResponse
    {
        $this->authorize('respond', $call);

        return response()->json(new CallResource($this->calls->end($call, $request->user())));
    }
}
