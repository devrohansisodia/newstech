<?php

namespace NewsTech\Bookmark\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use NewsTech\Article\Models\Article;
use NewsTech\Bookmark\Models\ReaderArticleHistory;
use NewsTech\Core\Repositories\BaseRepository;
use NewsTech\Reader\Models\Reader;

/**
 * @extends BaseRepository<ReaderArticleHistory>
 */
class ReaderArticleHistoryRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return ReaderArticleHistory::class;
    }

    public function recordArticleView(Reader $reader, Article $article): void
    {
        $history = $this->query()->firstOrNew([
            'reader_id' => $reader->getKey(),
            'article_id' => $article->getKey(),
        ]);

        $history->last_viewed_at = now();
        $history->view_count = $history->exists ? ($history->view_count + 1) : 1;
        $history->save();
    }

    public function paginateForReader(Reader $reader, int $perPage = 12): LengthAwarePaginator
    {
        return $this->query()
            ->with(['article.category:id,name,slug', 'article.author:id,name,slug'])
            ->whereBelongsTo($reader)
            ->whereHas('article', fn ($query) => $query->where('status', 'published')->where(function ($publishedQuery): void {
                $publishedQuery->whereNull('published_at')->orWhere('published_at', '<=', now());
            }))
            ->orderByDesc('last_viewed_at')
            ->paginate($perPage)
            ->withQueryString();
    }
}
