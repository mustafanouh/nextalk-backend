<?php

namespace App\Exceptions;

use App\Models\Call;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvalidCallStateException extends Exception
{
    public static function unexpectedStatus(Call $call, string $expected): self
    {
        return new self("Call #{$call->id} is '{$call->status}', expected '{$expected}'.");
    }

    public static function callerCannotRespond(Call $call): self
    {
        return new self('The caller cannot accept/reject their own call.');
    }

    public static function alreadyFinished(Call $call): self
    {
        return new self("Call #{$call->id} has already finished (status: {$call->status}).");
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 409);
    }
}
