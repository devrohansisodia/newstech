<?php

namespace NewsTech\Bookmark\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NewsTech\Article\Models\Article;
use NewsTech\Bookmark\Models\Bookmark;
use NewsTech\Bookmark\Models\BookmarkFolder;
use NewsTech\Reader\Models\Reader;

/**
 * @extends Factory<Bookmark>
 */
class BookmarkFactory extends Factory
{
    protected $model = Bookmark::class;

    public function definition(): array
    {
        return [
            'reader_id' => Reader::factory(),
            'article_id' => Article::factory()->published(),
            'folder_id' => null,
        ];
    }

    public function inFolder(?BookmarkFolder $folder): self
    {
        return $this->state([
            'folder_id' => $folder?->getKey(),
        ]);
    }
}
