<?php

namespace NewsTech\Newsletter\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use NewsTech\Newsletter\Models\NewsletterSubscriber;

/**
 * @extends Factory<NewsletterSubscriber>
 */
class NewsletterSubscriberFactory extends Factory
{
    protected $model = NewsletterSubscriber::class;

    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'unsubscribe_token' => Str::random(40),
            'status' => NewsletterSubscriber::STATUS_ACTIVE,
            'source' => fake()->randomElement(['homepage', 'article', 'footer']),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'subscribed_at' => now(),
            'unsubscribed_at' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'status' => 'inactive',
            'unsubscribed_at' => now(),
        ]);
    }

    public function unsubscribed(): static
    {
        return $this->state(fn (): array => [
            'status' => NewsletterSubscriber::STATUS_UNSUBSCRIBED,
            'unsubscribed_at' => now(),
        ]);
    }
}
