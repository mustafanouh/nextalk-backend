<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gate is applied via ConversationPolicy in the controller
    }

    public function rules(): array
    {
        return [
            // The other participant's user id. (Scope: one-to-one only for now.)
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ((int) $this->input('user_id') === $this->user()->id) {
                $validator->errors()->add('user_id', 'You cannot start a conversation with yourself.');
            }
        });
    }
}
