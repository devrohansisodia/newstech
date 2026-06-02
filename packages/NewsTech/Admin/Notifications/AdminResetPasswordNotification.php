<?php

namespace NewsTech\Admin\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminResetPasswordNotification extends Notification
{
    public function __construct(protected string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('admin.newstech.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Reset your NewsTech admin password')
            ->line('You requested a password reset for your NewsTech admin account.')
            ->action('Reset Password', $url)
            ->line('This reset link will expire in 60 minutes.');
    }
}
