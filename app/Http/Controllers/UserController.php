<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\PublicUserResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        return response()->json(new UserResource($request->user()));
    }

    public function updateMe(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        // Avatar handling now goes through Spatie Media Library instead of
        // a raw `avatar` column + manual Storage::put(). This is what
        // fixes the "old avatar never gets deleted" leak from the backend
        // update plan: singleFile() collections auto-replace (delete +
        // recreate) the previous media row and disk object on every add.
        if ($request->hasFile('avatar')) {
            $user->addMediaFromRequest('avatar')->toMediaCollection('avatar');
            unset($data['avatar']); // not a fillable column anymore
        }

        if (! empty($data)) {
            $user->update($data);
        }

        return response()->json(new UserResource($user->fresh()));
    }

    /**
     * Search users by username (primary), with optional email/phone lookup.
     * Excludes the requester from their own search results.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        $q = $request->string('q');

        $users = User::query()
            ->where('id', '!=', $request->user()->id)
            ->where(function ($query) use ($q) {
                $query->where('username', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            })
            ->limit(20)
            ->get();

        return response()->json(PublicUserResource::collection($users));
    }

    public function show(User $user): JsonResponse
    {
        // Route-model-bound by username (see User::getRouteKeyName).
        return response()->json(new PublicUserResource($user));
    }
}
