<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

/**
 * Replaces Laravel's default VerifyEmail notification. The default one
 * builds its link via route('verification.verify', ...) — a named route
 * this API-only project never registers (no web routes for it), so
 * dispatching the default notification throws RouteNotFoundException and
 * crashes the whole /auth/register request AFTER the user row is already
 * committed (see backend update plan #1).
 *
 * IMPORTANT: this still calls route('verification.verify', ...) below —
 * that route now DOES exist, but as an API route (see routes/api.php +
 * VerifyEmailController), not Laravel's default web-only Fortify/Breeze
 * route. Registering it is part of this same fix, not optional.
 */
class VerifyEmailNotification extends BaseVerifyEmail
{
    protected function verificationUrl($notifiable): string
    {
        $signedApiUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        // Frontend has no page of its own for this — it just needs the
        // signed API link the "Verify email" button in the email opens.
        return $signedApiUrl;
    }

    public function toMail($notifiable): MailMessage
    {
        $url = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('تأكيد البريد الإلكتروني — NexTalk')
            ->line('اضغط الزر تحت لتأكيد بريدك الإلكتروني.')
            ->action('تأكيد البريد الإلكتروني', $url)
            ->line('لو محدش طلب تسجيل حساب، تجاهل الرسالة دي.');
    }
}
