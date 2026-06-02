<?php

namespace NewsTech\Page\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use NewsTech\Page\Models\Page;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => fake()->paragraphs(3, true),
            'status' => true,
            'meta_title' => fake()->sentence(4),
            'meta_description' => fake()->paragraph(),
            'focus_keyword' => fake()->words(2, true),
        ];
    }
}
