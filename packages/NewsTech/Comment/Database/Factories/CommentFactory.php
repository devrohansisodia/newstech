<?php

namespace NewsTech\Comment\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NewsTech\Article\Models\Article;
use NewsTech\Comment\Models\Comment;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'article_id' => Article::factory(),
            'reader_id' => null,
            'parent_id' => null,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'website' => fake()->optional()->url(),
            'content' => fake()->paragraph(),
            'status' => 'pending',
            'is_spam' => false,
            'spam_reason' => null,
            'ip_address' => fake()->optional()->ipv4(),
            'user_agent' => fake()->optional()->userAgent(),
            'approved_at' => null,
            'moderated_at' => null,
            'moderated_by' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (): array => [
            'status' => 'approved',
            'is_spam' => false,
            'spam_reason' => null,
            'approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (): array => [
            'status' => 'rejected',
            'approved_at' => null,
        ]);
    }

    public function spam(string $status = 'pending', string $reason = 'blocked_word'): static
    {
        return $this->state(fn (): array => [
            'status' => $status,
            'is_spam' => true,
            'spam_reason' => $reason,
            'approved_at' => null,
        ]);
    }
}
