<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Introduced to fix backend update plan #2: controllers were returning raw
 * Eloquent models via response()->json($model), which (a) exposes every
 * column including internal ones with no filtering, (b) never included
 * the avatar_url/avatar_thumb_url accessors unless $appends happened to
 * list them, and (c) had no single place to fix a field for every endpoint
 * that returns a user. Resources fix all three at once.
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'name' => $this->name,
            'email' => $this->when($request->user()?->id === $this->id, $this->email),
            'phone' => $this->when($request->user()?->id === $this->id, $this->phone),
            'avatar_url' => $this->avatar_url,
            'avatar_thumb_url' => $this->avatar_thumb_url,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
