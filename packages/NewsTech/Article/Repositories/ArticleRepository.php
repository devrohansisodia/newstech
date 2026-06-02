<?php

namespace NewsTech\Article\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use NewsTech\Article\Models\Article;
use NewsTech\Author\Models\Author;
use NewsTech\Category\Models\Category;
use NewsTech\Core\Repositories\BaseRepository;
use NewsTech\Tag\Models\Tag;

/**
 * @extends BaseRepository<Article>
 */
class ArticleRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return Article::class;
    }

    /**
     * @return Builder<Article>
     */
    public function orderedQuery(): Builder
    {
        return $this->query()
            ->with([
                'category:id,name',
                'categories:id,name,parent_id',
                'author:id,name',
                'tags:id,name',
            ])
            ->latest('updated_at');
    }

    /**
     * @return Builder<Article>
     */
    public function topViewedQuery(): Builder
    {
        return $this->query()
            ->with([
                'category:id,name,slug',
                'categories:id,name,slug,parent_id',
                'author:id,name,slug',
            ])
            ->where('status', 'published')
            ->where(function (Builder $query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->orderByDesc('view_count')
            ->latest('published_at')
            ->latest('id');
    }

    /**
     * @return Builder<Article>
     */
    public function publishedQuery(): Builder
    {
        return $this->query()
            ->with([
                'category:id,name,slug',
                'categories:id,name,slug,parent_id',
                'author:id,name,slug',
                'tags:id,name,slug',
            ])
            ->where('status', 'published')
            ->where(function (Builder $query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->latest('published_at')
            ->latest('id');
    }

    public function heroArticle(): ?Article
    {
        /** @var ?Article $article */
        $article = $this->publishedQuery()->first();

        return $article;
    }

    /**
     * @return Collection<int, Article>
     */
    public function breakingArticles(int $limit = 6): Collection
    {
        return $this->publishedQuery()
            ->where('is_breaking', true)
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Article>
     */
    public function featuredArticles(int $limit = 4, int|string|null $exceptArticleId = null): Collection
    {
        return $this->publishedQuery()
            ->when($exceptArticleId, fn (Builder $query) => $query->whereKeyNot($exceptArticleId))
            ->where('is_featured', true)
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Article>
     */
    public function latestPublishedArticles(int $limit = 8, int|string|null $exceptArticleId = null): Collection
    {
        return $this->publishedQuery()
            ->when($exceptArticleId, fn (Builder $query) => $query->whereKeyNot($exceptArticleId))
            ->limit($limit)
            ->get();
    }

    /**
     * @return array<int, array{category: Category, articles: Collection<int, Article>, section_title: string}>
     */
    public function homepageCategoryBlocks(int $categoryLimit = 4, int $articlesPerCategory = 4): array
    {
        $categories = Category::query()
            ->where('status', true)
            ->where(function (Builder $query): void {
                $query->whereHas('articles', function (Builder $articleQuery): void {
                    $articleQuery->where('status', 'published')
                        ->where(function (Builder $publishedQuery): void {
                            $publishedQuery->whereNull('published_at')
                                ->orWhere('published_at', '<=', now());
                        });
                })->orWhereHas('primaryArticles', function (Builder $articleQuery): void {
                    $articleQuery->where('status', 'published')
                        ->where(function (Builder $publishedQuery): void {
                            $publishedQuery->whereNull('published_at')
                                ->orWhere('published_at', '<=', now());
                        });
                });
            })
            ->ordered()
            ->limit($categoryLimit)
            ->get();

        return $categories->map(function (Category $category) use ($articlesPerCategory): array {
            $articles = $this->publishedQuery()
                ->tap(fn (Builder $query) => $this->applyCategoryFilter($query, $category))
                ->limit($articlesPerCategory)
                ->get();

            return [
                'category' => $category,
                'articles' => $articles,
                'section_title' => Str::headline($category->name),
            ];
        })->filter(fn (array $block): bool => $block['articles']->isNotEmpty())->values()->all();
    }

    public function findPublishedBySlug(string $slug): ?Article
    {
        /** @var ?Article $article */
        $article = $this->publishedQuery()
            ->where('slug', $slug)
            ->first();

        return $article;
    }

    public function incrementViewCount(Article $article): void
    {
        $this->query()
            ->whereKey($article->getKey())
            ->increment('view_count');

        $article->view_count++;
    }

    /**
     * @return Collection<int, Article>
     */
    public function sitemapArticles(): Collection
    {
        return $this->publishedQuery()->get();
    }

    /**
     * @return Collection<int, Article>
     */
    public function recentPublishedArticlesForNewsSitemap(int $limit = 50, int $lookbackDays = 2): Collection
    {
        return $this->publishedQuery()
            ->whereNotNull('published_at')
            ->where('published_at', '>=', now()->subDays($lookbackDays))
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Article>
     */
    public function rssArticles(int $limit = 20): Collection
    {
        return $this->publishedQuery()
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Article>
     */
    public function categoryRssArticles(Category $category, int $limit = 20): Collection
    {
        return $this->publishedQuery()
            ->tap(fn (Builder $query) => $this->applyCategoryFilter($query, $category))
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Article>
     */
    public function topViewedArticles(int $limit = 5): Collection
    {
        return $this->topViewedQuery()
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Article>
     */
    public function relatedPublishedArticles(Article $article, int $limit = 4): Collection
    {
        $relatedByCategory = $this->publishedQuery()
            ->whereKeyNot($article->getKey())
            ->when($article->category_id, function (Builder $query) use ($article): void {
                $this->applyCategoryFilter($query, $article->category_id);
            })
            ->limit($limit)
            ->get();

        if ($relatedByCategory->count() >= $limit) {
            return $relatedByCategory;
        }

        $remaining = $limit - $relatedByCategory->count();

        $fallbackArticles = $this->publishedQuery()
            ->whereKeyNot($article->getKey())
            ->whereNotIn('id', $relatedByCategory->pluck('id'))
            ->limit($remaining)
            ->get();

        return $relatedByCategory->concat($fallbackArticles);
    }

    public function paginatePublishedByCategory(Category $category, int $perPage = 9): LengthAwarePaginator
    {
        return $this->publishedQuery()
            ->tap(fn (Builder $query) => $this->applyCategoryFilter($query, $category))
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginatePublishedByTag(Tag $tag, int $perPage = 9): LengthAwarePaginator
    {
        return $this->publishedQuery()
            ->whereHas('tags', fn (Builder $query) => $query->whereKey($tag->getKey()))
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginatePublishedByAuthor(Author $author, int $perPage = 9): LengthAwarePaginator
    {
        return $this->publishedQuery()
            ->where('author_id', $author->getKey())
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{q: string, category: string, author: string, tag: string}  $filters
     */
    public function searchPublishedArticles(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        return $this->publishedQuery()
            ->when($filters['q'] !== '', function (Builder $query) use ($filters): void {
                $keyword = $filters['q'];

                $query->where(function (Builder $searchQuery) use ($keyword): void {
                    $searchQuery->where('title', 'like', '%'.$keyword.'%')
                        ->orWhere('excerpt', 'like', '%'.$keyword.'%')
                        ->orWhere('content', 'like', '%'.$keyword.'%');
                });
            })
            ->when($filters['category'] !== '', function (Builder $query) use ($filters): void {
                $query->where(function (Builder $categoryScope) use ($filters): void {
                    $categoryScope->whereHas('category', function (Builder $categoryQuery) use ($filters): void {
                        $categoryQuery->where('status', true)->where('slug', $filters['category']);
                    })->orWhereHas('categories', function (Builder $categoryQuery) use ($filters): void {
                        $categoryQuery->where('status', true)->where('slug', $filters['category']);
                    });
                });
            })
            ->when($filters['author'] !== '', function (Builder $query) use ($filters): void {
                $query->whereHas('author', function (Builder $authorQuery) use ($filters): void {
                    $authorQuery->where('status', true)->where('slug', $filters['author']);
                });
            })
            ->when($filters['tag'] !== '', function (Builder $query) use ($filters): void {
                $query->whereHas('tags', function (Builder $tagQuery) use ($filters): void {
                    $tagQuery->where('status', true)->where('slug', $filters['tag']);
                });
            })
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, int|string>  $categoryIds
     * @param  array<int, int|string>  $tagIds
     */
    public function createWithRelations(array $attributes, array $categoryIds = [], array $tagIds = []): Article
    {
        /** @var Article $article */
        $article = DB::transaction(function () use ($attributes, $categoryIds, $tagIds): Article {
            /** @var Article $article */
            $article = $this->create($attributes);
            $article->categories()->sync($categoryIds);
            $article->tags()->sync($tagIds);

            return $article->load(['category', 'categories', 'author', 'tags']);
        });

        return $article;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, int|string>  $categoryIds
     * @param  array<int, int|string>  $tagIds
     */
    public function updateWithRelations(Article|int|string $article, array $attributes, array $categoryIds = [], array $tagIds = []): Article
    {
        /** @var Article $updatedArticle */
        $updatedArticle = DB::transaction(function () use ($article, $attributes, $categoryIds, $tagIds): Article {
            /** @var Article $updatedArticle */
            $updatedArticle = $this->update($article, $attributes);
            $updatedArticle->categories()->sync($categoryIds);
            $updatedArticle->tags()->sync($tagIds);

            return $updatedArticle->load(['category', 'categories', 'author', 'tags']);
        });

        return $updatedArticle;
    }

    /**
     * @return array<int, string>
     */
    public function categoryTree(): Collection
    {
        return Category::query()
            ->whereNull('parent_id')
            ->ordered()
            ->with('childrenRecursive')
            ->get();
    }

    /**
     * @return array<int, string>
     */
    public function authorOptions(): array
    {
        return Author::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function tagOptions(): array
    {
        return Tag::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * @return Builder<Article>
     */
    protected function applyCategoryFilter(Builder $query, Category|int|string $category): Builder
    {
        $categoryId = $category instanceof Category ? $category->getKey() : $category;

        return $query->where(function (Builder $categoryQuery) use ($categoryId): void {
            $categoryQuery->where('category_id', $categoryId)
                ->orWhereHas('categories', fn (Builder $pivotQuery) => $pivotQuery->whereKey($categoryId));
        });
    }
}
