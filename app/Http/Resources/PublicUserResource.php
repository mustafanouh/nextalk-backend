<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Used wherever OTHER users are exposed (search results, a conversation's
 * participants, a message's sender) — deliberately excludes email/phone so
 * those are never leaked to anyone but the account owner (see UserResource
 * for the "it's me" full shape).
 */
class PublicUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'name' => $this->name,
            'avatar_url' => $this->avatar_url,
            'avatar_thumb_url' => $this->avatar_thumb_url,
        ];
    }
}
