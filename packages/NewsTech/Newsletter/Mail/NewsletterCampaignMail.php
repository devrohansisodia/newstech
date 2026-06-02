<?php

namespace NewsTech\Newsletter\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use NewsTech\Newsletter\Models\NewsletterCampaign;
use NewsTech\Newsletter\Models\NewsletterSubscriber;

class NewsletterCampaignMail extends Mailable
{
    use Queueable;

    public function __construct(
        public NewsletterCampaign $campaign,
        public NewsletterSubscriber $subscriber,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->campaign->subject,
            from: new Address(
                (string) config('newstech-newsletter.sender_email', config('mail.from.address')),
                (string) config('newstech-newsletter.sender_name', config('mail.from.name'))
            )
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'newstech-newsletter::mail.campaign',
            with: [
                'campaign' => $this->campaign,
                'subscriber' => $this->subscriber,
                'siteName' => config('newstech.brand.name'),
                'footerUnsubscribeText' => config('newstech-newsletter.footer_unsubscribe_text'),
                'unsubscribeUrl' => route('newstech.newsletter.unsubscribe', $this->subscriber->unsubscribe_token),
            ],
        );
    }
}
