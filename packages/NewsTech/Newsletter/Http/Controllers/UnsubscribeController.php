<?php

namespace NewsTech\Newsletter\Http\Controllers;

use Illuminate\Contracts\View\View;
use NewsTech\Newsletter\Repositories\NewsletterSubscriberRepository;

class UnsubscribeController
{
    public function __construct(protected NewsletterSubscriberRepository $subscribers) {}

    public function __invoke(string $token): View
    {
        $subscriber = $this->subscribers->findByUnsubscribeToken($token);

        abort_if($subscriber === null, 404);

        if ($subscriber->isActive()) {
            $subscriber = $this->subscribers->unsubscribe($subscriber);
        }

        return view('newstech-frontend::newsletter.unsubscribe', [
            'subscriber' => $subscriber,
        ]);
    }
}
