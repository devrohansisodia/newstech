<?php

namespace NewsTech\Frontend\Http\Controllers;

use Illuminate\Contracts\View\View;
use NewsTech\Article\Repositories\ArticleRepository;
use NewsTech\Bookmark\Repositories\BookmarkFolderRepository;
use NewsTech\Bookmark\Repositories\BookmarkRepository;
use NewsTech\Bookmark\Repositories\ReaderArticleHistoryRepository;
use NewsTech\Comment\Repositories\CommentRepository;
use NewsTech\Core\Support\SeoData;
use NewsTech\Frontend\Support\AppliesSystemSettings;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ArticleController
{
    use AppliesSystemSettings;

    public function __construct(
        protected ArticleRepository $articles,
        protected CommentRepository $comments,
        protected BookmarkRepository $bookmarks,
        protected BookmarkFolderRepository $folders,
        protected ReaderArticleHistoryRepository $history,
    ) {}

    /**
     * @throws NotFoundHttpException
     */
    public function show(string $slug): View
    {
        $this->applySystemSettings();

        $article = $this->articles->findPublishedBySlug($slug);

        if (! $article) {
            abort(404);
        }

        $this->articles->incrementViewCount($article);

        $canonicalUrl = route('newstech.articles.show', ['slug' => $article->slug]);
        $relatedArticles = $this->articles->relatedPublishedArticles($article);
        $latestArticles = $this->articles->latestPublishedArticles(5, exceptArticleId: $article->getKey());
        $approvedComments = $this->comments->approvedForArticle($article);
        $reader = auth(config('newstech-reader.auth.guard'))->user();

        if ($reader) {
            $this->history->recordArticleView($reader, $article);
        }

        $isBookmarked = $reader
            ? $this->bookmarks->existsForReaderAndArticle($reader, $article)
            : false;

        $seo = SeoData::make(
            $article->meta_title ?: config('newstech.brand.name').' | '.$article->title,
            $article->meta_description ?: ($article->excerpt ?: 'Read the full article on NewsTech.'),
            $canonicalUrl
        )
            ->openGraph(
                $article->meta_title ?: config('newstech.brand.name').' | '.$article->title,
                $article->meta_description ?: ($article->excerpt ?: 'Read the full article on NewsTech.'),
                $article->featured_image_url
            )
            ->twitter(
                'summary_large_image',
                $article->meta_title ?: config('newstech.brand.name').' | '.$article->title,
                $article->meta_description ?: ($article->excerpt ?: 'Read the full article on NewsTech.'),
                $article->featured_image_url
            )
            ->structuredData([
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'NewsArticle',
                    'headline' => $article->title,
                    'description' => $article->meta_description ?: ($article->excerpt ?: 'Read the full article on NewsTech.'),
                    'datePublished' => $article->published_at?->toIso8601String(),
                    'dateModified' => $article->updated_at?->toIso8601String(),
                    'mainEntityOfPage' => $canonicalUrl,
                    'articleSection' => $article->category?->name,
                    'author' => $article->author ? [
                        '@type' => 'Person',
                        'name' => $article->author->name,
                    ] : null,
                    'image' => $article->featured_image_url ? [$article->featured_image_url] : [],
                    'publisher' => [
                        '@type' => 'Organization',
                        'name' => config('newstech.brand.name'),
                    ],
                    'keywords' => $article->tags->pluck('name')->implode(', '),
                ],
            ])
            ->breadcrumbs(array_values(array_filter([
                [
                    'name' => 'Home',
                    'url' => route('newstech.home'),
                ],
                $article->category ? [
                    'name' => $article->category->name,
                    'url' => route('newstech.categories.show', ['slug' => $article->category->slug]),
                ] : null,
                [
                    'name' => $article->title,
                    'url' => $canonicalUrl,
                ],
            ])));

        return view('newstech-frontend::articles.show', [
            'seo' => $seo,
            'article' => $article,
            'approvedComments' => $approvedComments,
            'approvedCommentCount' => $this->comments->approvedCountForArticle($article),
            'isBookmarked' => $isBookmarked,
            'bookmarkFolders' => $reader ? $this->folders->orderedQuery()->whereBelongsTo($reader)->get() : collect(),
            'relatedArticles' => $relatedArticles,
            'latestArticles' => $latestArticles,
        ]);
    }
}
