<?php

namespace NewsTech\Newsletter\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use NewsTech\Newsletter\Http\Requests\StoreNewsletterSubscriptionRequest;
use NewsTech\Newsletter\Models\NewsletterSubscriber;
use NewsTech\Newsletter\Repositories\NewsletterSubscriberRepository;

class SubscriptionController
{
    public function __construct(protected NewsletterSubscriberRepository $subscribers) {}

    public function store(StoreNewsletterSubscriptionRequest $request): RedirectResponse
    {
        if (! config('newstech-newsletter.enabled', true)) {
            return back()
                ->with('newsletter_source', $request->input('source'))
                ->with('newsletter_status', 'Newsletter subscriptions are currently disabled.')
                ->with('newsletter_status_tone', 'warning');
        }

        $validated = $request->validated();
        $email = $validated['email'];
        $source = $validated['source'] ?: null;

        $existingSubscriber = $this->subscribers->findByEmail($email);

        if ($existingSubscriber && $existingSubscriber->status === 'active' && $existingSubscriber->unsubscribed_at === null) {
            return back()
                ->withInput()
                ->with('newsletter_source', $source)
                ->with('newsletter_status', 'This email is already subscribed to the NewsTech newsletter.')
                ->with('newsletter_status_tone', 'warning');
        }

        if (
            $existingSubscriber
            && $existingSubscriber->status === NewsletterSubscriber::STATUS_UNSUBSCRIBED
            && ! config('newstech-newsletter.allow_resubscribe', true)
        ) {
            return back()
                ->withInput()
                ->with('newsletter_source', $source)
                ->with('newsletter_status', 'This email has previously unsubscribed and cannot be resubscribed right now.')
                ->with('newsletter_status_tone', 'warning');
        }

        $this->subscribers->subscribe(
            $email,
            $source,
            $request->ip(),
            (string) $request->userAgent(),
        );

        return back()
            ->with('newsletter_source', $source)
            ->with('newsletter_status', 'You are subscribed. NewsTech newsletter updates will arrive here when campaigns are sent.')
            ->with('newsletter_status_tone', 'success');
    }
}
