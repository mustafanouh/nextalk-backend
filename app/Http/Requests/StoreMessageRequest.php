<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gate is applied via MessagePolicy in the controller
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:text,image,file'],
            // required when type is text; optional caption otherwise
            'body' => ['required_if:type,text', 'nullable', 'string', 'max:5000'],
            // required when type is image/file; validated by MIME + size, never by extension alone
            'attachment' => ['required_if:type,image,file', 'nullable', 'file', 'max:20480', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/zip,text/plain'],
        ];
    }
}
