<?php

namespace App\Http\Controllers;

use App\Events\ICECandidate;
use App\Events\WebRTCAnswer;
use App\Events\WebRTCOffer;
use App\Models\Call;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * NOTE: this controller wasn't in the original API list (section 7), but the
 * spec explicitly requires WebRTCOffer / WebRTCAnswer / ICECandidate as
 * server-broadcast events (section 9), which means something has to trigger
 * them. Two options exist:
 *   (a) client-to-client "whisper" events on the presence channel — no
 *       backend round-trip, lowest latency, but bypasses CallPolicy checks.
 *   (b) these thin authenticated endpoints — adds one HTTP round-trip but
 *       keeps signaling behind the same policy as the rest of the call.
 * This scaffold picks (b) for consistency with the rest of the app's
 * authorization model; swap to whispering later if latency requires it.
 */
class SignalingController extends Controller
{
    public function offer(Request $request, Call $call): JsonResponse
    {
        $this->authorize('respond', $call);

        $request->validate(['sdp' => ['required', 'array']]);

        broadcast(new WebRTCOffer($call->id, $request->user()->id, $request->sdp))->toOthers();

        return response()->json(['message' => 'Offer sent.']);
    }

    public function answer(Request $request, Call $call): JsonResponse
    {
        $this->authorize('respond', $call);

        $request->validate(['sdp' => ['required', 'array']]);

        broadcast(new WebRTCAnswer($call->id, $request->user()->id, $request->sdp))->toOthers();

        return response()->json(['message' => 'Answer sent.']);
    }

    public function iceCandidate(Request $request, Call $call): JsonResponse
    {
        $this->authorize('respond', $call);

        $request->validate(['candidate' => ['required', 'array']]);

        broadcast(new ICECandidate($call->id, $request->user()->id, $request->candidate))->toOthers();

        return response()->json(['message' => 'Candidate sent.']);
    }
}
