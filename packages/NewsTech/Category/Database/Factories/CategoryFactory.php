<?php

namespace NewsTech\Category\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use NewsTech\Category\Models\Category;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'parent_id' => null,
            'name' => str($name)->title()->toString(),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'description' => fake()->sentence(),
            'meta_title' => fake()->sentence(4),
            'meta_description' => fake()->sentence(10),
            'status' => true,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
