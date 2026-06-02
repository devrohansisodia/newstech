<?php

namespace NewsTech\Bookmark\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use NewsTech\Article\Models\Article;
use NewsTech\Bookmark\Models\Bookmark;
use NewsTech\Bookmark\Models\BookmarkFolder;
use NewsTech\Core\Repositories\BaseRepository;
use NewsTech\Reader\Models\Reader;

/**
 * @extends BaseRepository<Bookmark>
 */
class BookmarkRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return Bookmark::class;
    }

    /**
     * @return Builder<Bookmark>
     */
    public function orderedQuery(): Builder
    {
        return $this->query()
            ->with(['article.category:id,name,slug', 'article.author:id,name,slug', 'folder:id,reader_id,name,slug'])
            ->latest('id');
    }

    public function existsForReaderAndArticle(Reader $reader, Article $article): bool
    {
        return $this->query()
            ->whereBelongsTo($reader)
            ->whereBelongsTo($article)
            ->exists();
    }

    public function createForReaderAndArticle(Reader $reader, Article $article, ?BookmarkFolder $folder = null): Bookmark
    {
        /** @var Bookmark $bookmark */
        $bookmark = $this->query()->firstOrCreate([
            'reader_id' => $reader->getKey(),
            'article_id' => $article->getKey(),
        ], [
            'folder_id' => $folder?->getKey(),
        ]);

        if ($folder && $bookmark->folder_id !== $folder->getKey()) {
            $bookmark->folder()->associate($folder);
            $bookmark->save();
        }

        return $bookmark;
    }

    public function removeForReaderAndArticle(Reader $reader, Article $article): void
    {
        $this->query()
            ->whereBelongsTo($reader)
            ->whereBelongsTo($article)
            ->delete();
    }

    public function countForReader(Reader $reader): int
    {
        return $this->query()->whereBelongsTo($reader)->count();
    }

    public function moveToFolder(Bookmark $bookmark, ?BookmarkFolder $folder): Bookmark
    {
        $bookmark->folder()->associate($folder);
        $bookmark->save();

        return $bookmark->refresh();
    }

    public function paginatePublishedForReader(Reader $reader, int $perPage = 12, ?BookmarkFolder $folder = null): LengthAwarePaginator
    {
        return $this->orderedQuery()
            ->whereBelongsTo($reader)
            ->when($folder, fn (Builder $query) => $query->whereBelongsTo($folder, 'folder'))
            ->whereHas('article', function (Builder $query): void {
                $query->where('status', 'published')
                    ->where(function (Builder $publishedQuery): void {
                        $publishedQuery->whereNull('published_at')
                            ->orWhere('published_at', '<=', now());
                    });
            })
            ->paginate($perPage)
            ->withQueryString();
    }
}
