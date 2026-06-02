<?php

namespace NewsTech\Bookmark\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NewsTech\Article\Models\Article;
use NewsTech\Bookmark\Models\ReaderArticleHistory;
use NewsTech\Reader\Models\Reader;

class ReaderArticleHistoryFactory extends Factory
{
    protected $model = ReaderArticleHistory::class;

    public function definition(): array
    {
        return [
            'reader_id' => Reader::factory(),
            'article_id' => Article::factory(),
            'last_viewed_at' => now(),
            'view_count' => 1,
        ];
    }
}
