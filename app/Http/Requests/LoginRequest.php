<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 'identifier' may be username or email — resolved in AuthController
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }
}
