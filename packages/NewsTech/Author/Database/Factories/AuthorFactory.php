<?php

namespace NewsTech\Author\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use NewsTech\Author\Models\Author;

/**
 * @extends Factory<Author>
 */
class AuthorFactory extends Factory
{
    protected $model = Author::class;

    public function definition(): array
    {
        $name = fake()->name();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'email' => fake()->unique()->safeEmail(),
            'designation' => fake()->randomElement(['Senior Reporter', 'Political Editor', 'Business Correspondent']),
            'bio' => fake()->paragraph(),
            'avatar' => 'authors/'.Str::slug($name).'.jpg',
            'facebook_url' => fake()->url(),
            'twitter_url' => fake()->url(),
            'linkedin_url' => fake()->url(),
            'website_url' => fake()->url(),
            'meta_title' => fake()->sentence(4),
            'meta_description' => fake()->sentence(10),
            'status' => true,
        ];
    }
}
