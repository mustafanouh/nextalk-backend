<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Message;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentService
{
    /**
     * Store the uploaded file on object storage (NEVER in MySQL, and NEVER
     * pushed through the WebSocket) and persist its metadata.
     *
     * MIME type is read from the file's actual contents (Symfony/finfo),
     * not the client-supplied extension — StoreMessageRequest's `mimetypes`
     * rule already enforces this before we get here, but we re-derive it
     * for the metadata row so it can't be spoofed.
     */
    public function storeForMessage(Message $message, UploadedFile $file): Attachment
    {
        $disk = config('filesystems.default'); // e.g. 's3' in production
        $path = $file->store("attachments/{$message->conversation_id}", $disk);

        return $message->attachments()->create([
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    public function delete(Attachment $attachment): void
    {
        Storage::disk($attachment->disk)->delete($attachment->path);
        $attachment->delete();
    }
}
