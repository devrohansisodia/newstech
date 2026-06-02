<?php

namespace NewsTech\Reader\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class ReaderVerifyEmailNotification extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify your NewsTech reader email')
            ->line('Confirm your reader account email address to keep your NewsTech account active.')
            ->action('Verify Email Address', $verificationUrl);
    }

    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'newstech.readers.verification.verify',
            now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
