<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * This is the actual fix for backend update plan #2: Attachment::temporaryUrl()
 * existed since the first version of this scaffold but was NEVER called by
 * any controller — responses carried the raw internal `path` (an S3 object
 * key) instead of a URL, so no attachment ever rendered in the frontend
 * despite every request returning 200/201.
 */
class AttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'message_id' => $this->message_id,
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'url' => $this->temporaryUrl(),
        ];
    }
}
