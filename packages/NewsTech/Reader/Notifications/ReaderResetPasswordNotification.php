<?php

namespace NewsTech\Reader\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReaderResetPasswordNotification extends Notification
{
    public function __construct(protected string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('newstech.readers.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Reset your NewsTech reader password')
            ->line('You requested a password reset for your reader account.')
            ->action('Reset Password', $url)
            ->line('This reset link will expire in 60 minutes.');
    }
}
