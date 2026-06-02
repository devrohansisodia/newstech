<?php

namespace NewsTech\Bookmark\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use NewsTech\Bookmark\Models\BookmarkFolder;
use NewsTech\Reader\Models\Reader;

class BookmarkFolderFactory extends Factory
{
    protected $model = BookmarkFolder::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'reader_id' => Reader::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'sort_order' => 0,
        ];
    }
}
