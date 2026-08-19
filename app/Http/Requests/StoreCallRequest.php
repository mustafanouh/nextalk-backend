<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gate is applied via CallPolicy in the controller
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:audio,video'],
        ];
    }
}
