<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    /**
     * Route name MUST be 'verification.verify' — VerifyEmailNotification
     * builds its link via route('verification.verify', ...), matching
     * Laravel's own convention so the base VerifyEmail class's helpers
     * (hash comparison, etc.) keep working unchanged.
     */
    public function __invoke(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);
        $frontendUrl = rtrim(config('app.frontend_url'), '/');

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return redirect("{$frontendUrl}/login?verified=0");
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect("{$frontendUrl}/login?verified=1");
    }
}
