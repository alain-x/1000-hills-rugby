<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    public function __construct(public string $token)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expire = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        $url = route('admin.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->subject('Reset your 1000 Hills Rugby password')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('We received a request to reset the password for your 1000 Hills Rugby staff account.')
            ->action('Reset my password', $url)
            ->line('This link expires in '.$expire.' minutes and can only be used once.')
            ->line('If you did not request a password reset, you can safely ignore this email. Your password will not change.')
            ->line('Need help? Contact the club office at info@1000hillsrugby.rw.')
            ->line('This is an automated message from a no-reply address, so please do not reply to this email.')
            ->salutation('Kind regards,'."\n".'1000 Hills Rugby');
    }
}
