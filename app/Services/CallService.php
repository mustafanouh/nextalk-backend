<?php

namespace App\Services;

use App\Events\CallAccepted;
use App\Events\CallEnded;
use App\Events\CallRejected;
use App\Events\IncomingCall;
use App\Exceptions\InvalidCallStateException;
use App\Models\Call;
use App\Models\Conversation;
use App\Models\User;

class CallService
{
    public function start(Conversation $conversation, User $caller, string $type): Call
    {
        $callee = $conversation->participants()
            ->where('users.id', '!=', $caller->id)
            ->firstOrFail();

        $call = $conversation->calls()->create([
            'caller_id' => $caller->id,
            'type' => $type,
            'status' => 'ringing',
        ]);

        broadcast(new IncomingCall($call, $callee->id));

        return $call;
    }

    public function accept(Call $call, User $user): Call
    {
        $this->assertStatus($call, 'ringing');
        $this->assertNotCaller($call, $user);

        $call->update([
            'status' => 'active',
            'started_at' => now(),
        ]);

        broadcast(new CallAccepted($call));

        return $call;
    }

    public function reject(Call $call, User $user): Call
    {
        $this->assertStatus($call, 'ringing');
        $this->assertNotCaller($call, $user);

        $call->update(['status' => 'rejected']);

        broadcast(new CallRejected($call));

        return $call;
    }

    /**
     * Either participant may end an active call. A ringing call that the
     * caller cancels (or that times out) is marked 'missed' rather than
     * 'ended', since it was never answered.
     */
    public function end(Call $call, User $user): Call
    {
        if (! in_array($call->status, ['ringing', 'active'], true)) {
            throw InvalidCallStateException::alreadyFinished($call);
        }

        $wasRinging = $call->status === 'ringing';

        $call->update([
            'status' => $wasRinging ? 'missed' : 'ended',
            'ended_at' => now(),
        ]);

        broadcast(new CallEnded($call));

        return $call;
    }

    protected function assertStatus(Call $call, string $expected): void
    {
        if ($call->status !== $expected) {
            throw InvalidCallStateException::unexpectedStatus($call, $expected);
        }
    }

    protected function assertNotCaller(Call $call, User $user): void
    {
        if ($call->caller_id === $user->id) {
            throw InvalidCallStateException::callerCannotRespond($call);
        }
    }
}
