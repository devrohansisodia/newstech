<?php

namespace NewsTech\Newsletter\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use NewsTech\Core\Repositories\BaseRepository;
use NewsTech\Newsletter\Models\NewsletterSubscriber;

/**
 * @extends BaseRepository<NewsletterSubscriber>
 */
class NewsletterSubscriberRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return NewsletterSubscriber::class;
    }

    public function activeQuery(): Builder
    {
        return $this->query()
            ->where('status', NewsletterSubscriber::STATUS_ACTIVE)
            ->whereNull('unsubscribed_at');
    }

    public function latestQuery(): Builder
    {
        return $this->query()->latest('subscribed_at')->latest();
    }

    public function findByEmail(string $email): ?NewsletterSubscriber
    {
        /** @var NewsletterSubscriber|null $subscriber */
        $subscriber = $this->query()
            ->where('email', $email)
            ->first();

        return $subscriber;
    }

    public function subscribe(
        string $email,
        ?string $source = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): NewsletterSubscriber {
        $subscriber = $this->findByEmail($email);

        if ($subscriber) {
            return $this->update($subscriber, [
                'status' => NewsletterSubscriber::STATUS_ACTIVE,
                'source' => $source,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'unsubscribe_token' => $subscriber->unsubscribe_token ?: Str::random(40),
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ]);
        }

        /** @var NewsletterSubscriber $subscriber */
        $subscriber = $this->create([
            'email' => $email,
            'unsubscribe_token' => Str::random(40),
            'status' => NewsletterSubscriber::STATUS_ACTIVE,
            'source' => $source,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'subscribed_at' => now(),
        ]);

        return $subscriber;
    }

    public function unsubscribe(NewsletterSubscriber $subscriber): NewsletterSubscriber
    {
        return $this->update($subscriber, [
            'status' => NewsletterSubscriber::STATUS_UNSUBSCRIBED,
            'unsubscribed_at' => now(),
            'unsubscribe_token' => $subscriber->unsubscribe_token ?: Str::random(40),
        ]);
    }

    public function deactivate(NewsletterSubscriber $subscriber): NewsletterSubscriber
    {
        return $this->update($subscriber, [
            'status' => NewsletterSubscriber::STATUS_INACTIVE,
            'unsubscribed_at' => $subscriber->unsubscribed_at ?: now(),
            'unsubscribe_token' => $subscriber->unsubscribe_token ?: Str::random(40),
        ]);
    }

    public function activate(NewsletterSubscriber $subscriber): NewsletterSubscriber
    {
        return $this->update($subscriber, [
            'status' => NewsletterSubscriber::STATUS_ACTIVE,
            'subscribed_at' => now(),
            'unsubscribed_at' => null,
            'unsubscribe_token' => $subscriber->unsubscribe_token ?: Str::random(40),
        ]);
    }

    public function findByUnsubscribeToken(string $token): ?NewsletterSubscriber
    {
        /** @var NewsletterSubscriber|null $subscriber */
        $subscriber = $this->query()
            ->where('unsubscribe_token', $token)
            ->first();

        return $subscriber;
    }
}
