<?php

namespace NewsTech\Article\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use NewsTech\Article\Models\Article;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        $title = fake()->sentence(6);

        return [
            'category_id' => null,
            'author_id' => null,
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(100, 999),
            'excerpt' => fake()->paragraph(),
            'content' => fake()->paragraphs(3, true),
            'featured_image' => 'articles/'.Str::slug($title).'.jpg',
            'status' => 'draft',
            'is_featured' => false,
            'is_breaking' => false,
            'published_at' => null,
            'scheduled_at' => null,
            'meta_title' => fake()->sentence(5),
            'meta_description' => fake()->sentence(12),
            'focus_keyword' => fake()->words(2, true),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => 'published',
            'published_at' => now()->subHour(),
            'scheduled_at' => null,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (): array => [
            'status' => 'published',
            'is_featured' => true,
            'published_at' => now()->subHours(2),
            'scheduled_at' => null,
        ]);
    }

    public function breaking(): static
    {
        return $this->state(fn (): array => [
            'status' => 'published',
            'is_breaking' => true,
            'published_at' => now()->subMinutes(30),
            'scheduled_at' => null,
        ]);
    }
}
