<?php

namespace NewsTech\Frontend\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use NewsTech\Article\Models\Article;
use NewsTech\Article\Repositories\ArticleRepository;
use NewsTech\Author\Models\Author;
use NewsTech\Category\Models\Category;
use NewsTech\Frontend\Support\AppliesSystemSettings;
use NewsTech\Page\Repositories\PageRepository;
use NewsTech\Tag\Models\Tag;

class SeoFeedController
{
    use AppliesSystemSettings;

    /**
     * @var list<string>
     */
    protected array $reservedStaticSlugs = [
        'about',
        'contact',
        'privacy-policy',
        'terms',
    ];

    public function __construct(
        protected ArticleRepository $articles,
        protected PageRepository $pages,
    ) {}

    public function sitemap(): Response
    {
        $this->applySystemSettings();

        return response()
            ->view('newstech-frontend::seo.sitemap', [
                'urls' => $this->sitemapUrls(),
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function newsSitemap(): Response
    {
        $this->applySystemSettings();

        return response()
            ->view('newstech-frontend::seo.sitemap-news', [
                'articles' => $this->articles->recentPublishedArticlesForNewsSitemap(),
                'siteName' => config('newstech.brand.name'),
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function rss(): Response
    {
        $this->applySystemSettings();

        return $this->rssResponse(
            title: config('newstech.brand.name').' Latest News',
            description: 'Latest published articles from '.config('newstech.brand.name').'.',
            canonicalUrl: route('newstech.rss'),
            articles: $this->articles->rssArticles(),
        );
    }

    public function categoryRss(string $slug): Response
    {
        $this->applySystemSettings();

        /** @var ?Category $category */
        $category = Category::query()
            ->where('status', true)
            ->where('slug', $slug)
            ->first();

        abort_if(! $category, 404);

        return $this->rssResponse(
            title: config('newstech.brand.name').' | '.$category->name.' RSS',
            description: 'Latest published articles from the '.$category->name.' category.',
            canonicalUrl: route('newstech.categories.rss', ['slug' => $category->slug]),
            articles: $this->articles->categoryRssArticles($category),
            category: $category,
        );
    }

    public function robots(): Response
    {
        $this->applySystemSettings();

        return response(
            implode("\n", [
                'User-agent: *',
                'Allow: /',
                '',
                'Sitemap: '.route('newstech.sitemap'),
                'Sitemap: '.route('newstech.sitemap-news'),
            ])
        )->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    protected function rssResponse(
        string $title,
        string $description,
        string $canonicalUrl,
        Collection $articles,
        ?Category $category = null,
    ): Response {
        return response()
            ->view('newstech-frontend::seo.rss', [
                'title' => $title,
                'description' => $description,
                'canonicalUrl' => $canonicalUrl,
                'siteName' => config('newstech.brand.name'),
                'articles' => $articles,
                'category' => $category,
            ])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }

    /**
     * @return Collection<int, array{loc: string, lastmod: ?string}>
     */
    protected function sitemapUrls(): Collection
    {
        $staticPages = collect([
            [
                'loc' => route('newstech.about'),
                'lastmod' => $this->pages->findActiveBySlug('about')?->updated_at?->toAtomString(),
            ],
            [
                'loc' => route('newstech.contact'),
                'lastmod' => $this->pages->findActiveBySlug('contact')?->updated_at?->toAtomString(),
            ],
            [
                'loc' => route('newstech.privacy-policy'),
                'lastmod' => $this->pages->findActiveBySlug('privacy-policy')?->updated_at?->toAtomString(),
            ],
            [
                'loc' => route('newstech.terms'),
                'lastmod' => $this->pages->findActiveBySlug('terms')?->updated_at?->toAtomString(),
            ],
        ]);

        $pages = $this->pages->activeQuery()
            ->whereNotIn('slug', $this->reservedStaticSlugs)
            ->get()
            ->map(fn ($page): array => [
                'loc' => route('newstech.pages.show', ['slug' => $page->slug]),
                'lastmod' => $page->updated_at?->toAtomString(),
            ]);

        $articles = $this->articles->sitemapArticles()->map(
            fn (Article $article): array => [
                'loc' => route('newstech.articles.show', ['slug' => $article->slug]),
                'lastmod' => ($article->updated_at ?? $article->published_at)?->toAtomString(),
            ]
        );

        $categories = Category::query()
            ->where('status', true)
            ->get()
            ->map(fn (Category $category): array => [
                'loc' => route('newstech.categories.show', ['slug' => $category->slug]),
                'lastmod' => $category->updated_at?->toAtomString(),
            ]);

        $tags = Tag::query()
            ->where('status', true)
            ->get()
            ->map(fn (Tag $tag): array => [
                'loc' => route('newstech.tags.show', ['slug' => $tag->slug]),
                'lastmod' => $tag->updated_at?->toAtomString(),
            ]);

        $authors = Author::query()
            ->where('status', true)
            ->get()
            ->map(fn (Author $author): array => [
                'loc' => route('newstech.authors.show', ['slug' => $author->slug]),
                'lastmod' => $author->updated_at?->toAtomString(),
            ]);

        return collect([
            [
                'loc' => route('newstech.home'),
                'lastmod' => null,
            ],
            ...$staticPages->all(),
            ...$pages->all(),
            ...$categories->all(),
            ...$tags->all(),
            ...$authors->all(),
            ...$articles->all(),
        ])->unique('loc')->values();
    }
}
