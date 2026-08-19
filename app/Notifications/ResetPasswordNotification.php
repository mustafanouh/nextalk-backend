<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Replaces Laravel's default ResetPassword notification, which builds its
 * link via route('password.reset', ...) — not registered in this API-only
 * project, so the default one throws RouteNotFoundException the moment
 * Password::sendResetLink() tries to dispatch it (see backend update plan #1).
 *
 * This version links straight to the Next.js frontend's /reset-password
 * page (see features/auth/components/ResetPasswordForm.tsx, which already
 * reads `token` and `email` from the query string).
 */
class ResetPasswordNotification extends BaseResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $frontendUrl = rtrim(config('app.frontend_url'), '/');
        $url = "{$frontendUrl}/reset-password?token={$this->token}&email=" . urlencode($notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject('إعادة تعيين كلمة المرور — NexTalk')
            ->line('استلمنا طلب إعادة تعيين كلمة المرور بتاعتك.')
            ->action('إعادة تعيين كلمة المرور', $url)
            ->line('الرابط ده صالح لمدة ' . config('auth.passwords.users.expire', 60) . ' دقيقة.')
            ->line('لو مطلبتش إعادة تعيين، تجاهل الرسالة دي — كلمة مرورك متغيرتش.');
    }
}
