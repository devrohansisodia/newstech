<?php

namespace NewsTech\Tag\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use NewsTech\Tag\Models\Tag;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => str($name)->title()->toString(),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'description' => fake()->sentence(),
            'meta_title' => fake()->sentence(4),
            'meta_description' => fake()->sentence(10),
            'status' => true,
        ];
    }
}
